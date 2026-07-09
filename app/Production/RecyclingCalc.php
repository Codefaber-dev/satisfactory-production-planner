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
 *  - container demand from packaging (V135/V136): leftover container byproduct
 *    feeds the Packager before the sink; any shortfall is produced via a **full
 *    nested production chain** (V136) — container ← Plastic ← Crude Oil etc. —
 *    whose own fluid byproducts (Plastic → HOR) feed back into packaging,
 *    iterated to a fixed point
 *  - everything else → waste
 *
 * Total points/min = Σ recycled flows × sink_points.
 *
 * @phpstan-type PackagedRow array{fluid: string, product: string, qty: float, buildings: float, power: float, container: ?string, container_qty: float, points: float}
 * @phpstan-type ChainRow array{product: string, qty: float, buildings: float, power: float, building: ?string, recipe: ?string, build_cost: array<string, int>, inputs: list<array{item: string, qty: float}>}
 */
class RecyclingCalc
{
    /**
     * Container-chain fixpoint iteration bounds (V136): the packaging↔chain
     * feedback is geometric (each round's extra fluid is a fraction of the
     * last), so demand converges fast; the cap only guards pathological data.
     */
    protected const MAX_CHAIN_PASSES = 20;

    protected const CHAIN_EPSILON = 1e-4;

    /**
     * @param  Collection|array<string, float>  $byproducts  produced byproduct qty/min
     * @param  Collection|array<string, float>  $byproductsUsed  byproduct qty/min consumed elsewhere
     * @return array{points: float, recycled: array<string, array{qty: float, points: float}>, packaged: list<array<string, mixed>>, containers: list<array<string, mixed>>, raw_materials: array<string, float>, intermediate_materials: array<string, float>, all_materials: array<string, float>, waste: array<string, float>}
     */
    public static function calc($byproducts, $byproductsUsed = [], bool $autoPackage = false): array
    {
        $leftovers = static::leftovers(collect($byproducts), collect($byproductsUsed));

        $recycled = [];
        $packaged = [];
        $waste = [];
        $chain = null;
        $previousShortfall = null;

        // V136: packaging demands containers; producing containers emits fluid
        // byproducts that get packaged too (Plastic → HOR). Iterate the whole
        // pool → classify → net → chain loop until the shortfall stabilizes.
        for ($pass = 0; $pass < static::MAX_CHAIN_PASSES; $pass++) {
            $pool = collect([$leftovers, $chain['leftovers'] ?? []])->sumByKey();

            [$recycled, $packaged, $waste] = static::classify($pool, $autoPackage);
            [$recycled, $shortfall] = static::netContainers($recycled, $packaged);

            if (static::stable($shortfall, $previousShortfall)) {
                break;
            }

            $previousShortfall = $shortfall;
            $chain = empty($shortfall) ? null : static::containerChain($shortfall);
        }

        // totalled after the container offset — only flows actually sunk count
        $points = (float) (collect($recycled)->sum('points') + collect($packaged)->sum('points'));

        return [
            'points' => $points,
            'recycled' => $recycled,
            'packaged' => $packaged,
            'containers' => $chain['rows'] ?? [],
            'raw_materials' => $chain['raw'] ?? [],
            'intermediate_materials' => $chain['intermediates'] ?? [],
            'all_materials' => $chain['all'] ?? [],
            'waste' => $waste,
        ];
    }

    /**
     * Per-item leftover = produced − consumed. getByproductsUsed is nested
     * (byproduct → {consumer → qty}); sum the per-consumer amounts to get the
     * total consumed. A scalar is tolerated too.
     *
     * @return array<string, float>
     */
    protected static function leftovers(Collection $produced, Collection $used): array
    {
        $out = [];

        foreach ($produced as $name => $qty) {
            $consumed = $used->get($name, 0);
            $consumed = is_array($consumed) || $consumed instanceof Collection
                ? collect($consumed)->sum()
                : (float) $consumed;

            $leftover = (float) $qty - (float) $consumed;

            if ($leftover > 1e-9) {
                $out[$name] = $leftover;
            }
        }

        return $out;
    }

