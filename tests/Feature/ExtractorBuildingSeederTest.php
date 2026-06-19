<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Production\ExtractionCalc;
use App\Production\ExtractorSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T118 / V77: the 6 extractor buildings are seeded — one mk1 variant each, base_power
 * per §C, names exactly matching ExtractorSummary::buildingName. db:seed completes (V28).
 */
class ExtractorBuildingSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
    }

    public static function extractorProvider(): array
    {
        return [
            'Miner Mk.1' => ['Miner Mk.1', 5],
            'Miner Mk.2' => ['Miner Mk.2', 15],
            'Miner Mk.3' => ['Miner Mk.3', 45],
            'Water Extractor' => ['Water Extractor', 20],
            'Oil Extractor' => ['Oil Extractor', 40],
            'Resource Well Pressurizer' => ['Resource Well Pressurizer', 150],
        ];
    }

    #[Test]
    public function db_seed_completes_without_exception(): void
    {
        // setUp seeded already; reaching here means V28 held.
        $this->assertTrue(true);
    }

    #[Test]
    #[DataProvider('extractorProvider')]
    public function each_extractor_building_is_seeded_with_its_base_power(string $name, int $basePower): void
    {
        $building = Building::ofName($name);

        $this->assertNotNull($building, "$name should be seeded.");

        $variant = $building->variant('mk1');
        $this->assertNotNull($variant, "$name should have an mk1 variant.");
        $this->assertEqualsWithDelta($basePower, $variant->base_power, 1e-9);
    }

    #[Test]
    #[DataProvider('extractorProvider')]
    public function seeded_base_power_equals_extraction_calc_constant(string $name, int $basePower): void
    {
        // V77/V78: the seeded base_power is the single source shared with ExtractionCalc.
        // Verify via the calc: 1 extractor at 100% clock draws exactly base_power MW.
        $config = match ($name) {
            'Miner Mk.1' => ['miner' => 'mk1'],
            'Miner Mk.2' => ['miner' => 'mk2'],
            'Miner Mk.3' => ['miner' => 'mk3'],
            default => [],
        };
        $raw = match ($name) {
            'Water Extractor' => 'Water',
            'Oil Extractor' => 'Crude Oil',
            'Resource Well Pressurizer' => 'Nitrogen Gas',
            default => 'Iron Ore',
        };

        // demand = 1 item so exactly one extractor at 100% clock (no shards/purity)
        $calc = ExtractionCalc::calc($raw, 1, $config + ['purity' => 'normal', 'shards' => 0]);

        $this->assertSame(1, $calc['count']);
        $this->assertEqualsWithDelta($basePower, $calc['power'], 1e-6);
    }

    #[Test]
    public function building_names_match_extractor_summary_output(): void
    {
        // every name ExtractorSummary emits resolves to a seeded building (V77)
        $names = [
            ExtractorSummary::build(['Iron Ore' => 60], ['Iron Ore' => ['mode' => 'extract', 'miner' => 'mk1']])[0]['building'],
            ExtractorSummary::build(['Iron Ore' => 60], ['Iron Ore' => ['mode' => 'extract', 'miner' => 'mk2']])[0]['building'],
            ExtractorSummary::build(['Iron Ore' => 60], ['Iron Ore' => ['mode' => 'extract', 'miner' => 'mk3']])[0]['building'],
            ExtractorSummary::build(['Water' => 60], ['Water' => ['mode' => 'extract']])[0]['building'],
            ExtractorSummary::build(['Crude Oil' => 60], ['Crude Oil' => ['mode' => 'extract']])[0]['building'],
            ExtractorSummary::build(['Nitrogen Gas' => 60], ['Nitrogen Gas' => ['mode' => 'extract']])[0]['building'],
        ];

        foreach ($names as $name) {
            $this->assertNotNull(Building::ofName($name), "$name (ExtractorSummary output) should be a seeded building.");
        }
    }
}
