<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Production\ProductionCalculator;
use App\Production\RecyclingCalc;
use App\Production\RecyclingSteps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T127 / V88: recycling rendered as full build-step descriptors — a Packager step
 * per auto-package fluid (real footprint) and a terminal AWESOME Sink step.
 */
class RecyclingStepsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    private function recycling(bool $autoPackage): array
    {
        $calc = ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic');

        return RecyclingCalc::calc($calc->getByproducts(), $calc->getByproductsUsed(), $autoPackage);
    }

    #[Test]
    public function builds_a_packaging_step_with_a_real_footprint_and_a_sink_step(): void
    {
        $steps = RecyclingSteps::build($this->recycling(true), 780);

        // a Packager packaging step + the terminal sink
        $package = collect($steps)->firstWhere('type', 'package');
        $sink = collect($steps)->firstWhere('type', 'sink');

        $this->assertNotNull($package, 'expected a packaging step');
        $this->assertSame('Packaged Heavy Oil Residue', $package['name']);
        $this->assertSame('Packager', $package['building']);

        // V94: real recipe input rates — the fluid AND the Empty Canister, never the
        // packaged output qty reused for the fluid input (B53).
        $recycling = $this->recycling(true);
        $packagedRow = collect($recycling['packaged'])->firstWhere('product', 'Packaged Heavy Oil Residue');

        $items = collect($package['inputs'])->pluck('item')->all();
        $this->assertContains('Heavy Oil Residue', $items);
        $this->assertContains('Empty Canister', $items, 'packaging step must surface the empty container input');

        $fluidInput = collect($package['inputs'])->firstWhere('item', 'Heavy Oil Residue');
        $containerInput = collect($package['inputs'])->firstWhere('item', 'Empty Canister');
        // fluid input = the recipe fluid rate (fluid_qty), NOT the packaged output qty
        $this->assertEqualsWithDelta($packagedRow['fluid_qty'], $fluidInput['qty'], 0.001);
        $this->assertEqualsWithDelta($packagedRow['container_qty'], $containerInput['qty'], 0.001);
        // output = the packaged base-rate
        $this->assertEqualsWithDelta($packagedRow['qty'], $package['output']['qty'], 0.001);
        $this->assertTrue($package['output']['to_sink']);
        // real footprint from BuildingDetails::calc
        $this->assertIsArray($package['footprint']);
        $this->assertArrayHasKey('foundations', $package['footprint']);

        $this->assertNotNull($sink, 'expected a sink step');
        $this->assertSame('AWESOME Sink', $sink['building']);
        $this->assertSame(1, $sink['num_buildings']);
        $this->assertEqualsWithDelta(30.0, $sink['power'], 1e-9);
        // sink consumes the packaged form, outputs points
        $this->assertSame('Packaged Heavy Oil Residue', $sink['inputs'][0]['item']);
        $this->assertGreaterThan(0, $sink['output']['points']);
        $this->assertArrayHasKey('foundations', $sink['footprint']);
    }

    #[Test]
    public function no_auto_package_means_no_packaging_step(): void
    {
        // Plastic leftover is a fluid (Heavy Oil Residue); toggle off → not packaged,
        // not sinkable → no recycling steps at all.
        $steps = RecyclingSteps::build($this->recycling(false), 780);

        $this->assertEmpty(collect($steps)->where('type', 'package'));
    }

    #[Test]
    public function null_or_empty_recycling_yields_no_steps(): void
    {
        $this->assertSame([], RecyclingSteps::build(null));
        $this->assertSame([], RecyclingSteps::build(['points' => 0, 'recycled' => [], 'packaged' => [], 'waste' => []]));
    }

    #[Test]
    public function sinkable_solid_byproduct_feeds_the_sink_step(): void
    {
        // Alumina Solution plan emits unused Silica (sinkable solid) → sink input, no packaging.
        $calc = ProductionCalculator::make(product: 'Alumina Solution', qty: 120, recipe: 'Alumina Solution');
        $recycling = RecyclingCalc::calc($calc->getByproducts(), $calc->getByproductsUsed(), false);

        $steps = RecyclingSteps::build($recycling, 780);
        $sink = collect($steps)->firstWhere('type', 'sink');

        $this->assertNotNull($sink);
        $this->assertContains('Silica', collect($sink['inputs'])->pluck('item')->all());
    }

    #[Test]
    public function sink_step_count_scales_with_throughput(): void
    {
        // V106: the sink is sized by throughput — num_buildings = max(1, ceil(rate /
        // belt_speed)), power = count × seeded base_power, never hardcoded 1 × 30.
        $recycling = [
            'points' => 100000,
            'recycled' => ['Iron Plate' => ['qty' => 1600, 'points' => 100000]],
            'packaged' => [],
            'waste' => [],
        ];

        $steps = RecyclingSteps::build($recycling, 780);
        $sink = collect($steps)->firstWhere('type', 'sink');

        $this->assertNotNull($sink);
        $this->assertSame(3, $sink['num_buildings']); // ceil(1600 / 780)
        $this->assertEqualsWithDelta(90.0, $sink['power'], 1e-9); // 3 × 30 MW (seeded, V89)

        // V106: sink build cost from the seeded building's recipe, scaled by count
        $this->assertSame([
            'Reinforced Iron Plate' => 45,
            'Cable' => 90,
            'Concrete' => 135,
        ], $sink['build_cost']);

        // footprint reflects the count; per-row belt load stays within the belt
        $this->assertSame(3, $sink['footprint']['num_buildings']);
        $this->assertSame(3, $sink['footprint']['rows']);
        $this->assertLessThanOrEqual(100, $sink['footprint']['belt_utilization_in']);
    }

    #[Test]
    public function low_rate_sink_is_a_single_building_with_build_cost(): void
    {
        $recycling = [
            'points' => 100,
            'recycled' => ['Iron Plate' => ['qty' => 100, 'points' => 100]],
            'packaged' => [],
            'waste' => [],
        ];

        $steps = RecyclingSteps::build($recycling, 780);
        $sink = collect($steps)->firstWhere('type', 'sink');

        $this->assertSame(1, $sink['num_buildings']);
        $this->assertEqualsWithDelta(30.0, $sink['power'], 1e-9);
        $this->assertSame([
            'Reinforced Iron Plate' => 15,
            'Cable' => 30,
            'Concrete' => 45,
        ], $sink['build_cost']);
    }

    #[Test]
    public function packaging_step_matches_its_c100_overview_under_multipliers(): void
    {
        // V107: packagingStep threads the same cost/power/building multipliers (+
        // somersloops) into BuildingDetails::calc as overviews() passes to
        // BuildingOverview::make — the step row must equal its own c100 overview.
        request()->merge([
            'power_multiplier' => 2.0,
            'building_cost_multiplier' => 3.0,
        ]);

        $recycling = $this->recycling(true);
        $steps = RecyclingSteps::build($recycling, 780);
        $overviews = RecyclingSteps::overviews($recycling, 780);

        $package = collect($steps)->firstWhere('type', 'package');
        $entry = $overviews['Packaged Heavy Oil Residue|Packaged Heavy Oil Residue'];
        $c100 = $entry['overviews']['c100'];
        $detail = $c100['details'][$c100['selected_variant_name']];

        $this->assertEqualsWithDelta($detail['power_usage'], $package['power'], 0.001,
            'packaging step power must equal its c100 overview power under power_multiplier');
        $this->assertEqualsWithDelta($detail['num_buildings'], $package['num_buildings'], 0.001);
    }

    #[Test]
    public function package_step_overview_carries_four_clock_variants(): void
    {
        // V93: the package step gets a real 4-clock BuildingOverview set keyed
        // `product|recipe.description ?? product`, so the frontend renders controls.
        $overviews = RecyclingSteps::overviews($this->recycling(true), 780);

        $key = 'Packaged Heavy Oil Residue|Packaged Heavy Oil Residue';
        $this->assertArrayHasKey($key, $overviews);

        $entry = $overviews[$key];
        $this->assertSame('c100', $entry['clock']);

        foreach (['c100', 'c150', 'c200', 'c250'] as $clock) {
            $this->assertArrayHasKey($clock, $entry['overviews']);
            $this->assertNotNull($entry['overviews'][$clock]);
            $this->assertSame('Packager', $entry['overviews'][$clock]['building']);
        }
    }

    #[Test]
    public function no_package_overviews_without_auto_package(): void
    {
        $this->assertSame([], RecyclingSteps::overviews($this->recycling(false), 780));
        $this->assertSame([], RecyclingSteps::overviews(null));
    }

    #[Test]
    public function unseeded_sink_building_degrades_gracefully(): void
    {
        // B61: a prod DB without the v1.3.0 AWESOME Sink row 500'd —
        // Building::ofName returned a Builder (scope null fallback) and
        // ->variant('mk1') fataled with Builder::variant(). Missing building
        // must fall back to the hardcoded defaults instead.
        Building::where('name', 'AWESOME Sink')->delete();
        Cache::flush();

        $steps = RecyclingSteps::build($this->recycling(true), 780);
        $sink = collect($steps)->firstWhere('type', 'sink');

        $this->assertNotNull($sink, 'sink step must still build without the seeded row');
        $this->assertEqualsWithDelta(30.0, $sink['power'], 1e-9);
        $this->assertSame([], $sink['build_cost']);
        $this->assertArrayHasKey('foundations', $sink['footprint']);
    }

    #[Test]
    public function package_recipe_lookup_is_cached_by_fluid_name(): void
    {
        // T154: packageRecipe hits the DB once per fluid, then serves from the
        // model cache (same pattern as r()/i()/b(), TTL'd per T124).
        $this->assertFalse(Cache::has('package_recipe.Heavy Oil Residue'));

        $recycling = $this->recycling(true);

        $this->assertTrue(Cache::has('package_recipe.Heavy Oil Residue'));
        $this->assertNotEmpty($recycling['packaged']);
        $this->assertSame('Packaged Heavy Oil Residue', $recycling['packaged'][0]['product']);
    }

    #[Test]
    public function recipe_save_invalidates_the_package_recipe_cache(): void
    {
        // T154: observer-invalidated — saving a recipe flushes the package_recipe
        // key of every fluid it consumes (B17/T35 hook family).
        $this->recycling(true); // primes the cache
        $this->assertTrue(Cache::has('package_recipe.Heavy Oil Residue'));

        r('Packaged Heavy Oil Residue')->touch();

        $this->assertFalse(Cache::has('package_recipe.Heavy Oil Residue'));
    }
}
