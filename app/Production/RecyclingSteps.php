<?php

namespace App\Production;

use App\Enums\Building as BuildingEnum;
use App\Models\Building;
use App\Models\Ingredient;

/**
 * V88: turn the recycling result (RecyclingCalc) into full production-step
 * descriptors so the frontend can render them like every other build step —
 * with inputs, an output, and a build diagram.
 *
 *  - one **produce** step per container shortfall (V135): the container's base
 *    recipe (Constructor) sized to the packaging demand; output feeds the
 *    Packager, never the sink.
 *  - one **packaging** step per auto-package fluid: a real Packager step whose
 *    footprint/power come from BuildingDetails::calc on the Package recipe; input
 *    = the leftover fluid, output = the Packaged form (→ the sink).
 *  - one terminal **sink** step (AWESOME Sink): inputs = every recycled item
 *    (sinkable byproduct + packaged fluid), output = total points/min.
 */
class RecyclingSteps
{
    /**
     * @param  array{points: float, recycled: array<string, array{qty: float, points: float}>, packaged: list<array<string, mixed>>, waste: array<string, float>}|null  $recycling
     * @return list<array<string, mixed>>
     */
    public static function build(?array $recycling, float $beltSpeed = 780): array
    {
        if (! $recycling) {
            return [];
        }

        $recycled = $recycling['recycled'] ?? [];
        $packaged = $recycling['packaged'] ?? [];
        $containers = $recycling['containers'] ?? [];

        if (empty($recycled) && empty($packaged)) {
            return [];
        }

        $steps = [];
        $sinkInputs = [];

        // direct sinkable byproducts feed the sink
        foreach ($recycled as $name => $info) {
            $sinkInputs[] = ['item' => $name, 'qty' => round((float) ($info['qty'] ?? 0), 4)];
        }

        // V135: container shortfall → a production step feeding the Packager (not the sink)
        foreach ($containers as $row) {
            $steps[] = static::produceStep($row, $beltSpeed);
        }

        // each auto-package fluid → a real Packager step; its output feeds the sink
        foreach ($packaged as $row) {
            $steps[] = static::packagingStep($row, $beltSpeed);
            $sinkInputs[] = ['item' => $row['product'], 'qty' => round((float) ($row['qty'] ?? 0), 4)];
        }

        $steps[] = static::sinkStep($sinkInputs, (float) ($recycling['points'] ?? 0), $beltSpeed);

        return $steps;
    }

    /**
     * V93: build the 4-clock overview set (c100/c150/c200/c250) for each auto-package
     * step's Package recipe, keyed `product|recipe.description ?? product` — the same
     * shape + key convention as ParsesSteps::getOverviews — so the frontend renders the
     * package ProductionStep with real overclock/somersloop controls. Merged into the
     * plan `overviews` map by the controller. Container produce steps (V135) get the
     * same treatment — their rows share the product/qty shape.
     *
     * @param  array{packaged?: list<array<string, mixed>>, containers?: list<array<string, mixed>>}|null  $recycling
     * @return array<string, array<string, mixed>>
     */
    public static function overviews(?array $recycling, float $beltSpeed = 780): array
    {
        if (! $recycling) {
            return [];
        }

        [
            'cost_multiplier' => $cost_multiplier,
            'power_multiplier' => $power_multiplier,
            'building_multiples' => $building_multiples,
            'building_cost_multiplier' => $building_cost_multiplier,
        ] = static::planParams();

        $overviews = [];

        foreach (array_merge($recycling['packaged'] ?? [], $recycling['containers'] ?? []) as $row) {
            $product = $row['product'];
            $qty = (float) ($row['qty'] ?? 0);
            $recipe = optional(Ingredient::ofName($product))->baseRecipe();

            if (! $recipe || $qty <= 0) {
                continue;
            }

            $key = $product.'|'.($recipe->description ?? $product);
            $somersloopSlots = (int) (request('somersloops', [])[$key] ?? 0);

            $variants = [];
            foreach ([100 => 'c100', 150 => 'c150', 200 => 'c200', 250 => 'c250'] as $clock => $clockKey) {
                $variants[$clockKey] = BuildingOverview::make(
                    $recipe, $qty, $beltSpeed, 'mk1', $clock, $somersloopSlots,
                    $cost_multiplier, $power_multiplier, $building_multiples, $building_cost_multiplier
                )->toArray();
            }

            $overviews[$key] = [
                'clock' => 'c100',
                'selected_variant_name' => $variants['c100']['selected_variant_name'] ?? 'mk1',
                'overviews' => $variants,
                'overview' => $variants['c100'],
            ];
        }

        return $overviews;
    }

