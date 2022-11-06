<?php

namespace App\Production;

use App\Models\Recipe;
use Illuminate\Support\Collection;

class BuildingDetails extends Collection
{
    /**
     * @var \App\Models\Recipe
     */
    protected $recipe;

    protected $qty;

    protected $belt_speed;

    // even rows
    protected $even = false;

    protected $base_clock = 100;

    public static function calc(Recipe $recipe, $qty, $belt_speed = 720, $base_clock = 100): static
    {
        return (new static)
            ->setRecipe($recipe)
            ->setQty($qty)
            ->setBeltSpeed($belt_speed)
            ->setBaseClock($base_clock)
            ->setEven(request('even',false))
            ->getBuildingDetails();
    }

    protected function setRecipe(Recipe $recipe): static
    {
        $this->recipe = $recipe;

        return $this;
    }

    protected function setQty($qty): static
    {
        $this->qty = $qty;

        return $this;
    }

    protected function setBeltSpeed($belt_speed): static
    {
        $this->belt_speed = $belt_speed;

        return $this;
    }

    protected function setBaseClock($base_clock): static
    {
        $this->base_clock = $base_clock;

        return $this;
    }

    public function setEven($even): static
    {
        $this->even = $even;

        return $this;
    }

    protected function getBuildingDetails(): static
    {
        $this->items = $this->recipe->building->variants->map(function ($variant) {
            // calc number of buildings needed
            $num_buildings = 1 * ceil($this->qty / $this->recipe->base_per_min / $variant->multiplier / ($this->base_clock/100));

            // calc the clock speed for the buildings
            $clock_speed = 1 * round(100 * $this->qty / $num_buildings / $this->recipe->base_per_min / $variant->multiplier, 4);

            // calc shards per building
            $shards_per_building = match(true) {
                $clock_speed > 200 => 3,
                $clock_speed > 150 => 2,
                $clock_speed > 100 => 1,
                default => 0
            };

            // calc the number of power shards
            $power_shards = $num_buildings * $shards_per_building;

            // calc the power_usage for the buildings
            $power_usage = 1 * round(1 * $num_buildings * $variant->calculatePowerUsage($clock_speed / 100), 6);

            // calc the energy used per item
            $mj_per_s = $variant->calculatePowerUsage($clock_speed / 100);
            $s_per_min = 60;
            $mj_per_min = $mj_per_s * $s_per_min;
            $min_per_item = 1 / ($this->recipe->base_per_min * $clock_speed/100);
            $energy_per_item = $mj_per_min * $min_per_item;

            // calc the total energy used
            $total_energy = $energy_per_item * $this->qty;

            // calc the build cost
            $build_cost = $variant->recipe->map(function ($ingredient) use ($num_buildings) {
                return [$ingredient->name => $ingredient->pivot->qty * $num_buildings];
            })->collapse();

            // calculate the max belt load
            $belt_load_in = $this->recipe->ingredients->map(function ($ingredient) use ($num_buildings,$clock_speed,$variant) {
                return $ingredient->pivot->base_qty * $num_buildings * $clock_speed * $variant->multiplier / 100;
            })->max();

            // calc the number of rows needed
            $rows = (int) max(ceil($belt_load_in / $this->belt_speed), 1, ceil($this->qty / $this->belt_speed));

            // calc the footprint
            //$rows = ceil($num_buildings/16); // max 16 buildings per row
            $buildings_per_row = min($num_buildings, ceil($num_buildings/$rows) );

            // force even rows
            if($this->even) {
                $num_buildings = $rows * $buildings_per_row;
                $clock_speed = 1 * round(100 * $this->qty / $num_buildings / $this->recipe->base_per_min / $variant->multiplier, 4);
                $shards_per_building = match(true) {
                    $clock_speed > 200 => 3,
                    $clock_speed > 150 => 2,
                    $clock_speed > 100 => 1,
                    default => 0
                };
                $power_shards = $num_buildings * $shards_per_building;
                $power_usage = 1 * round(1 * $num_buildings * $variant->calculatePowerUsage($clock_speed / 100), 6);
                // calc the energy used per item
                $energy_per_item = $power_usage / ($this->recipe->base_per_min * $clock_speed);
                // calc the total energy used
                $total_energy = $energy_per_item * $this->qty;
                $build_cost = $variant->recipe->map(function ($ingredient) use ($num_buildings) {
                    return [$ingredient->name => $ingredient->pivot->qty * $num_buildings];
                })->collapse();
            }

            $footprint = [
                'foundation_border_y' => $foundation_border_y = ($rows > 1),
                'monogram' => $this->recipe->building->name[0],
                'belt_speed' => $this->belt_speed,
                'belt_load' => $belt_load_in,
                'rows' => $rows,
                'num_buildings' => $num_buildings,
                'power_shards' => $power_shards,
                'buildings_per_row' => $buildings_per_row,
                'building_length' => $building_length = $this->recipe->building->length,
                'building_length_foundations' => $building_length_foundations = ceil($this->recipe->building->length/8),
                'building_width' => $building_width = $this->recipe->building->width,
                'length_m' => $length = $rows * $this->recipe->building->length,
                'length_foundations' => $length_foundations = ($foundation_border_y ? 2 : 0) + $rows * ($building_length_foundations+2), //ceil($length/8) + ($rows > 1 ? (ceil(2*($rows+1.2))) : 2),
                'width_m' => $width = $this->recipe->building->width * $buildings_per_row,
                'width_foundations' => $width_foundations = ( ceil($width/8) + 4),
                'height_m' => $height = $this->recipe->building->height,
                'height_walls' => $height_walls = ceil($height/4) + 1,
                'foundations' => $foundations = $length_foundations * $width_foundations,
                'walls' => $height_walls * (2*($length_foundations + $width_foundations)),
                'row_spacing' => ($rows === 1) ? 0 : 8 * ($building_length_foundations+2) - $building_length,
                'top_offset' => $top_offset = ceil((8 * ($building_length_foundations+2) - $building_length ) / 2 + ($foundation_border_y ? 8 : 0)),
                'bottom_offset' => $top_offset = floor((8 * ($building_length_foundations+2) - $building_length ) / 2 + ($foundation_border_y ? 8 : 0)),
                'row_spacing_offset' => $top_offset + $building_length,
                'left_offset' => 16 + (8*($width_foundations-4) - $building_width*$buildings_per_row)/2,
                'building_top_offset' => $building_length%2 ? 0.5 : 0,
            ];

            return [
                "{$this->recipe->building->name} ($variant->name)" => ['variant' => $variant->name] +
                    compact('num_buildings', 'clock_speed', 'power_usage', 'energy_per_item', 'total_energy', 'build_cost','footprint'),
            ];
        })->collapse()->all();

        return $this;
    }
}
