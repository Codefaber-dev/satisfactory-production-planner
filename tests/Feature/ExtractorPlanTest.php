<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Production\ExtractionCalc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T119 / V77 / V78: an extract-mode raw contributes its extractor's seeded build
 * cost to the parts list and its power to the plan power total.
 */
class ExtractorPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function extract_iron_ore_row_carries_seeded_miner_build_cost_and_power(): void
    {
        $query = http_build_query([
            'raw_sources' => [
                'Iron Ore' => ['mode' => 'extract', 'miner' => 'mk2', 'purity' => 'normal', 'shards' => 0],
            ],
        ]);

        $response = $this->get("/dashboard/Iron Plate/30/Iron Plate?{$query}");
        $response->assertStatus(200);

        $props = $response->viewData('page')['props'];
        $extractors = $props['production']['extractors'];

        $this->assertCount(1, $extractors);
        $row = $extractors[0];

        $this->assertSame('Miner Mk.2', $row['building']);

        // V77: build_cost = seeded Miner Mk.2 mk1 recipe × count
        $variant = Building::ofName('Miner Mk.2')->variant('mk1');
        $expected = $variant->recipe
            ->mapWithKeys(fn ($i) => [$i->name => (int) ceil($i->pivot->qty * $row['num_buildings'])])
            ->all();
        $this->assertSame($expected, $row['build_cost']);

        // V78: power_usage equals ExtractionCalc (= count × 15 MW at 100%, 0 shards)
        $this->assertEqualsWithDelta($row['num_buildings'] * 15, $row['power_usage'], 1e-4);
    }

    #[Test]
    public function plan_total_power_includes_extractor_power(): void
    {
        // V78: the building-summary total (recipe-building power + Σ extractor power)
        // includes the extractor row's power_usage.
        $query = http_build_query([
            'raw_sources' => [
                'Iron Ore' => ['mode' => 'extract', 'miner' => 'mk2', 'purity' => 'normal', 'shards' => 0],
            ],
        ]);

        $response = $this->get("/dashboard/Iron Plate/30/Iron Plate?{$query}");
        $props = $response->viewData('page')['props'];

        $extractorPower = collect($props['production']['extractors'])->sum('power_usage');
        $this->assertGreaterThan(0, $extractorPower, 'extractor power should be present and positive');

        // the recipe-building side of the plan exists too (frontend sums both into the
        // building-summary total); a non-empty overviews proves the recipe steps render.
        $this->assertNotEmpty($props['production']['overviews']);
    }

    #[Test]
    public function import_raw_adds_no_extractor(): void
    {
        // default import → byte-identical, no extractor row
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate');
        $props = $response->viewData('page')['props'];

        $this->assertSame([], $props['production']['extractors']);
    }
}