    /**
     * The plan-level multiplier params (clamped), read once per request — the single
     * source both overviews() and packagingStep() thread into their calcs (V107).
     *
     * @return array{cost_multiplier: float, power_multiplier: float, building_multiples: array<string, int>, building_cost_multiplier: float}
     */
    protected static function planParams(): array
    {
        return [
            'cost_multiplier' => max(0.1, min(10.0, (float) request('cost_multiplier', 1.0))),
            'power_multiplier' => max(0.1, min(10.0, (float) request('power_multiplier', 1.0))),
            'building_multiples' => collect(request('building_multiples', []))->map(fn ($v) => max(1, (int) $v))->all(),
            'building_cost_multiplier' => max(0.1, min(10.0, (float) request('building_cost_multiplier', 1.0))),
        ];
    }

    /**
     * A Packager build step for one auto-package fluid (real recipe → real footprint).
     * V107: same multiplier + somersloop args as overviews() — the step row must
     * equal its own c100 overview for every multiplier value.
     *
     * @param  array<string, mixed>  $row  a RecyclingCalc packaged row
     * @return array<string, mixed>
     */
    protected static function packagingStep(array $row, float $beltSpeed): array
    {
        $product = $row['product'];
        $qty = (float) ($row['qty'] ?? 0);
        $recipe = optional(Ingredient::ofName($product))->baseRecipe(); // the Package recipe

        [$footprint, $numBuildings, $power] = static::stepDetail(
            $recipe, $product, $qty, $beltSpeed,
            (float) ($row['buildings'] ?? 0), (float) ($row['power'] ?? 0)
        );

        // V94: real recipe input rates — the fluid at its leftover rate AND the empty
        // container — never the packaged output qty reused for the input (B53).
        $inputs = [
            ['item' => $row['fluid'], 'qty' => round((float) ($row['fluid_qty'] ?? $qty), 4)],
        ];

        if (! empty($row['container'])) {
            $inputs[] = ['item' => $row['container'], 'qty' => round((float) ($row['container_qty'] ?? 0), 4)];
        }

        return [
            'type' => 'package',
            'name' => $product,
            'building' => BuildingEnum::PACKAGER->value,
            // V93: recipe description → frontend overview key is `product|recipe.description ?? product`
            'recipe' => optional($recipe)->description,
            'num_buildings' => $numBuildings,
            'power' => round($power, 4),
            'footprint' => $footprint,
            'inputs' => $inputs,
            'output' => ['item' => $product, 'qty' => round($qty, 4), 'to_sink' => true],
        ];
    }

    /**
     * A production step for a container shortfall (V135): the container's base
     * recipe (e.g. Empty Canister ← Plastic, Constructor) sized to the packaging
     * demand. Output feeds the Packager — never `to_sink`.
     *
     * @param  array<string, mixed>  $row  a RecyclingCalc containers row
     * @return array<string, mixed>
     */
    protected static function produceStep(array $row, float $beltSpeed): array
    {
        $product = $row['product'];
        $qty = (float) ($row['qty'] ?? 0);
        $recipe = optional(Ingredient::ofName($product))->baseRecipe();

        [$footprint, $numBuildings, $power] = static::stepDetail(
            $recipe, $product, $qty, $beltSpeed,
            (float) ($row['buildings'] ?? 0), (float) ($row['power'] ?? 0)
        );

        $inputs = collect($row['inputs'] ?? [])
            ->map(fn ($input) => ['item' => $input['item'], 'qty' => round((float) $input['qty'], 4)])
            ->values()
            ->all();

        return [
            'type' => 'produce',
            'name' => $product,
            'building' => $row['building'] ?? optional(optional($recipe)->building)->name,
            'recipe' => optional($recipe)->description,
            'num_buildings' => $numBuildings,
            'power' => round($power, 4),
            'footprint' => $footprint,
            'inputs' => $inputs,
            'output' => ['item' => $product, 'qty' => round($qty, 4)],
        ];
    }

    /**
     * Footprint / building count / power for a recipe-backed recycling step via
     * BuildingDetails::calc (mk1 variant), threading the plan multipliers +
     * somersloops (V107). Falls back to the row's own numbers without a recipe.
     *
     * @return array{0: ?array<string, mixed>, 1: float, 2: float}
     */
    protected static function stepDetail(
        ?object $recipe, string $product, float $qty, float $beltSpeed,
        float $fallbackBuildings, float $fallbackPower
    ): array {
        if (! $recipe) {
            return [null, $fallbackBuildings, $fallbackPower];
        }

        $params = static::planParams();
        $key = $product.'|'.($recipe->description ?? $product);
        $somersloopSlots = (int) (request('somersloops', [])[$key] ?? 0);

        $detail = BuildingDetails::calc(
            $recipe, $qty, $beltSpeed, 100, $somersloopSlots,
            $params['cost_multiplier'], $params['power_multiplier'],
            $params['building_multiples'], $params['building_cost_multiplier']
        )->first(); // mk1 variant row

        if (! $detail) {
            return [null, $fallbackBuildings, $fallbackPower];
        }

        return [$detail['footprint'], $detail['num_buildings'], $detail['power_usage']];
    }