    /**
     * Route each leftover flow: sinkable solid → recycled (V66); fluid/gas →
     * packaged when the toggle is on (V67); everything else → waste.
     *
     * @param  iterable<string, float>  $pool  leftover qty/min per item
     * @return array{0: array<string, array{qty: float, points: float}>, 1: list<array<string, mixed>>, 2: array<string, float>}
     */
    protected static function classify(iterable $pool, bool $autoPackage): array
    {
        $recycled = [];
        $packaged = [];
        $waste = [];

        foreach ($pool as $name => $leftover) {
            $leftover = (float) $leftover;

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

        return [$recycled, $packaged, $waste];
    }

    /**
     * Net the packaging steps' empty-container demand (V135): leftover container
     * byproduct (already in $recycled) feeds the Packager before the sink; the
     * remaining demand is returned as the shortfall to produce (V136).
     *
     * @param  array<string, array{qty: float, points: float}>  $recycled
     * @param  list<array<string, mixed>>  $packaged
     * @return array{0: array<string, array{qty: float, points: float}>, 1: array<string, float>}
     */
    protected static function netContainers(array $recycled, array $packaged): array
    {
        $demand = collect($packaged)
            ->filter(fn ($row) => ! empty($row['container']) && (float) ($row['container_qty'] ?? 0) > 0)
            ->groupBy('container')
            ->map(fn ($rows) => (float) $rows->sum('container_qty'));

        $shortfall = [];

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

            if ($qty > 1e-9) {
                $shortfall[$name] = $qty;
            }
        }

        return [$recycled, $shortfall];
    }

    /**
     * Shortfall maps are equal within CHAIN_EPSILON → the fixpoint is reached.
     *
     * @param  array<string, float>  $current
     * @param  array<string, float>|null  $previous
     */
    protected static function stable(array $current, ?array $previous): bool
    {
        if (is_null($previous)) {
            return empty($current);
        }

        $keys = array_unique(array_merge(array_keys($current), array_keys($previous)));

        foreach ($keys as $key) {
            if (abs(($current[$key] ?? 0) - ($previous[$key] ?? 0)) > static::CHAIN_EPSILON) {
                return false;
            }
        }

        return true;
    }

    /**
     * The full production chain for the container shortfall (V136): a nested
     * plan (Multiplexer of one ProductionCalculator per container) so recipe
     * choices, favorites, and internal byproduct reuse all apply. Returns the
     * per-product production rows (dependency order), the chain's own leftover
     * byproducts (they feed back into packaging), and its material totals for
     * the plan-payload merge.
     *
     * @param  array<string, float>  $shortfall  container → qty/min to produce
     * @return array{rows: list<array<string, mixed>>, leftovers: array<string, float>, raw: array<string, float>, intermediates: array<string, float>, all: array<string, float>}
     */
    protected static function containerChain(array $shortfall): array
    {
        $multiplexer = new Multiplexer;

        foreach ($shortfall as $name => $qty) {
            $multiplexer->add(ProductionCalculator::make(
                product: i($name),
                qty: $qty,
                choices: collect(request('choices'))->map(fn ($recipeName) => r($recipeName)),
            ));
        }

        $multiplexer->recalculateUsingByproductsUntilStable();

        $results = $multiplexer->getResults();
        $nonRaw = isset($results[1]) ? $results->skip(1) : $results;

        $rows = [];

        foreach ($nonRaw as $tier) {
            foreach ($tier as $product => $material) {
                foreach ($material->production as $production) {
                    if ((float) ($production['qty'] ?? 0) > 1e-9 && ! empty($production['recipe'])) {
                        $rows[] = static::chainRow($product, $production);
                    }
                }
            }
        }

        $raw = $multiplexer->getRawMaterials();
        $all = $multiplexer->getAllMaterials();

        return [
            'rows' => $rows,
            'leftovers' => static::leftovers(collect($multiplexer->getByproducts()), collect($multiplexer->getByproductsUsed())),
            'raw' => $raw->all(),
            // "intermediates" for the plan merge = every chain product incl. the
            // container roots (they're intermediates from the plan's view)
            'intermediates' => collect($all)->except($raw->keys()->all())->all(),
            'all' => $all->all(),
        ];
    }

    /**
     * A production row for one chain step (V136) — same shape family as the
     * packaged rows so it folds into the build summary / parts / power (V87)
     * and renders as a `produce` step.
     *
     * @param  array<string, mixed>  $production  a groupAndSortResults production row
     * @return array<string, mixed>
     */
    protected static function chainRow(string $product, array $production): array
    {
        $recipe = $production['recipe'];
        $qty = (float) $production['qty'];
        $buildings = $qty / (float) $recipe->base_per_min;
        $basePower = (float) (optional(optional($recipe->building)->variant('mk1'))->base_power ?? 0);

        return [
            'product' => $product,
            'qty' => $qty,
            'buildings' => $buildings,
            'power' => $buildings * $basePower,
            'building' => optional($recipe->building)->name,
            'recipe' => $recipe->description ?: null,
            'build_cost' => static::packagerBuildCost($recipe, $buildings),
            'inputs' => collect($production['ingredients'] ?? [])
                ->map(fn ($rate, $name) => ['item' => $name, 'qty' => (float) $rate])
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
