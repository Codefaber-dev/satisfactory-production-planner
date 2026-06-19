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

        if (empty($recycled) && empty($packaged)) {
            return [];
        }

        $steps = [];
        $sinkInputs = [];

        // direct sinkable byproducts feed the sink
        foreach ($recycled as $name => $info) {
            $sinkInputs[] = ['item' => $name, 'qty' => round((float) ($info['qty'] ?? 0), 4)];
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
     * A Packager build step for one auto-package fluid (real recipe → real footprint).
     *
     * @param  array<string, mixed>  $row  a RecyclingCalc packaged row
     * @return array<string, mixed>
     */
    protected static function packagingStep(array $row, float $beltSpeed): array
    {
        $product = $row['product'];
        $qty = (float) ($row['qty'] ?? 0);
        $recipe = optional(Ingredient::ofName($product))->baseRecipe(); // the Package recipe

        $footprint = null;
        $numBuildings = (float) ($row['buildings'] ?? 0);
        $power = (float) ($row['power'] ?? 0);

        if ($recipe) {
            $detail = BuildingDetails::calc($recipe, $qty, $beltSpeed)
                ->first(); // mk1 variant row

            if ($detail) {
                $footprint = $detail['footprint'];
                $numBuildings = $detail['num_buildings'];
                $power = $detail['power_usage'];
            }
        }

        return [
            'type' => 'package',
            'name' => $product,
            'building' => BuildingEnum::PACKAGER->value,
            'num_buildings' => $numBuildings,
            'power' => round($power, 4),
            'footprint' => $footprint,
            'inputs' => [['item' => $row['fluid'], 'qty' => round($qty, 4)]],
            'output' => ['item' => $product, 'qty' => round($qty, 4), 'to_sink' => true],
        ];
    }

    /**
     * The terminal AWESOME Sink step: consumes every recycled item, outputs points/min.
     *
     * @param  list<array{item: string, qty: float}>  $inputs
     * @return array<string, mixed>
     */
    protected static function sinkStep(array $inputs, float $points, float $beltSpeed): array
    {
        $rate = array_sum(array_column($inputs, 'qty'));

        return [
            'type' => 'sink',
            'name' => BuildingEnum::AWESOME_SINK->value,
            'building' => BuildingEnum::AWESOME_SINK->value,
            'num_buildings' => 1,
            'power' => 30.0,
            'footprint' => static::sinkFootprint($rate, $beltSpeed),
            'inputs' => $inputs,
            'output' => ['points' => round($points, 2)],
        ];
    }

    /**
     * Footprint for a single AWESOME Sink (rows = 1), mirroring BuildingDetails' formula
     * so the diagram matches the rest of the build.
     *
     * @return array<string, mixed>
     */
    protected static function sinkFootprint(float $rate, float $beltSpeed): array
    {
        $building = Building::ofName(BuildingEnum::AWESOME_SINK->value);

        $length = (float) (optional($building)->length ?? 13);
        $width = (float) (optional($building)->width ?? 16);
        $height = (float) (optional($building)->height ?? 24);

        $rows = 1;
        $buildingLengthFoundations = (int) ceil($length / 8);
        $lengthFoundations = $rows * ($buildingLengthFoundations + 2);
        $widthFoundations = (int) (ceil($width / 8) + 4);
        $heightWalls = (int) (ceil($height / 4) + 1);
        $topOffset = (int) ceil((8 * ($buildingLengthFoundations + 2) - $length) / 2);

        return [
            'foundation_border_y' => false,
            'monogram' => substr(BuildingEnum::AWESOME_SINK->value, 0, 1),
            'belt_speed' => $beltSpeed,
            'belt_load' => round($rate, 2),
            'belt_load_in' => round($rate, 2),
            'belt_load_out' => 0,
            'belt_utilization_in' => $beltSpeed > 0 ? round(100 * $rate / $beltSpeed) : 0,
            'belt_utilization_out' => 0,
            'rows' => $rows,
            'num_buildings' => 1,
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