    /**
     * The terminal AWESOME Sink step: consumes every recycled item, outputs points/min.
     * V106: sized by throughput — each sink takes one belt, so count = ceil(rate /
     * belt_speed); power and build cost come from the seeded building (V89).
     *
     * @param  list<array{item: string, qty: float}>  $inputs
     * @return array<string, mixed>
     */
    protected static function sinkStep(array $inputs, float $points, float $beltSpeed): array
    {
        $rate = array_sum(array_column($inputs, 'qty'));
        $count = max(1, (int) ceil($beltSpeed > 0 ? $rate / $beltSpeed : 1));

        $variant = optional(Building::ofName(BuildingEnum::AWESOME_SINK->value))->variant('mk1');
        $basePower = (float) (optional($variant)->base_power ?? 30);

        return [
            'type' => 'sink',
            'name' => BuildingEnum::AWESOME_SINK->value,
            'building' => BuildingEnum::AWESOME_SINK->value,
            'num_buildings' => $count,
            'power' => round($count * $basePower, 4),
            'build_cost' => static::sinkBuildCost($variant, $count),
            'footprint' => static::sinkFootprint($rate, $beltSpeed, $count),
            'inputs' => $inputs,
            'output' => ['points' => round($points, 2)],
        ];
    }

    /**
     * Build cost from the seeded sink building's mk1 variant recipe, scaled by
     * count (same shape as ExtractorSummary::buildCost). Empty when not seeded.
     *
     * @return array<string, int>
     */
    protected static function sinkBuildCost(?object $variant, int $count): array
    {
        if (! $variant) {
            return [];
        }

        return $variant->recipe
            ->mapWithKeys(fn ($ingredient) => [
                $ingredient->name => (int) ceil($ingredient->pivot->qty * $count),
            ])
            ->all();
    }

    /**
     * Footprint for the AWESOME Sink row, mirroring BuildingDetails' formula so the
     * diagram matches the rest of the build. V106: one sink per row — each sink is
     * belt-bound, so rows = count and the per-row belt load stays ≤ belt_speed.
     *
     * @return array<string, mixed>
     */
    protected static function sinkFootprint(float $rate, float $beltSpeed, int $count = 1): array
    {
        $building = Building::ofName(BuildingEnum::AWESOME_SINK->value);

        $length = (float) (optional($building)->length ?? 13);
        $width = (float) (optional($building)->width ?? 16);
        $height = (float) (optional($building)->height ?? 24);

        $rows = max(1, $count);
        $rowRate = $rate / $rows;
        $buildingLengthFoundations = (int) ceil($length / 8);
        $lengthFoundations = $rows * ($buildingLengthFoundations + 2);
        $widthFoundations = (int) (ceil($width / 8) + 4);
        $heightWalls = (int) (ceil($height / 4) + 1);
        $topOffset = (int) ceil((8 * ($buildingLengthFoundations + 2) - $length) / 2);

        return [
            'foundation_border_y' => false,
            'monogram' => substr(BuildingEnum::AWESOME_SINK->value, 0, 1),
            'belt_speed' => $beltSpeed,
            'belt_load' => round($rowRate, 2),
            'belt_load_in' => round($rowRate, 2),
            'belt_load_out' => 0,
            'belt_utilization_in' => $beltSpeed > 0 ? round(100 * $rowRate / $beltSpeed) : 0,
            'belt_utilization_out' => 0,
            'rows' => $rows,
            'num_buildings' => $rows,
            'power_shards' => 0,
            'somersloops' => 0,
            'buildings_per_row' => 1,
            'building_length' => $length,
            'building_length_foundations' => $buildingLengthFoundations,
            'building_width' => $width,
            'length_m' => $rows * $length,
            'length_foundations' => $lengthFoundations,
            'width_m' => $width,
            'width_foundations' => $widthFoundations,
            'height_m' => $height,
            'height_walls' => $heightWalls,
            'foundations' => $lengthFoundations * $widthFoundations,
            'walls' => $heightWalls * (2 * ($lengthFoundations + $widthFoundations)),
            'row_spacing' => 0,
            'top_offset' => $topOffset,
            'bottom_offset' => (int) floor((8 * ($buildingLengthFoundations + 2) - $length) / 2),
            'row_spacing_offset' => $topOffset + $length,
            'left_offset' => 16 + (8 * ($widthFoundations - 4) - $width) / 2,
            'building_top_offset' => fmod($length, 2) ? 0.5 : 0,
        ];
    }
}
