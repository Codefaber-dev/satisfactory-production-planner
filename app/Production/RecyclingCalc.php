<?php

namespace App\Production;

use App\Enums\Building as BuildingEnum;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
 *  - container demand from packaging (V135): leftover container byproduct feeds
 *    the Packager before the sink; any shortfall is produced via the container's
 *    base recipe → a `containers` production row
 *  - everything else → waste
 *
 * Total points/min = Σ recycled flows × sink_points.
 *
 * @phpstan-type PackagedRow array{fluid: string, product: string, qty: float, buildings: float, power: float, container: ?string, container_qty: float, points: float}
 * @phpstan-type ContainerRow array{product: string, qty: float, buildings: float, power: float, building: ?string, build_cost: array<string, int>, inputs: list<array{item: string, qty: float}>}
 */
class RecyclingCalc
{
    /**
     * @param  Collection|array<string, float>  $byproducts  produced byproduct qty/min
     * @param  Collection|array<string, float>  $byproductsUsed  byproduct qty/min consumed elsewhere
     * @return array{points: float, recycled: array<string, array{qty: float, points: float}>, packaged: list<array<string, mixed>>, containers: list<array<string, mixed>>, waste: array<string, float>}
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

            // sinkable solid → recycle directly (V66); points totalled after the
            // container offset (V135)
            if ($ingredient->isSinkable()) {
                $recycled[$name] = ['qty' => $leftover, 'points' => $leftover * (int) $ingredient->sink_points];

                continue;
            }

            // fluid/gas → package + recycle only when the toggle is on (V67)
            if ($ingredient->is_liquid && $autoPackage) {
                $row = static::packageFluid($ingredient, $leftover);

                if ($row) {
                    $packaged[] = $row;

                    continue;
                }
            }

            $waste[$name] = $leftover;
        }

        // V135/B68: packaging consumes empty containers the plan may not supply —
        // net the demand against leftover container byproduct, produce the rest.
        [$recycled, $containers] = static::resolveContainers($recycled, $packaged);

        // recompute: the container offset removes sunk container flows
        $points = (float) (collect($recycled)->sum('points') + collect($packaged)->sum('points'));

        return compact('points', 'recycled', 'packaged', 'containers', 'waste');
    }

    /**
     * Supply the packaging steps' empty-container demand (V135): leftover container
     * byproduct (already in $recycled) feeds the Packager before the sink; any
     * shortfall becomes a production row via the container's base recipe.
     *
     * @param  array<string, array{qty: float, points: float}>  $recycled
     * @param  list<array<string, mixed>>  $packaged
     * @return array{0: array<string, array{qty: float, points: float}>, 1: list<array<string, mixed>>}
     */
    protected static function resolveContainers(array $recycled, array $packaged): array
    {
        $demand = collect($packaged)
            ->filter(fn ($row) => ! empty($row['container']) && (float) ($row['container_qty'] ?? 0) > 0)
            ->groupBy('container')
            ->map(fn ($rows) => $rows->sum('container_qty'));

        $containers = [];

        foreach ($demand as $name => $qty) {
            if (isset($recycled[$name])) {
                $available = (float) $recycled[$name]['qty'];
                $used = min($available, $qty);
                $remaining = $available - $used;
                $qty -= $used;

                $recycled = $remaining > 1e-9
                    ? array_replace($recycled, [$name => [
                        'qty' => $remaining,
                        'points' => $remaining * (int) optional(Ingredient::ofName($name))->sink_points,
                    ]])
                    : collect($recycled)->except($name)->all();
            }

            if ($qty > 1e-9 && ($row = static::produceContainer($name, $qty))) {
                $containers[] = $row;
            }
        }

        return [$recycled, $containers];
    }

    /**
     * A production row for a container shortfall (V135): the container's base
     * recipe (Empty Canister ← Plastic, Empty Fluid Tank ← Aluminum Ingot) sized
     * to the demand — same shape family as the packaged rows so it folds into
     * the build summary / parts / power (V87). Null when no recipe exists.
     *
     * @return array<string, mixed>|null
     */
    protected static function produceContainer(string $name, float $qty): ?array
    {
        $recipe = optional(Ingredient::ofName($name))->baseRecipe();

        if (! $recipe || (float) $recipe->base_per_min <= 0) {
            return null;
        }

        $buildings = $qty / (float) $recipe->base_per_min;
        $basePower = (float) (optional(optional($recipe->building)->variant('mk1'))->base_power ?? 0);

        return [
            'product' => $name,
            'qty' => $qty,
            'buildings' => $buildings,
            'power' => $buildings * $basePower,
            'building' => optional($recipe->building)->name,
            'build_cost' => static::packagerBuildCost($recipe, $buildings),
            'inputs' => $recipe->ingredients
                ->map(fn ($ingredient) => [
                    'item' => $ingredient->name,
                    'qty' => $buildings * (float) $ingredient->pivot->base_qty,
                ])
                ->values()
                ->all(),
        ];
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
            // V94: the fluid input rate (= the leftover), so the packaging step never
            // reuses the packaged output qty for the input (B53)
            'fluid_qty' => $qty,
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
        // T154: cached per fluid (model-cache pattern, TTL per T124) and constrained
        // to Packager recipes — never load every recipe consuming the fluid.
        // RecipeObserver flushes `package_recipe.{ingredient}` on save.
        return Cache::remember("package_recipe.{$fluid->name}", MODEL_CACHE_TTL, function () use ($fluid) {
            return Recipe::query()
                ->whereHas('ingredients', fn ($q) => $q->where('name', $fluid->name))
                ->whereHas('building', fn ($q) => $q->where('name', BuildingEnum::PACKAGER->value))
                ->get()
                ->first(function (Recipe $recipe) {
                    return $recipe->product
                        && ! $recipe->product->is_liquid
                        && str_starts_with($recipe->product->name, 'Packaged');
                });
        });
    }
}
