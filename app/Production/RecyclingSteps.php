<?php

namespace App\Production;

use App\Enums\Building as BuildingEnum;
use App\Models\Building;

/**
 * V88/V137: turn the recycling result (RecyclingCalc) into the terminal
 * AWESOME Sink step descriptor. Packaging + container-chain production render
 * as normal plan steps (injected calcs, V137) — the Recycling group carries
 * ONLY the sink:
 *
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

        if (empty($recycled) && empty($packaged)) {
            return [];
        }

        $sinkInputs = [];

        // direct sinkable byproducts feed the sink
        foreach ($recycled as $name => $info) {
            $sinkInputs[] = ['item' => $name, 'qty' => round((float) ($info['qty'] ?? 0), 4)];
        }

        // each injected packaging calc's output (the Packaged form) feeds the sink
        foreach ($packaged as $row) {
            $sinkInputs[] = ['item' => $row['product'], 'qty' => round((float) ($row['qty'] ?? 0), 4)];
        }

        return [static::sinkStep($sinkInputs, (float) ($recycling['points'] ?? 0), $beltSpeed)];
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
