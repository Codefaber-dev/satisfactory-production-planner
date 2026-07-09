<?php

namespace Tests\Unit;

use App\Production\Multiplexer;
use App\Production\ProductionCalculator;
use App\Production\RecyclingCalc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T109 / V66 / V67: AWESOME Sink recycling calc.
 *
 * calc() is the pure single-pass classifier: unused byproduct of sinkable
 * solids is auto-recycled; unused fluid/gas byproduct is sized for packaging
 * only when the auto-package toggle is on; everything else is waste.
 *
 * V137 (T184): the packaging production itself is exercised through inject()
 * — one real plan calc per packaged form added to the plan's Multiplexer, with
 * container chain, byproduct feedback, and leftover netting all handled by the
 * plan's own byproduct convergence.
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
    public function fluid_leftover_is_sized_for_packaging_when_toggle_on(): void
    {
        // V67 ON: leftover Water → Package recipe (Packager) → Packaged Water
        // (sinkable). 60 Water → 1 Packager → 60 Packaged Water (130 pts each).
        // calc() is the single-pass classifier — no chain recursion here (V137:
        // that happens in the plan via inject()).
        $result = RecyclingCalc::calc(['Water' => 60], [], true);

        $this->assertCount(1, $result['packaged']);
        $row = $result['packaged'][0];

        $this->assertSame('Water', $row['fluid']);
        // V94: the fluid input rate (the leftover) is carried explicitly, distinct
        // from the packaged output qty (B53)
        $this->assertEqualsWithDelta(60.0, $row['fluid_qty'], 0.001);
        $this->assertSame('Packaged Water', $row['product']);
        $this->assertEqualsWithDelta(60.0, $row['qty'], 0.001);
        $this->assertEqualsWithDelta(1.0, $row['buildings'], 0.001);
        $this->assertEqualsWithDelta(10.0, $row['power'], 0.001); // Packager mk1 base_power
        $this->assertEqualsWithDelta(7800.0, $row['points'], 0.001); // 60 × 130

        $this->assertEqualsWithDelta(7800.0, $result['points'], 0.01);
        $this->assertEmpty($result['waste']);
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
        // V66: total = Σ recycled flows. Concrete (360) + Packaged Water (7800).
        $result = RecyclingCalc::calc(['Concrete' => 30, 'Water' => 60], [], true);

        $this->assertEqualsWithDelta(8160.0, $result['points'], 0.01);
    }

    #[Test]
    public function inject_recurses_the_container_chain_with_byproduct_feedback(): void
    {
        // B69/V136/V137: Plastic 60 leaves 30 HOR. Packaging demands canisters,
        // producing canisters demands Plastic, whose HOR byproduct is packaged
        // too: packaged HOR = 30/(1−¼) = 40, chain Plastic 20, chain Crude 30.
        $multiplexer = new Multiplexer;
        $multiplexer->add(ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic'));

        $recycling = RecyclingCalc::inject($multiplexer, true);

        $hor = collect($recycling['packaged'])->firstWhere('product', 'Packaged Heavy Oil Residue');
        $this->assertEqualsWithDelta(40.0, $hor['qty'], 0.01);
        $this->assertEqualsWithDelta(7200.0, $recycling['points'], 2.0); // 40 × 180

        // the chain is real plan production, merged into the results (V137)
        $all = $multiplexer->getAllMaterials();
        $this->assertEqualsWithDelta(80.0, $all['Plastic'], 0.01); // 60 plan + 20 chain
        $this->assertEqualsWithDelta(40.0, $all['Empty Canister'], 0.05); // 1:1 with packaged HOR
        $this->assertEqualsWithDelta(120.0, $multiplexer->getRawMaterials()['Crude Oil'], 0.01);
    }

    #[Test]
    public function inject_nets_leftover_container_byproduct_via_convergence(): void
    {
        // V135/V137: Unpackage Alumina Solution leaves 60 Empty Canister
        // byproduct (its Packaged input is imported, B55/V97); the Plastic
        // plan's packaged HOR needs 30 — fed from the leftover via byproduct
        // convergence (no chain production), remainder 30 recycled.
        $multiplexer = new Multiplexer;
        $multiplexer->add(ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic'));
        $multiplexer->add(ProductionCalculator::make(product: i('Alumina Solution'), qty: 60, recipe: 'Unpackage Alumina Solution'));
        $multiplexer->recalculateUsingByproductsUntilStable();

        $recycling = RecyclingCalc::inject($multiplexer, true);

        // no shortfall → no chain Plastic/Crude beyond the plan's own
        $this->assertEqualsWithDelta(60.0, $multiplexer->getAllMaterials()['Plastic'], 0.01);

        $hor = collect($recycling['packaged'])->firstWhere('product', 'Packaged Heavy Oil Residue');
        $this->assertEqualsWithDelta(30.0, $hor['qty'], 0.01);

        // canisters: 60 leftover − 30 fed to the Packager → 30 sunk
        $this->assertEqualsWithDelta(30.0, $recycling['recycled']['Empty Canister']['qty'], 0.05);

        // points = 30 sunk canisters (×60) + 30 packaged HOR (×180)
        $this->assertEqualsWithDelta(
            30 * (int) i('Empty Canister')->sink_points + 5400.0,
            $recycling['points'],
            0.5
        );
    }

    #[Test]
    public function inject_without_auto_package_leaves_the_plan_untouched(): void
    {
        // V137 canary: toggle OFF ⇒ no injected calcs, no reconvergence, fluid
        // leftover surfaced as waste — plan materials identical to a plain calc.
        $baseline = ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic');

        $multiplexer = new Multiplexer;
        $multiplexer->add(ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic'));

        $recycling = RecyclingCalc::inject($multiplexer, false);

        $this->assertEmpty($recycling['packaged']);
        $this->assertEqualsWithDelta(30.0, $recycling['waste']['Heavy Oil Residue'], 0.001);
        $this->assertEquals(
            $baseline->getRawMaterials()->all(),
            $multiplexer->getRawMaterials()->all()
        );
    }

    #[Test]
    public function payload_folds_recycling_production_into_plan_materials(): void
    {
        // V137: chain materials arrive via the merged results — no separate
        // recycling materials keys. Plastic 60: raw Crude 120, Plastic total 80.
        request()->merge(['auto_package_recycle' => true]);

        $payload = ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic')->toPayload();

        $this->assertEqualsWithDelta(120.0, $payload['raw_materials']['Crude Oil'], 0.01);
        $this->assertEqualsWithDelta(80.0, $payload['all_materials']['Plastic'], 0.01);

        // V137: the container/materials recycling keys are gone
        $this->assertArrayNotHasKey('containers', $payload['recycling']);
        $this->assertArrayNotHasKey('raw_materials', $payload['recycling']);
        $this->assertArrayNotHasKey('intermediate_materials', $payload['recycling']);
        $this->assertArrayNotHasKey('all_materials', $payload['recycling']);
    }

    #[Test]
    public function toggle_off_payload_has_no_injected_production(): void
    {
        // V137 canary: auto_package_recycle OFF ⇒ no injected steps, no material drift.
        $payload = ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic')->toPayload();

        $this->assertSame([], $payload['recycling']['packaged']);
        $this->assertEqualsWithDelta(90.0, $payload['raw_materials']['Crude Oil'], 0.01);

        $products = collect($payload['results'])->flatMap(fn ($tier) => array_keys($tier));
        $this->assertNotContains('Packaged Heavy Oil Residue', $products);
        $this->assertNotContains('Empty Canister', $products);
    }
}
