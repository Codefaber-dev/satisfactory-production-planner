<?php

namespace Tests\Feature;

use App\Production\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T184 / V137: recycling production lives in the plan proper. The auto-package
 * Packager and its container chain are injected as real plan calcs — they
 * render as normal steps inside `results` (recipe selectors, overviews, links),
 * overlap merges with plan production, chain raws surface as Level-0 raws, and
 * the Recycling group carries ONLY the terminal AWESOME Sink step.
 *
 * Numbers (Plastic 60, base recipe): 90 Crude → 60 Plastic + 30 HOR leftover.
 * Packaging HOR needs canisters, canisters need Plastic, whose HOR byproduct is
 * packaged too: packaged HOR = 30/(1−¼) = 40, chain Plastic 20, chain Crude 30.
 */
class RecyclingInjectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    private function payload(): array
    {
        return ProductionCalculator::make(product: 'Plastic', qty: 60, recipe: 'Plastic')->toPayload();
    }

    private function resultsProduction(array $payload, string $product): ?array
    {
        foreach ($payload['results'] as $tier) {
            if (isset($tier[$product])) {
                return $tier[$product];
            }
        }

        return null;
    }

    #[Test]
    public function injected_packaging_and_chain_render_as_plan_steps(): void
    {
        request()->merge(['auto_package_recycle' => true]);
        $payload = $this->payload();

        // packaging step is a normal results step with a real recipe (picker renders)
        $packaged = $this->resultsProduction($payload, 'Packaged Heavy Oil Residue');
        $this->assertNotNull($packaged, 'Packaged Heavy Oil Residue must be a results step');
        $row = collect($packaged['production'])->first(fn ($p) => ($p['qty'] ?? 0) > 0);
        $this->assertNotNull($row['recipe']);
        $this->assertEqualsWithDelta(40.0, $packaged['total'], 0.01);
        // its output feeds the sink, not 'final' (V137 — not a requested output)
        $this->assertArrayHasKey('AWESOME Sink', $packaged['outputs']);
        $this->assertArrayNotHasKey('final', $packaged['outputs']);

        // container chain renders as a results step too
        $canister = $this->resultsProduction($payload, 'Empty Canister');
        $this->assertNotNull($canister, 'Empty Canister must be a results step');
        $this->assertEqualsWithDelta(40.0, $canister['total'], 0.05);

        // overlap merged: ONE Plastic step, plan 60 + chain 20 (V137)
        $plastic = $this->resultsProduction($payload, 'Plastic');
        $this->assertEqualsWithDelta(80.0, $plastic['total'], 0.01);
        $this->assertCount(1, collect($plastic['production'])->filter(fn ($p) => ($p['qty'] ?? 0) > 0));

        // chain raws fold into the plan raws (Level 0 raw step, raw_sources apply)
        $this->assertEqualsWithDelta(120.0, $payload['raw_materials']['Crude Oil'], 0.01);
        $crude = $this->resultsProduction($payload, 'Crude Oil');
        $this->assertTrue((bool) $crude['raw']);

        // injected steps carry standard 4-clock overviews (controls render)
        $this->assertArrayHasKey('Packaged Heavy Oil Residue|Packaged Heavy Oil Residue', $payload['overviews']);
        $canisterKey = collect(array_keys($payload['overviews']))->first(fn ($k) => str_starts_with($k, 'Empty Canister|'));
        $this->assertNotNull($canisterKey, 'Empty Canister overview key expected');

        // injected calcs are NOT requested outputs: final stays the single Plastic tree
        $this->assertSame('Plastic', $payload['final']['name']);
    }

    #[Test]
    public function zero_qty_destinations_are_filtered_from_outputs(): void
    {
        // V138/B70: the injected Packaged HOR calc's HOR child ends at qty 0
        // (fully byproduct-covered) — its suppliers must NOT list it as a
        // ghost 0/min destination (Crude Oil showed "Heavy Oil Residue 0 per
        // min (1.undefined)").
        request()->merge(['auto_package_recycle' => true]);
        $payload = $this->payload();

        $crude = $this->resultsProduction($payload, 'Crude Oil');
        $this->assertArrayNotHasKey('Heavy Oil Residue', $crude['outputs']);

        // general form: every outputs entry is a positive flow
        foreach ($payload['results'] as $tier) {
            foreach ($tier as $name => $material) {
                foreach ($material['outputs'] as $dest => $qty) {
                    $this->assertGreaterThan(0, $qty, "$name → $dest must be a positive flow");
                }
            }
        }
    }

    #[Test]
    public function recycling_group_is_sink_only(): void
    {
        request()->merge(['auto_package_recycle' => true]);
        $payload = $this->payload();

        $this->assertCount(1, $payload['recycling_steps']);
        $sink = $payload['recycling_steps'][0];

        $this->assertSame('sink', $sink['type']);
        // sink inputs reference plan steps (the packaged form is a results step)
        $this->assertSame('Packaged Heavy Oil Residue', $sink['inputs'][0]['item']);
        $this->assertEqualsWithDelta(40.0, $sink['inputs'][0]['qty'], 0.01);
        $this->assertEqualsWithDelta(7200.0, $sink['output']['points'], 2.0); // 40 × 180
        $this->assertEqualsWithDelta(7200.0, $payload['recycling']['points'], 2.0);
    }

    #[Test]
    public function chain_honors_request_recipe_choices(): void
    {
        // V137: injected calcs are real plan calcs — request `choices` apply.
        // Steel Canister (Empty Canister ← Steel Ingot) removes the Plastic
        // feedback loop: packaged HOR stays 30, no chain Plastic/Crude.
        request()->merge([
            'auto_package_recycle' => true,
            'choices' => ['Empty Canister' => 'Steel Canister'],
        ]);

        $payload = $this->payload();

        $canister = $this->resultsProduction($payload, 'Empty Canister');
        $row = collect($canister['production'])->first(fn ($p) => ($p['qty'] ?? 0) > 0);
        $this->assertSame('Steel Canister', $row['recipe']['description']);
        $this->assertEqualsWithDelta(30.0, $canister['total'], 0.01);

        // no HOR feedback → plan Plastic only, packaged HOR 30
        $plastic = $this->resultsProduction($payload, 'Plastic');
        $this->assertEqualsWithDelta(60.0, $plastic['total'], 0.01);
        $this->assertEqualsWithDelta(30.0, $this->resultsProduction($payload, 'Packaged Heavy Oil Residue')['total'], 0.01);
    }

    #[Test]
    public function multi_plan_payload_matches_the_single_path(): void
    {
        // V137 multi parity: the multi endpoint injects through the same
        // Multiplexer mechanism.
        $response = $this->get('/dashboard/multi?product[]=Plastic&yield[]=60&recipe[]=Plastic&variant=mk1&auto_package_recycle=1');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('production.recycling_steps', fn ($steps) => count($steps) === 1 && $steps[0]['type'] === 'sink')
            ->where('production.raw_materials.Crude Oil', fn ($qty) => abs($qty - 120.0) < 0.01)
            ->where('production.results', function ($results) {
                $products = collect($results)->flatMap(fn ($tier) => array_keys($tier));

                return $products->contains('Packaged Heavy Oil Residue')
                    && $products->contains('Empty Canister');
            })
            ->etc()
        );
    }

    #[Test]
    public function toggle_off_multi_plan_has_no_injected_steps(): void
    {
        $response = $this->get('/dashboard/multi?product[]=Plastic&yield[]=60&recipe[]=Plastic&variant=mk1');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('production.raw_materials.Crude Oil', fn ($qty) => abs($qty - 90.0) < 0.01)
            ->where('production.results', function ($results) {
                $products = collect($results)->flatMap(fn ($tier) => array_keys($tier));

                return ! $products->contains('Packaged Heavy Oil Residue')
                    && ! $products->contains('Empty Canister');
            })
            ->etc()
        );
    }
}
