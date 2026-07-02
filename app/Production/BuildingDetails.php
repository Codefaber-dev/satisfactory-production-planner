<?php

namespace App\Production;

use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BuildingDetails extends Collection
{
    // Somersloop max slots per building type (wiki: satisfactory.wiki.gg/wiki/Somersloop)
    const SLOTS = [
        'Smelter' => 1,
        'Constructor' => 1,
        'Foundry' => 2,
        'Assembler' => 2,
        'Refinery' => 2,
        'Manufacturer' => 4,
        'Blender' => 4,
        'Particle Accelerator' => 4,
        'Quantum Encoder' => 4,
        'Converter' => 2,
        // Packager => 0 (no slots)
    ];

    /**
     * @var Recipe
     */
    protected $recipe;

    protected $qty;

    protected $belt_speed;

    // even rows
    protected $even = false;

    protected $base_clock = 100;

    protected $somersloop_slots = 0;

    protected $cost_multiplier = 1.0;

    protected $plan_power_multiplier = 1.0;

    protected $building_multiples = [];

    protected $building_cost_multiplier = 1.0;

    public static function calc(Recipe $recipe, $qty, $belt_speed = 720, $base_clock = 100, $somersloop_slots = 0, $cost_multiplier = 1.0, $power_multiplier = 1.0, $building_multiples = [], $building_cost_multiplier = 1.0): static
    {
        return (new static)
            ->setRecipe($recipe)
            ->setQty($qty)
            ->setBeltSpeed($belt_speed)
            ->setBaseClock($base_clock)
            ->setSomersloopSlots($somersloop_slots)
            ->setCostMultiplier($cost_multiplier)
            ->setPlanPowerMultiplier($power_multiplier)
            ->setBuildingMultiples($building_multiples)
            ->setBuildingCostMultiplier($building_cost_multiplier)
            ->setEven(request('even', false))
            ->getBuildingDetails();
    }

    protected function setSomersloopSlots(int $slots): static
    {
        $max = self::SLOTS[$this->recipe->building->name] ?? 0;
        $this->somersloop_slots = max(0, min($slots, $max));

        return $this;
    }

    protected function setCostMultiplier(float $multiplier): static
    {
        $this->cost_multiplier = max(0.1, min(10.0, $multiplier));

        return $this;
    }

    protected function setPlanPowerMultiplier(float $multiplier): static
    {
        $this->plan_power_multiplier = max(0.1, min(10.0, $multiplier));

        return $this;
    }

    protected function setBuildingMultiples(array $multiples): static
    {
        $this->building_multiples = $multiples;

        return $this;
    }

    protected function setBuildingCostMultiplier(float $multiplier): static
    {
        $this->building_cost_multiplier = max(0.1, min(10.0, $multiplier));

        return $this;
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
        $building_name = $this->recipe->building->name;
        $max_slots = self::SLOTS[$building_name] ?? 0;
        $slots = $this->somersloop_slots;
        $amplifier = $max_slots > 0 ? (1 + $slots / $max_slots) : 1.0;
        $somersloop_power_amp = $max_slots > 0 ? (1 + 3 * $slots / $max_slots) : 1.0;
        $effective_base_per_min = $this->recipe->base_per_min * $amplifier;
        $plan_power_multiplier = $this->plan_power_multiplier;
        $multiple = max(1, (int) ($this->building_multiples[$building_name] ?? 1));

        $this->items = $this->recipe->building->variants->map(function ($variant) use ($max_slots, $slots, $effective_base_per_min, $somersloop_power_amp, $plan_power_multiplier, $multiple) {
            // exact fractional machine count at 100% clock, before any rounding —
            // the raw demand Perfect Ratio (V56) scales to a whole number. Independent
            // of base_clock, ceil, and building_multiples on purpose.
            $exact_buildings = $effective_base_per_min > 0 && $variant->multiplier > 0
                ? $this->qty / $effective_base_per_min / $variant->multiplier
                : 0;

            // calc number of buildings needed, assuming belts can handle it
            $num_buildings = 1 * ceil($this->qty / $effective_base_per_min / $variant->multiplier / ($this->base_clock / 100));
            if ($multiple > 1) {
                $num_buildings = (int) ceil($num_buildings / $multiple) * $multiple;
            }

            // calc the clock speed for the buildings
            $clock_speed = 1 * round(100 * $this->qty / $num_buildings / $effective_base_per_min / $variant->multiplier, 4);

            // calc shards per building
            $shards_per_building = match (true) {
                $clock_speed > 200 => 3,
                $clock_speed > 150 => 2,
                $clock_speed > 100 => 1,
                default => 0
            };

            // calc the max clock speed
            $max_clock_speed = match (true) {
                $clock_speed > 200 => 250,
                $clock_speed > 150 => 200,
                $clock_speed > 100 => 150,
                default => 100
            };

            // calc the number of power shards
            $power_shards = $num_buildings * $shards_per_building;

            // calc the power_usage for the buildings (somersloop amplifies power up to 4× at max slots)
            $power_usage = 1 * round(1 * $num_buildings * $variant->calculatePowerUsage($clock_speed / 100) * $somersloop_power_amp * $plan_power_multiplier, 6);

            // calc the energy used per item
            $mj_per_s = $variant->calculatePowerUsage($clock_speed / 100);
            $s_per_min = 60;
            $mj_per_min = $mj_per_s * $s_per_min;
            $min_per_item = 1 / ($this->recipe->base_per_min * $clock_speed / 100);
            $energy_per_item = $mj_per_min * $min_per_item * $plan_power_multiplier;

            // calc the total energy used
            $total_energy = $energy_per_item * $this->qty;

            // calc the build cost
            $build_cost = $variant->recipe->map(function ($ingredient) use ($num_buildings) {
                return [$ingredient->name => ceil($ingredient->pivot->qty * $num_buildings * $this->building_cost_multiplier)];
            })->collapse();

            // calculate the max belt load (cost_multiplier scales ingredient consumption)
            $belt_load_in = $this->recipe->ingredients->reject(fn ($ing) => $ing->is_liquid)
                ->map(function ($ingredient) use ($num_buildings, $clock_speed, $variant) {
                    return $ingredient->pivot->base_qty * $num_buildings * $clock_speed * $variant->multiplier / 100 * $this->cost_multiplier;
                })->max();

            // calc the number of rows needed
            $rows = (int) max(ceil($belt_load_in / $this->belt_speed), 1, ceil($this->qty / $this->belt_speed));
            $input_rows = (int) max(ceil($belt_load_in / $this->belt_speed), 1);
            $output_rows = (int) max(ceil($this->qty / $this->belt_speed), 1);

            // set the alternate rows based on limit settings
            if (request('speedLimit') === 'outputs') {
                $rows = $output_rows;
            }
            if (request('speedLimit') === 'inputs') {
                $rows = $input_rows;
            }

            // calc the footprint
            // $rows = ceil($num_buildings/16); // max 16 buildings per row
            $buildings_per_row = min($num_buildings, ceil($num_buildings / $rows));

            // building delta, check if belts are too slow for nominal building count
            $building_delta = ($rows * $buildings_per_row) - $num_buildings;

            // force even rows — grid-fill auto-trigger superseded by blueprint grouping:
            // a grouped count must stay an exact multiple of the group size (V44).
            // explicit even with grouping rounds the stamp count up to an even number (V46)
            $adjusted_buildings = null;
            if ($multiple === 1 && ($this->even || $building_delta > 1)) {
                // Log::debug("Building delta: $building_delta");
                $adjusted_buildings = $rows * $buildings_per_row;
            } elseif ($multiple > 1 && $this->even) {
                // grid-fill for stamps (V50): bump the stamp count so the grouped
                // layout rows — near-square floored by belt-required rows, the
                // same rule the diagram uses — come out full, no ragged last row.
                // A grid that is already full stays untouched.
                // rows stays belt-derived: the bump keeps total throughput
                // constant (clock drops as count rises), so belt load per row
                // is unchanged and footprint.rows remains the belt minimum (V47)
                $stamps = (int) round($num_buildings / $multiple);
                $layout_rows = (int) min($stamps, max(ceil($stamps / ceil(sqrt($stamps))), $rows));
                $stamps_per_row = (int) ceil($stamps / $layout_rows);
                if ($stamps !== $layout_rows * $stamps_per_row) {
                    $adjusted_buildings = $layout_rows * $stamps_per_row * $multiple;
                }
            }
            if ($adjusted_buildings !== null) {
                $num_buildings = $adjusted_buildings;
                $buildings_per_row = min($num_buildings, ceil($num_buildings / $rows));
                $clock_speed = 1 * round(100 * $this->qty / $num_buildings / $effective_base_per_min / $variant->multiplier, 4);
                $shards_per_building = match (true) {
                    $clock_speed > 200 => 3,
                    $clock_speed > 150 => 2,
                    $clock_speed > 100 => 1,
                    default => 0
                };
                // calc the max clock speed
                $max_clock_speed = match (true) {
                    $clock_speed > 200 => 250,
                    $clock_speed > 150 => 200,
                    $clock_speed > 100 => 150,
                    default => 100
                };
                $power_shards = $num_buildings * $shards_per_building;
                $power_usage = 1 * round(1 * $num_buildings * $variant->calculatePowerUsage($clock_speed / 100) * $somersloop_power_amp * $plan_power_multiplier, 6);
                $build_cost = $variant->recipe->map(function ($ingredient) use ($num_buildings) {
                    return [$ingredient->name => ceil($ingredient->pivot->qty * $num_buildings * $this->building_cost_multiplier)];
                })->collapse();
                $mj_per_s = $variant->calculatePowerUsage($clock_speed / 100);
                $mj_per_min = $mj_per_s * 60;
                $min_per_item = 1 / ($this->recipe->base_per_min * $clock_speed / 100);
                $energy_per_item = $mj_per_min * $min_per_item * $plan_power_multiplier;
                $total_energy = $energy_per_item * $this->qty;
            }

            $footprint = [
                'foundation_border_y' => $foundation_border_y = ($rows > 1),
                'monogram' => $this->recipe->building->name[0],
                'belt_speed' => $this->belt_speed,
                'belt_load' => $belt_load_in,
                'belt_load_in' => round($belt_load_in / $rows, 2),
                'belt_load_out' => round($this->qty / $rows, 2),
                'belt_utilization_in' => round(100 * $belt_load_in / $rows / $this->belt_speed),
                'belt_utilization_out' => round(100 * $this->qty / $rows / $this->belt_speed),
                'rows' => $rows,
                'num_buildings' => $num_buildings,
                'power_shards' => $power_shards,
                'somersloops' => $num_buildings * $slots,
                'buildings_per_row' => $buildings_per_row,
                'building_length' => $building_length = $this->recipe->building->length,
                'building_length_foundations' => $building_length_foundations = ceil($this->recipe->building->length / 8),
                'building_width' => $building_width = $this->recipe->building->width,
                'length_m' => $length = $rows * $this->recipe->building->length,
                'length_foundations' => $length_foundations = ($foundation_border_y ? 2 : 0) + $rows * ($building_length_foundations + 2), // ceil($length/8) + ($rows > 1 ? (ceil(2*($rows+1.2))) : 2),
                'width_m' => $width = $this->recipe->building->width * $buildings_per_row,
                'width_foundations' => $width_foundations = (ceil($width / 8) + 4),
                'height_m' => $height = $this->recipe->building->height,
                'height_walls' => $height_walls = ceil($height / 4) + 1,
                'foundations' => $foundations = $length_foundations * $width_foundations,
                'walls' => $height_walls * (2 * ($length_foundations + $width_foundations)),
                'row_spacing' => ($rows === 1) ? 0 : 8 * ($building_length_foundations + 2) - $building_length,
                'top_offset' => $top_offset = ceil((8 * ($building_length_foundations + 2) - $building_length) / 2 + ($foundation_border_y ? 8 : 0)),
                'bottom_offset' => $top_offset = floor((8 * ($building_length_foundations + 2) - $building_length) / 2 + ($foundation_border_y ? 8 : 0)),
                'row_spacing_offset' => $top_offset + $building_length,
                'left_offset' => 16 + (8 * ($width_foundations - 4) - $building_width * $buildings_per_row) / 2,
                'building_top_offset' => $building_length % 2 ? 0.5 : 0,
            ];

            return [
                "{$this->recipe->building->name} ($variant->name)" => ['variant' => $variant->name] +
                    compact('num_buildings', 'exact_buildings', 'clock_speed', 'power_usage', 'energy_per_item', 'total_energy', 'build_cost', 'footprint', 'max_clock_speed', 'max_slots', 'slots', 'multiple'),
            ];
        })->collapse()->all();

        return $this;
    }
}
