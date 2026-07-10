<?php

namespace App\Production;

use App\Enums\Building as BuildingEnum;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * AWESOME Sink recycling calc (V66/V67/V137). Given a plan's byproduct flows
 * (produced vs used), compute the recycled points/min and the auto-package
 * demand.
 *
 *  - leftover = produced − used (per item, > 0)
 *  - sinkable solid leftover (V65) → recycled directly: points = leftover × sink_points
 *  - fluid/gas leftover → recycled only when the auto-package toggle is on AND a
 *    Package recipe with a sinkable packaged form exists (V67): packaged → sunk
 *  - everything else → waste
 *
 * V137: the packaging production itself — Packager steps, container chain down
 * to raw, byproduct feedback — is NOT modeled here as synthetic rows. Instead
 * inject() adds one real ProductionCalculator per packaged form to the plan's
 * Multiplexer; byproduct convergence consumes the leftover fluid, nets leftover
 * containers, and recurses the chain, all rendering as normal plan steps. The
 * outer fixpoint re-sizes the injected calcs until leftovers stabilize
 * (ε 1e-4, cap 20 — geometric feedback per V136).
 *
 * Total points/min = Σ solid recycled + Σ injected packaged output × sink_points.
 *
 * @phpstan-type PackagedRow array{fluid: string, fluid_qty: float, product: string, qty: float, buildings: float, power: float, recipe: ?string, points: float}
 */
class RecyclingCalc
{
    /**
     * Packaging↔chain fixpoint iteration bounds (V136/V137): the feedback is
     * geometric (each round's extra fluid is a fraction of the last), so demand
     * converges fast; the cap only guards pathological data.
     */
    protected const MAX_CHAIN_PASSES = 20;

    protected const CHAIN_EPSILON = 1e-4;

    /**
     * Plan-level entry (V137): classify the plan's leftovers, inject one plan
     * calc per packaged form into the Multiplexer, and iterate to a fixpoint —
     * chain byproducts (Plastic → HOR) grow packaging demand, injected calcs
     * consume leftover fluids/containers, until the sized targets stabilize.
     *
     * @return array{points: float, recycled: array<string, array{qty: float, points: float}>, packaged: list<array<string, mixed>>, waste: array<string, float>}
     */
    public static function inject(Multiplexer $multiplexer, bool $autoPackage = false): array
    {
        $recycled = [];
        $packaged = [];
        $waste = [];
        $previousTargets = null;

        for ($pass = 0; $pass < static::MAX_CHAIN_PASSES; $pass++) {
            $pool = static::sizingPool($multiplexer, $packaged);

            [$recycled, $packaged, $waste] = static::classify($pool, $autoPackage);

            $targets = collect($packaged)->mapWithKeys(fn ($row) => [$row['product'] => (float) $row['qty']])->all();

            if (static::stable($targets, $previousTargets)) {
                break;
            }

            $previousTargets = $targets;

            $multiplexer->setInjected(static::injectedCalcs($packaged));
            $multiplexer->recalculateUsingByproductsUntilStable();
        }

        $points = (float) (collect($recycled)->sum('points') + collect($packaged)->sum('points'));

        return [
            'points' => $points,
            'recycled' => $recycled,
            'packaged' => $packaged,
            'waste' => $waste,
        ];
    }

    /**
     * Pure single-pass classification (unit-testable without a plan): route the
     * given byproduct leftovers into recycled / packaged / waste. No injection,
     * no container netting — plan-level behavior lives in inject().
     *
     * @param  Collection|array<string, float>  $byproducts  produced byproduct qty/min
     * @param  Collection|array<string, float>  $byproductsUsed  byproduct qty/min consumed elsewhere
     * @return array{points: float, recycled: array<string, array{qty: float, points: float}>, packaged: list<array<string, mixed>>, waste: array<string, float>}
     */
    public static function calc($byproducts, $byproductsUsed = [], bool $autoPackage = false): array
    {
        $leftovers = static::leftovers(collect($byproducts), collect($byproductsUsed));

        [$recycled, $packaged, $waste] = static::classify($leftovers, $autoPackage);

        $points = (float) (collect($recycled)->sum('points') + collect($packaged)->sum('points'));

        return [
            'points' => $points,
            'recycled' => $recycled,
            'packaged' => $packaged,
            'waste' => $waste,
        ];
    }

    /**
     * The leftover pool the packaging targets are sized against (V137):
     * produced − used across ALL calcs (injected included), except each
     * injected packager's own draw on its fluid — sizing measures the leftover
     * BEFORE packaging consumes it, else a met target collapses to zero on the
     * next pass. Excluding the recorded draw (not adding back the sized one)
     * stays exact under partial consumption. Container draws are NOT excluded:
     * a canister fed to the Packager must shrink the recycled-canister row.
     *
     * @param  list<array<string, mixed>>  $currentPackaged
     * @return array<string, float>
     */
    protected static function sizingPool(Multiplexer $multiplexer, array $currentPackaged): array
    {
        $used = collect($multiplexer->getByproductsUsed())
            ->map(fn ($consumers) => is_array($consumers) || $consumers instanceof Collection ? collect($consumers) : $consumers);

        foreach ($currentPackaged as $row) {
            $consumers = $used->get($row['fluid']);

            if ($consumers instanceof Collection) {
                $used->put($row['fluid'], $consumers->except($row['product']));
            }
        }

        return static::leftovers(collect($multiplexer->getByproducts()), $used);
    }

    /**
     * One real plan calc per packaged form (V137): Packaged X at the sized rate
     * via its Package recipe. Request recipe choices apply, so the chain honors
     * user picks (alt Plastic, etc.); byproduct convergence then makes it
     * consume the plan's leftover fluid instead of producing fresh.
     *
     * @param  list<array<string, mixed>>  $packaged
     * @return list<ProductionCalculator>
     */
    protected static function injectedCalcs(array $packaged): array
    {
        return collect($packaged)
            ->filter(fn ($row) => (float) $row['qty'] > 1e-9)
            ->map(function ($row) {
                // the Package recipe model — a base recipe's description is null,
                // so resolve it from the fluid rather than trusting the row string
                $recipe = static::packageRecipe(i($row['fluid']));

                if (! $recipe) {
                    return null;
                }

                return ProductionCalculator::make(
                    product: i($row['product']),
                    qty: $row['qty'],
                    recipe: $recipe,
                    choices: collect(request('choices'))->map(fn ($recipeName) => r($recipeName)),
                    dest: BuildingEnum::AWESOME_SINK->value,
                );
            })
            ->filter()
            ->values()
            ->all();
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

            // sinkable solid → recycle directly (V66)
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
     * Targets maps are equal within CHAIN_EPSILON → the fixpoint is reached.
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
     * Route a fluid/gas leftover through its Package recipe → Packaged form (V67).
     * Returns null when there is no Package recipe with a sinkable packaged form.
     * The row sizes the injected plan calc (V137); buildings/power stay for the
     * ProductionSummary line — the real step numbers come from the plan itself.
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

        $basePower = (float) (optional(optional($recipe->building)->variant('mk1'))->base_power ?? 0);

        return [
            'fluid' => $fluid->name,
            // V94: the fluid input rate (= the leftover), so packaging never
            // reuses the packaged output qty for the input (B53)
            'fluid_qty' => $qty,
            'product' => $recipe->product->name,
            'qty' => $packagedQty,
            'buildings' => $buildings,
            'power' => $buildings * $basePower,
            'points' => $packagedQty * (int) $recipe->product->sink_points,
        ];
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
