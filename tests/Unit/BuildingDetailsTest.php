<?php

namespace Tests\Unit;

use App\Production\BuildingDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BuildingDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
    }

    #[Test]
    public function normal_path_energy_formula_holds(): void
    {
        $recipe = r('Iron Plate');
        // Small qty → 1 building at ~100% clock, building_delta <= 1 (no even-rows branch)
        $qty = $recipe->base_per_min;

        $details = BuildingDetails::calc($recipe, $qty, 780)->first();

        $clock = $details['clock_speed'];
        $variant = $recipe->building->variant('mk1');
        $expected = 60 * $variant->calculatePowerUsage($clock / 100)
            / ($recipe->base_per_min * $clock / 100);

        $this->assertEqualsWithDelta($expected, $details['energy_per_item'], 1e-9);
        $this->assertEqualsWithDelta($expected * $qty, $details['total_energy'], 1e-9);
    }

    #[Test]
    public function even_rows_branch_uses_updated_clock_for_energy(): void
    {
        // base_per_min * 1000 with belt_speed=780 → building_delta > 1 → even-rows branch fires
        $recipe = r('Iron Plate');
        $qty = $recipe->base_per_min * 1000;

        $details = BuildingDetails::calc($recipe, $qty, 780)->first();

        // Even-rows adjusts num_buildings up → clock_speed drops below 100%
        $this->assertLessThan(100.0, $details['clock_speed'],
            'Even-rows branch must reduce clock_speed below 100%');

        // energy_per_item must use the post-adjustment clock_speed (B18 regression guard)
        $clock = $details['clock_speed'];
        $variant = $recipe->building->variant('mk1');
        $expected = 60 * $variant->calculatePowerUsage($clock / 100)
            / ($recipe->base_per_min * $clock / 100);

        $this->assertEqualsWithDelta($expected, $details['energy_per_item'], 1e-9);
        $this->assertEqualsWithDelta($expected * $qty, $details['total_energy'], 1e-9);
    }

    #[Test]
    public function total_energy_equals_energy_per_item_times_qty(): void
    {
        $recipe = r('Iron Plate');

        foreach ([$recipe->base_per_min, $recipe->base_per_min * 10, $recipe->base_per_min * 1000] as $qty) {
            $details = BuildingDetails::calc($recipe, $qty, 780)->first();

            $this->assertEqualsWithDelta(
                $details['energy_per_item'] * $qty,
                $details['total_energy'],
                1e-9,
                "total_energy mismatch at qty={$qty}"
            );
        }
    }

    #[Test]
    public function somersloop_slots_reduce_num_buildings(): void
    {
        // Assembler has 2 slots; 1 slot → 1.5× amplifier → fewer buildings needed
        $recipe = r('Reinforced Iron Plate');
        $qty = $recipe->base_per_min * 3;

        $noSlots = BuildingDetails::calc($recipe, $qty, 780, 100, 0)->first();
        $oneSlot = BuildingDetails::calc($recipe, $qty, 780, 100, 1)->first();

        $this->assertLessThanOrEqual($noSlots['num_buildings'], $oneSlot['num_buildings'],
            'Somersloop should not require more buildings than base');
        $this->assertSame(2, $noSlots['max_slots']);
        $this->assertSame(1, $oneSlot['slots']);
    }

    #[Test]
    public function somersloop_max_slots_increases_power_usage(): void
    {
        // Assembler 2 slots at max → power_multiplier = 4×; power per building is higher
        $recipe = r('Reinforced Iron Plate');
        $qty = $recipe->base_per_min;

        $noSlots = BuildingDetails::calc($recipe, $qty, 780, 100, 0)->first();
        $maxSlots = BuildingDetails::calc($recipe, $qty, 780, 100, 2)->first();

        $this->assertGreaterThan($noSlots['power_usage'], $maxSlots['power_usage'],
            'Max somersloop slots must increase power_usage');
    }

    #[Test]
    public function somersloop_slots_clamped_to_max(): void
    {
        // Requesting 10 slots on Assembler (max=2) should clamp to 2
        $recipe = r('Reinforced Iron Plate');
        $qty = $recipe->base_per_min;

        $clamped = BuildingDetails::calc($recipe, $qty, 780, 100, 10)->first();
        $maxed = BuildingDetails::calc($recipe, $qty, 780, 100, 2)->first();

        $this->assertSame($maxed['num_buildings'], $clamped['num_buildings']);
        $this->assertSame($maxed['power_usage'], $clamped['power_usage']);
    }

    #[Test]
    public function somersloop_zero_slots_on_unsupported_building_has_no_effect(): void
    {
        // Packager has no slots; requesting slots should have no effect
        $recipe = r('Packaged Water');
        $qty = $recipe->base_per_min;

        $noSlots = BuildingDetails::calc($recipe, $qty, 780, 100, 0)->first();
        $attempted = BuildingDetails::calc($recipe, $qty, 780, 100, 2)->first();

        $this->assertSame($noSlots['num_buildings'], $attempted['num_buildings']);
        $this->assertSame($noSlots['power_usage'], $attempted['power_usage']);
        $this->assertSame(0, $attempted['max_slots']);
    }

    #[Test]
    public function cost_multiplier_scales_belt_load_in_without_changing_building_count(): void
    {
        // Assembler — has solid ingredients; Iron Plate base recipe uses Iron Ore (solid)
        $recipe = r('Reinforced Iron Plate');
        $qty = $recipe->base_per_min * 2;

        $base = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0)->first();
        $doubled = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 2.0)->first();

        // Building count and output unchanged
        $this->assertSame($base['num_buildings'], $doubled['num_buildings']);
        $this->assertSame($base['clock_speed'], $doubled['clock_speed']);

        // Belt load in should double
        $this->assertEqualsWithDelta(
            $base['footprint']['belt_load_in'] * 2,
            $doubled['footprint']['belt_load_in'],
            1e-6
        );
    }

    #[Test]
    public function cost_multiplier_clamped_to_valid_range(): void
    {
        $recipe = r('Iron Plate');
        $qty = $recipe->base_per_min;

        $tooLow = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 0.0)->first();
        $tooHigh = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 100.0)->first();
        $atMin = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 0.1)->first();
        $atMax = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 10.0)->first();

        // Clamped values match boundary values
        $this->assertSame($atMin['footprint']['belt_load_in'], $tooLow['footprint']['belt_load_in']);
        $this->assertSame($atMax['footprint']['belt_load_in'], $tooHigh['footprint']['belt_load_in']);
    }

    #[Test]
    public function power_multiplier_scales_power_usage_without_changing_building_count(): void
    {
        $recipe = r('Reinforced Iron Plate');
        $qty = $recipe->base_per_min * 2;

        $base = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 1.0)->first();
        $doubled = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 2.0)->first();

        $this->assertSame($base['num_buildings'], $doubled['num_buildings']);
        $this->assertSame($base['clock_speed'], $doubled['clock_speed']);

        $this->assertEqualsWithDelta($base['power_usage'] * 2, $doubled['power_usage'], 1e-6);
        $this->assertEqualsWithDelta($base['energy_per_item'] * 2, $doubled['energy_per_item'], 1e-9);
        $this->assertEqualsWithDelta($base['total_energy'] * 2, $doubled['total_energy'], 1e-9);
    }

    #[Test]
    public function power_multiplier_clamped_to_valid_range(): void
    {
        $recipe = r('Iron Plate');
        $qty = $recipe->base_per_min;

        $tooLow = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 0.0)->first();
        $tooHigh = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 100.0)->first();
        $atMin = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 0.1)->first();
        $atMax = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 10.0)->first();

        $this->assertSame($atMin['power_usage'], $tooLow['power_usage']);
        $this->assertSame($atMax['power_usage'], $tooHigh['power_usage']);
    }

    #[Test]
    public function building_multiple_rounds_up_num_buildings(): void
    {
        // Iron Rod: Constructor, base_per_min=15; qty=45 → needs exactly 3 buildings at 100%
        $recipe = r('Iron Rod');
        $qty = $recipe->base_per_min * 3;

        $base = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 1.0, [])->first();
        $multiple4 = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 1.0, ['Constructor' => 4])->first();

        $this->assertEquals(3, $base['num_buildings']);
        $this->assertEquals(4, $multiple4['num_buildings']);
        $this->assertEquals(0, $multiple4['num_buildings'] % 4, 'num_buildings must be divisible by 4');
    }

    #[Test]
    public function building_multiple_one_is_no_op(): void
    {
        $recipe = r('Iron Rod');
        $qty = $recipe->base_per_min * 3;

        $base = BuildingDetails::calc($recipe, $qty, 780, 100, 0)->first();
        $multiple1 = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 1.0, ['Constructor' => 1])->first();

        $this->assertSame($base['num_buildings'], $multiple1['num_buildings']);
    }

    #[Test]
    public function building_multiple_only_applies_to_matching_building(): void
    {
        // Iron Rod uses Constructor; Smelter multiple has no effect
        $recipe = r('Iron Rod');
        $qty = $recipe->base_per_min * 3;

        $base = BuildingDetails::calc($recipe, $qty, 780, 100, 0)->first();
        $mismatch = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 1.0, ['Smelter' => 4])->first();

        $this->assertSame($base['num_buildings'], $mismatch['num_buildings']);
    }

    #[Test]
    public function building_multiple_supersedes_even_rows_auto_trigger(): void
    {
        // Large qty → building_delta > 1 would fire the even-rows branch and inflate
        // num_buildings to rows × buildings_per_row, breaking the multiple (V44)
        $recipe = r('Iron Plate');
        $qty = $recipe->base_per_min * 1000;

        $grouped = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 1.0, ['Constructor' => 7])->first();

        $this->assertEquals(0, $grouped['num_buildings'] % 7,
            'Grouped num_buildings must stay an exact multiple of the group size');
    }

    #[Test]
    public function building_multiple_supersedes_explicit_even_setting(): void
    {
        $recipe = r('Iron Plate');
        $qty = $recipe->base_per_min * 1000;

        request()->merge(['even' => 1]);
        $grouped = BuildingDetails::calc($recipe, $qty, 780, 100, 0, 1.0, 1.0, ['Constructor' => 7])->first();
        request()->merge(['even' => null]);

        $this->assertEquals(0, $grouped['num_buildings'] % 7,
            'Explicit even setting must not break the group multiple');
    }

    #[Test]
    public function even_rows_still_applies_to_ungrouped_buildings(): void
    {
        // Same large qty without a multiple: even-rows auto-trigger fills the grid
        $recipe = r('Iron Plate');
        $qty = $recipe->base_per_min * 1000;

        $details = BuildingDetails::calc($recipe, $qty, 780)->first();
        $footprint = $details['footprint'];

        $this->assertSame(
            $footprint['rows'] * $footprint['buildings_per_row'],
            $details['num_buildings'],
            'Ungrouped even-rows behavior must remain: grid fully filled'
        );
    }

    #[Test]
    public function energy_per_item_is_consistent_across_qty_scales(): void
    {
        // energy_per_item should remain stable at small qty (no even-rows);
        // it may change at large qty due to even-rows clock adjustment.
        // Both values must still satisfy the formula.
        $recipe = r('Wire');

        $smallDetails = BuildingDetails::calc($recipe, $recipe->base_per_min, 780)->first();
        $largeDetails = BuildingDetails::calc($recipe, $recipe->base_per_min * 1000, 780)->first();

        foreach ([$smallDetails, $largeDetails] as $details) {
            $clock = $details['clock_speed'];
            $variant = $recipe->building->variant('mk1');
            $expected = 60 * $variant->calculatePowerUsage($clock / 100)
                / ($recipe->base_per_min * $clock / 100);

            $this->assertEqualsWithDelta($expected, $details['energy_per_item'], 1e-9);
        }
    }
}
