<?php

namespace Tests\Unit;

use App\Production\RecyclingCalc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T109 / V66 / V67: AWESOME Sink recycling calc.
 *
 * Given a plan's byproduct flows (produced vs used), compute the recycled
 * points/min: unused byproduct of sinkable solids is auto-recycled; unused
 * fluid/gas byproduct is recycled only when the auto-package toggle is on
 * (packaged → sunk, adding a Packager step + empty-container input); everything
 * else is surfaced as waste. Total points/min = Σ recycled flows × sink_points.
 */
class RecyclingCalcTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function unused_sinkable_byproduct_is_recycled_to_points(): void
    {
        // V66: leftover = produced − used; sinkable solid → points = leftover × sink_points.
        // Concrete sink_points = 12 → 30 × 12 = 360.
        $result = RecyclingCalc::calc(['Concrete' => 50], ['Concrete' => 20], false);

        $this->assertEqualsWithDelta(360.0, $result['points'], 0.001);
        $this->assertEqualsWithDelta(360.0, $result['recycled']['Concrete']['points'], 0.001);
        $this->assertEqualsWithDelta(30.0, $result['recycled']['Concrete']['qty'], 0.001);
        $this->assertEmpty($result['packaged']);
        $this->assertEmpty($result['waste']);
    }

    #[Test]
    public function non_sinkable_leftover_with_no_value_is_waste(): void
    {
        // V65/V66: an item with no sink value is not recycled → waste, 0 points.
        $result = RecyclingCalc::calc(['Motor' => 5], [], false);

        $this->assertSame(0.0, $result['points']);
        $this->assertEqualsWithDelta(5.0, $result['waste']['Motor'], 0.001);
        $this->assertEmpty($result['recycled']);
    }

    #[Test]
    public function fluid_leftover_is_waste_when_toggle_off(): void
    {
        // V67 OFF (default): unused fluid byproduct is surfaced as waste, never packaged.
        $result = RecyclingCalc::calc(['Water' => 60], [], false);

        $this->assertSame(0.0, $result['points']);
        $this->assertEqualsWithDelta(60.0, $result['waste']['Water'], 0.001);
        $this->assertEmpty($result['packaged']);
    }

    #[Test]
    public function fluid_leftover_is_packaged_and_recycled_when_toggle_on(): void
    {
        // V67 ON: leftover Water → Package recipe (Packager) → Packaged Water (sinkable)
        // → sunk. 60 Water → 1 Packager → 60 Packaged Water (130 pts) + 60 Empty Canister.
        $result = RecyclingCalc::calc(['Water' => 60], [], true);

        $this->assertCount(1, $result['packaged']);
        $row = $result['packaged'][0];

        $this->assertSame('Water', $row['fluid']);
        $this->assertSame('Packaged Water', $row['product']);
        $this->assertEqualsWithDelta(60.0, $row['qty'], 0.001);
        $this->assertEqualsWithDelta(1.0, $row['buildings'], 0.001);
        $this->assertEqualsWithDelta(10.0, $row['power'], 0.001); // Packager mk1 base_power
        $this->assertSame('Empty Canister', $row['container']);
        $this->assertEqualsWithDelta(60.0, $row['container_qty'], 0.001);
        $this->assertEqualsWithDelta(7800.0, $row['points'], 0.001); // 60 × 130

        $this->assertEqualsWithDelta(7800.0, $result['points'], 0.001);
        $this->assertEmpty($result['waste']);

        // V87: the packaged row carries the Packager build cost (mk1 recipe × buildings)
        // so it folds into the build summary / parts list. Packager mk1 = 20 Steel Beam,
        // 10 Rubber, 10 Plastic → ×1 building.
        $this->assertSame('Packager', $row['building']);
        $this->assertSame(20, $row['build_cost']['Steel Beam']);
        $this->assertSame(10, $row['build_cost']['Rubber']);
        $this->assertSame(10, $row['build_cost']['Plastic']);
    }

    #[Test]
    public function used_byproducts_use_the_nested_consumer_shape(): void
    {
        // B52: getByproductsUsed is nested (byproduct → {consumer → qty}); the calc
        // must sum the inner amounts, not treat the array as a scalar. Concrete
        // produced 50, consumed 20+10 across two steps → leftover 20 → 240 pts.
        $result = RecyclingCalc::calc(
            ['Concrete' => 50],
            ['Concrete' => ['Step A' => 20, 'Step B' => 10]],
            false
        );

        $this->assertEqualsWithDelta(20.0, $result['recycled']['Concrete']['qty'], 0.001);
        $this->assertEqualsWithDelta(240.0, $result['points'], 0.001);
    }

    #[Test]
    public function fully_consumed_byproduct_has_no_leftover(): void
    {
        // B52: when a byproduct is fully consumed by its consumers, leftover is 0
        // (not produced − 1 from a bad scalar cast).
        $result = RecyclingCalc::calc(
            ['Silica' => 25],
            ['Silica' => ['Aluminum Ingot' => 25]],
            false
        );

        $this->assertSame(0.0, $result['points']);
        $this->assertArrayNotHasKey('Silica', $result['recycled']);
        $this->assertArrayNotHasKey('Silica', $result['waste']);
    }

    #[Test]
    public function total_points_aggregate_across_flows(): void
    {
        // V66: total = Σ recycled flows. Concrete (360) + packaged Water (7800) = 8160.
        $result = RecyclingCalc::calc(['Concrete' => 30, 'Water' => 60], [], true);

        $this->assertEqualsWithDelta(8160.0, $result['points'], 0.001);
    }
}
