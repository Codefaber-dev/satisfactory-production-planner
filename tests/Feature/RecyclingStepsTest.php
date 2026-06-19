<?php

namespace Tests\Feature;

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
        $this->assertSame('Heavy Oil Residue', $package['inputs'][0]['item']);
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
}
