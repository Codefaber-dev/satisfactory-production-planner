<?php

namespace Tests\Unit;

use App\Production\ProductionCalculator;
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
        // V136: the container chain's HOR byproduct is packaged too (second row).
        $result = RecyclingCalc::calc(['Water' => 60], [], true);

        $this->assertCount(2, $result['packaged']);
        $row = collect($result['packaged'])->firstWhere('product', 'Packaged Water');

        $this->assertSame('Water', $row['fluid']);
        // V94: the fluid input rate (the leftover) is carried explicitly, distinct from
        // the packaged output qty, so the packaging step never reuses one for the other (B53)
        $this->assertEqualsWithDelta(60.0, $row['fluid_qty'], 0.001);
        $this->assertSame('Packaged Water', $row['product']);
        $this->assertEqualsWithDelta(60.0, $row['qty'], 0.001);
        $this->assertEqualsWithDelta(1.0, $row['buildings'], 0.001);
        $this->assertEqualsWithDelta(10.0, $row['power'], 0.001); // Packager mk1 base_power
        $this->assertSame('Empty Canister', $row['container']);
        $this->assertEqualsWithDelta(60.0, $row['container_qty'], 0.001);
        $this->assertEqualsWithDelta(7800.0, $row['points'], 0.001); // 60 × 130

        // V136: total includes the chain's packaged HOR — 60×130 + 20×180 = 11400
        $this->assertEqualsWithDelta(11400.0, $result['points'], 0.01);
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
        // V66: total = Σ recycled flows. Concrete (360) + packaged Water incl.
        // chain HOR (11400, V136) = 11760.
        $result = RecyclingCalc::calc(['Concrete' => 30, 'Water' => 60], [], true);

        $this->assertEqualsWithDelta(11760.0, $result['points'], 0.01);
    }

    #[Test]
    public function packaging_container_demand_spawns_the_full_production_chain(): void
    {
        // B69/V136: container demand recurses the full chain with byproduct
        // feedback. Water 60 → 60 canisters; Plastic (C/2) emits HOR (C/4) which
        // packages into C/4 more canister demand: C = 60/(1−¼) = 80. Plastic 40,
        // Crude Oil 60, packaged HOR 20.
        $result = RecyclingCalc::calc(['Water' => 60], [], true);

        $rows = collect($result['containers'])->keyBy('product');

        $canister = $rows['Empty Canister'];
        $this->assertEqualsWithDelta(80.0, $canister['qty'], 0.01);
        $this->assertSame('Constructor', $canister['building']);
        $this->assertEqualsWithDelta(80 / 60, $canister['buildings'], 0.001);
        $this->assertNotEmpty($canister['build_cost']);

        $plasticInput = collect($canister['inputs'])->firstWhere('item', 'Plastic');
        $this->assertEqualsWithDelta(40.0, $plasticInput['qty'], 0.01);

        // the chain recurses: Plastic is produced too, from Crude Oil
        $plastic = $rows['Plastic'];
        $this->assertEqualsWithDelta(40.0, $plastic['qty'], 0.01);
        $crudeInput = collect($plastic['inputs'])->firstWhere('item', 'Crude Oil');
        $this->assertEqualsWithDelta(60.0, $crudeInput['qty'], 0.01);

        // chain materials surface for the plan-materials merge (V136)
        $this->assertEqualsWithDelta(60.0, $result['raw_materials']['Crude Oil'], 0.01);
        $this->assertEqualsWithDelta(40.0, $result['all_materials']['Plastic'], 0.01);
        $this->assertEqualsWithDelta(40.0, $result['intermediate_materials']['Plastic'], 0.01);

        // chain HOR byproduct is packaged and sunk
        $hor = collect($result['packaged'])->firstWhere('product', 'Packaged Heavy Oil Residue');
        $this->assertEqualsWithDelta(20.0, $hor['qty'], 0.01);

        // conservation: canisters produced = Σ packaged container demand
        $demand = collect($result['packaged'])->sum('container_qty');
        $this->assertEqualsWithDelta($demand, $canister['qty'], 0.01);
    }

    #[Test]
    public function leftover_container_byproduct_offsets_packaging_demand_before_sinking(): void
    {
        // V135: leftover Empty Canister byproduct feeds the Packager before the
        // sink — 100 leftover − 60 demand → 40 sunk, no production row.
        $result = RecyclingCalc::calc(['Water' => 60, 'Empty Canister' => 100], [], true);

        $this->assertEqualsWithDelta(40.0, $result['recycled']['Empty Canister']['qty'], 0.001);
        $this->assertEmpty($result['containers']);

        // points reflect only the 40 actually sunk
        $canisterPoints = 40 * (int) i('Empty Canister')->sink_points;
        $packagedPoints = $result['packaged'][0]['points'];
        $this->assertEqualsWithDelta($canisterPoints + $packagedPoints, $result['points'], 0.001);
    }

    #[Test]
    public function container_shortfall_after_offset_is_produced(): void
    {
        // V135/V136: 20 leftover feed packaging first (none sunk); the produced
        // shortfall grows with the chain's own HOR feedback:
        // S = (60 − 20) / (1 − ¼) = 53.3̅.
        $result = RecyclingCalc::calc(['Water' => 60, 'Empty Canister' => 20], [], true);

        $this->assertArrayNotHasKey('Empty Canister', $result['recycled']);

        $canister = collect($result['containers'])->firstWhere('product', 'Empty Canister');
        $this->assertEqualsWithDelta(160 / 3, $canister['qty'], 0.01);
    }

    #[Test]
    public function payload_merges_the_container_chain_into_plan_materials(): void
    {
        // V136: chain raws/intermediates fold into the plan payload materials.
        // Plastic 60 plan: 90 Crude, 30 HOR leftover → chain C = 30/(1−¼) = 40,
        // chain Plastic 20, chain Crude 30 → raw total 120, Plastic total 80.
        request()->merge(['auto_package_recycle' => true]);

        $payload = ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic')->toPayload();

        $this->assertEqualsWithDelta(120.0, $payload['raw_materials']['Crude Oil'], 0.01);
        $this->assertEqualsWithDelta(80.0, $payload['all_materials']['Plastic'], 0.01);

        $canister = collect($payload['recycling']['containers'])->firstWhere('product', 'Empty Canister');
        $this->assertEqualsWithDelta(40.0, $canister['qty'], 0.01);
    }

    #[Test]
    public function toggle_off_payload_has_no_chain_and_unchanged_materials(): void
    {
        // V136 canary: auto_package_recycle OFF ⇒ no containers, no material drift.
        $payload = ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic')->toPayload();

        $this->assertSame([], $payload['recycling']['containers']);
        $this->assertEqualsWithDelta(90.0, $payload['raw_materials']['Crude Oil'], 0.01);
    }
}
