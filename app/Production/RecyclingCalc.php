<?php

namespace App\Production;

use App\Enums\Building as BuildingEnum;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;

/**
 * AWESOME Sink recycling calc (V66/V67). Given a plan's byproduct flows
 * (produced vs used), compute the recycled points/min and any auto-package
 * Packager steps.
 *
 *  - leftover = produced − used (per item, > 0)
 *  - sinkable solid leftover (V65) → recycled directly: points = leftover × sink_points
 *  - fluid/gas leftover → recycled only when the auto-package toggle is on AND a
 *    Package recipe with a sinkable packaged form exists (V67): packaged → sunk,
 *    surfacing the Packager building/power + empty-container input
 *  - everything else → waste
 *
 * Total points/min = Σ recycled flows × sink_points.
 *
 * @phpstan-type PackagedRow array{fluid: string, product: string, qty: float, buildings: float, power: float, container: ?string, container_qty: float, points: float}
 */
class RecyclingCalc
{
    /**
     * @param  Collection|array<string, float>  $byproducts      produced byproduct qty/min
     * @param  Collection|array<string, float>  $byproductsUsed  byproduct qty/min consumed elsewhere
     * @return array{points: float, recycled: array<string, array{qty: float, points: float}>, packaged: list<array<string, mixed>>, waste: array<string, float>}
     */
    public static function calc($byproducts, $byproductsUsed = [], bool $autoPackage = false): array
    {
        $produced = collect($byproducts);
        $used = collect($byproductsUsed);

        $points = 0.0;
        $recycled = [];
        $packaged = [];
        $waste = [];

        foreach ($produced as $name => $qty) {
            // getByproductsUsed is nested (byproduct → {consumer → qty}); sum the
            // per-consumer amounts to get the total consumed. A scalar is tolerated too.
            $consumed = $used->get($name, 0);
            $consumed = is_array($consumed) || $consumed instanceof Collection
                ? collect($consumed)->sum()
                : (float) $consumed;

            $leftover = (float) $qty - (float) $consumed;

            if ($leftover <= 1e-9) {
                continue;
            }

            $ingredient = Ingredient::ofName($name);

            if (! $ingredient) {
                $waste[$name] = $leftover;

                continue;
            }

            // sinkable solid → recycle directly (V66)
            if ($ingredient->isSinkable()) {
                $flowPoints = $leftover * (int) $ingredient->sink_points;
                $recycled[$name] = ['qty' => $leftover, 'points' => $flowPoints];
                $points += $flowPoints;

                continue;
            }

            // fluid/gas → package + recycle only when the toggle is on (V67)
            if ($ingredient->is_liquid && $autoPackage) {
                $row = static::packageFluid($ingredient, $leftover);

                if ($row) {
                    $packaged[] = $row;
                    $points += $row['points'];

                    continue;
                }
            }

            $waste[$name] = $leftover;
        }

        return compact('points', 'recycled', 'packaged', 'waste');
    }

    /**
     * Route a fluid/gas leftover through its Package recipe → Packaged form (V67).
     * Returns null when there is no Package recipe with a sinkable packaged form.
     *
     * @return array<string, mixed>|null
     */
    protected static function packageFluid(Ingredient $fluid, float $qty): ?array
    {
        $recipe = static::packageRecipe($fluid);

        if (! $recipe || ! $recipe->product || ! $recipe->product->isSinkable()) {
            return null;
        }

        $fluidInput = $recipe->ingredients->firstWhere('name', $fluid->name);
        $fluidPerMin = (float) optional(optional($fluidInput)->pivot)->base_qty;

        if ($fluidPerMin <= 0) {
            return null;
        }

        $buildings = $qty / $fluidPerMin;
        $packagedQty = $buildings * (float) $recipe->base_per_min;

        // the other input is the empty container (Empty Canister / Empty Fluid Tank)
        $container = $recipe->ingredients->first(fn ($i) => $i->name !== $fluid->name);
        $containerQty = $container ? $buildings * (float) $container->pivot->base_qty : 0.0;

        $basePower = (float) (optional(optional($recipe->building)->variant('mk1'))->base_power ?? 0);

        return [
            'fluid' => $fluid->name,
            'product' => $recipe->product->name,
            'qty' => $packagedQty,
            'buildings' => $buildings,
            'power' => $buildings * $basePower,
            'container' => optional($container)->name,
            'container_qty' => $containerQty,
            'points' => $packagedQty * (int) $recipe->product->sink_points,
            // V87: fold the Packager into the build (building summary / parts / power)
            'building' => optional($recipe->building)->name,
            'build_cost' => static::packagerBuildCost($recipe, $buildings),
        ];
    }

    /**
     * Build-cost materials for the auto-package Packager step (V87), scaled by the
     * (possibly fractional) building count — read from the Packager mk1 variant recipe.
     *
     * @return array<string, int>
     */
    protected static function packagerBuildCost(Recipe $recipe, float $buildings): array
    {
        $variant = optional($recipe->building)->variant('mk1');

        if (! $variant) {
            return [];
        }

        return $variant->recipe
            ->mapWithKeys(fn ($ingredient) => [
                $ingredient->name => (int) ceil($ingredient->pivot->qty * $buildings),
            ])
            ->all();
    }

    /**
     * The Package recipe for a fluid: a Packager recipe that consumes the fluid and
     * produces its (solid) Packaged form — NOT the Unpackage recipe (which consumes
     * the packaged form). Name mapping is not always "Packaged <X>" (Crude Oil →
     * Packaged Oil), so match by building + ingredient + non-liquid packaged product.
     */
    protected static function packageRecipe(Ingredient $fluid): ?Recipe
    {
        return Recipe::query()
            ->whereHas('ingredients', fn ($q) => $q->where('name', $fluid->name))
            ->get()
            ->first(function (Recipe $recipe) {
                return optional($recipe->building)->name === BuildingEnum::PACKAGER->value
                    && $recipe->product
                    && ! $recipe->product->is_liquid
                    && str_starts_with($recipe->product->name, 'Packaged');
            });
    }
}
