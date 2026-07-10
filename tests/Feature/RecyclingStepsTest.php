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
 * T127 / V88 / V137: the Recycling group carries ONLY the terminal AWESOME Sink
 * step descriptor — packaging + container-chain production render as normal
 * plan steps (injected calcs, V137), so RecyclingSteps::build no longer emits
 * produce/package descriptors.
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
    public function builds_only_the_terminal_sink_step(): void
    {
        // V137: sink-only — the packaged form feeds the sink as an input; no
        // produce/package descriptors (those are plan steps now).
        $steps = RecyclingSteps::build($this->recycling(true), 780);

        $this->assertCount(1, $steps);
        $sink = $steps[0];

        $this->assertSame('sink', $sink['type']);
        $this->assertSame('AWESOME Sink', $sink['building']);
        $this->assertSame(1, $sink['num_buildings']);
        $this->assertEqualsWithDelta(30.0, $sink['power'], 1e-9);
        // sink consumes the packaged form, outputs points
        $this->assertSame('Packaged Heavy Oil Residue', $sink['inputs'][0]['item']);
        $this->assertGreaterThan(0, $sink['output']['points']);
        $this->assertArrayHasKey('foundations', $sink['footprint']);
    }

    #[Test]
    public function no_auto_package_means_no_steps_for_a_fluid_only_leftover(): void
    {
        // Plastic leftover is a fluid (Heavy Oil Residue); toggle off → not packaged,
        // not sinkable → no recycling steps at all.
        $this->assertSame([], RecyclingSteps::build($this->recycling(false), 780));
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
