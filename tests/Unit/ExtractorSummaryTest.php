<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Production\ExtractionCalc;
use App\Production\ExtractorSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// DB-backed (V77): build_cost is read from the seeded extractor building.
class ExtractorSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
    }

    public function test_extract_raw_becomes_an_extractor_building_row(): void
    {
        // V76: 120/min Iron Ore, Mk.2 miner, normal, 0 shards = 1 miner @ 15 MW.
        $rows = ExtractorSummary::build(
            ['Iron Ore' => 120],
            ['Iron Ore' => ['mode' => 'extract', 'miner' => 'mk2', 'purity' => 'normal', 'shards' => 0]]
        );

        $this->assertCount(1, $rows);
        $row = $rows[0];

        $this->assertSame('Iron Ore', $row['product']);
        $this->assertSame('Miner Mk.2', $row['building']);
        $this->assertSame('Miner Mk.2', $row['variant_name']);
        $this->assertSame(1, $row['num_buildings']);

        // power matches ExtractionCalc exactly
        $expected = ExtractionCalc::calc('Iron Ore', 120, ['miner' => 'mk2', 'purity' => 'normal', 'shards' => 0]);
        $this->assertEqualsWithDelta($expected['power'], $row['power_usage'], 1e-4);
        $this->assertEqualsWithDelta(15, $row['power_usage'], 1e-4);
    }

    public function test_node_count_and_power_scale_with_demand_and_shards(): void
    {
        // 600/min pure +3 shards: rate = 120×2×2.5 = 600 → 1 miner; power = 15×2.5^1.321928
        $rows = ExtractorSummary::build(
            ['Iron Ore' => 600],
            ['Iron Ore' => ['mode' => 'extract', 'miner' => 'mk2', 'purity' => 'pure', 'shards' => 3]]
        );

        $expected = ExtractionCalc::calc('Iron Ore', 600, ['miner' => 'mk2', 'purity' => 'pure', 'shards' => 3]);
        $this->assertSame($expected['count'], $rows[0]['num_buildings']);
        $this->assertEqualsWithDelta($expected['power'], $rows[0]['power_usage'], 1e-4);
        // power shards consumed = count × shards
        $this->assertSame($expected['count'] * 3, $rows[0]['footprint']['power_shards']);
    }

    public function test_extractor_names_dispatch_by_type(): void
    {
        $name = fn ($raw, $config) => ExtractorSummary::build([$raw => 120], [$raw => $config])[0]['building'];

        $this->assertSame('Miner Mk.1', $name('Iron Ore', ['mode' => 'extract', 'miner' => 'mk1']));
        $this->assertSame('Miner Mk.3', $name('Coal', ['mode' => 'extract', 'miner' => 'mk3']));
        $this->assertSame('Water Extractor', $name('Water', ['mode' => 'extract']));
        $this->assertSame('Oil Extractor', $name('Crude Oil', ['mode' => 'extract']));
        $this->assertSame('Resource Well Pressurizer', $name('Nitrogen Gas', ['mode' => 'extract']));
    }

    public function test_import_and_convert_raws_produce_no_extractor(): void
    {
        $rows = ExtractorSummary::build(
            ['Iron Ore' => 120, 'Coal' => 60, 'Limestone' => 90],
            [
                'Iron Ore' => ['mode' => 'import'],
                'Coal' => ['mode' => 'convert', 'recipe' => 'Coal (Iron)'],
                // Limestone has no config → defaults to import
            ]
        );

        $this->assertSame([], $rows);
    }

    public function test_non_extractable_raw_never_yields_an_extractor(): void
    {
        // even if (wrongly) set to extract, a biomass raw produces no extractor (V74)
        $rows = ExtractorSummary::build(
            ['Wood' => 100],
            ['Wood' => ['mode' => 'extract']]
        );

        $this->assertSame([], $rows);
    }

    public function test_extractor_build_cost_comes_from_the_seeded_building(): void
    {
        // V77: build_cost = seeded Miner Mk.2 mk1-variant recipe × count.
        $rows = ExtractorSummary::build(
            ['Iron Ore' => 360], // 360/min @ 120/extractor = 3 miners
            ['Iron Ore' => ['mode' => 'extract', 'miner' => 'mk2', 'purity' => 'normal', 'shards' => 0]]
        );

        $this->assertSame(3, $rows[0]['num_buildings']);

        $variant = Building::ofName('Miner Mk.2')->variant('mk1');
        $expected = $variant->recipe
            ->mapWithKeys(fn ($i) => [$i->name => (int) ceil($i->pivot->qty * 3)])
            ->all();

        $this->assertSame($expected, $rows[0]['build_cost']);
        $this->assertNotEmpty($rows[0]['build_cost']);
    }
}
