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
