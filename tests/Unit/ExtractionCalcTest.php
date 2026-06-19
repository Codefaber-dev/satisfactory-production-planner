<?php

namespace Tests\Unit;

use App\Production\ExtractionCalc;
use PHPUnit\Framework\TestCase;

class ExtractionCalcTest extends TestCase
{
    public function test_miner_mk2_normal_no_shards_is_120_per_min(): void
    {
        $r = ExtractionCalc::calc('Iron Ore', 120, ['purity' => 'normal', 'miner' => 'mk2', 'shards' => 0]);

        $this->assertSame('miner', $r['type']);
        $this->assertEqualsWithDelta(120, $r['rate_per'], 1e-9);
        $this->assertSame(1, $r['count']);
    }

    public function test_miner_pure_three_shards_is_doubled_and_2_5x(): void
    {
        // 120 × 2 (pure) × 2.5 (250%) = 600
        $r = ExtractionCalc::calc('Iron Ore', 600, ['purity' => 'pure', 'miner' => 'mk2', 'shards' => 3]);

        $this->assertEqualsWithDelta(600, $r['rate_per'], 1e-9);
        $this->assertSame(1, $r['count']);
    }

    public function test_miner_tier_rates(): void
    {
        $this->assertEqualsWithDelta(60, ExtractionCalc::calc('Coal', 10, ['miner' => 'mk1'])['rate_per'], 1e-9);
        $this->assertEqualsWithDelta(120, ExtractionCalc::calc('Coal', 10, ['miner' => 'mk2'])['rate_per'], 1e-9);
        $this->assertEqualsWithDelta(240, ExtractionCalc::calc('Coal', 10, ['miner' => 'mk3'])['rate_per'], 1e-9);
    }

    public function test_node_count_is_ceil_of_demand_over_rate(): void
    {
        // demand 250 / 120 per node → ceil = 3
        $this->assertSame(3, ExtractionCalc::calc('Iron Ore', 250, ['miner' => 'mk2'])['count']);
    }

    public function test_water_ignores_purity_and_tier(): void
    {
        // purity + miner params ignored; base 120
        $r = ExtractionCalc::calc('Water', 120, ['purity' => 'pure', 'miner' => 'mk3', 'shards' => 0]);

        $this->assertSame('water', $r['type']);
        $this->assertEqualsWithDelta(120, $r['rate_per'], 1e-9);
        $this->assertSame(1, $r['count']);
    }

    public function test_oil_applies_purity_but_no_tier(): void
    {
        // 120 × 0.5 (impure) = 60; miner tier ignored
        $r = ExtractionCalc::calc('Crude Oil', 60, ['purity' => 'impure', 'miner' => 'mk3']);

        $this->assertSame('oil', $r['type']);
        $this->assertEqualsWithDelta(60, $r['rate_per'], 1e-9);
    }

    public function test_well_nitrogen_is_aggregate_with_purity(): void
    {
        $r = ExtractionCalc::calc('Nitrogen Gas', 240, ['purity' => 'pure']);

        $this->assertSame('well', $r['type']);
        $this->assertEqualsWithDelta(240, $r['rate_per'], 1e-9); // 120 × 2
    }

    public function test_power_uses_exponent_and_extractor_power(): void
    {
        // mk2 = 15 MW, 1 node, 250% clock → 15 × 2.5^1.321928
        $r = ExtractionCalc::calc('Iron Ore', 1, ['miner' => 'mk2', 'shards' => 3]);

        $this->assertEqualsWithDelta(15 * pow(2.5, 1.321928), $r['power'], 1e-6);
    }

    public function test_power_scales_with_node_count(): void
    {
        // 3 nodes @ 100% clock (exponent factor 1) × 15 MW
        $r = ExtractionCalc::calc('Iron Ore', 250, ['miner' => 'mk2']);

        $this->assertSame(3, $r['count']);
        $this->assertEqualsWithDelta(3 * 15 * pow(1.0, 1.321928), $r['power'], 1e-6);
    }

    public function test_defaults_match_import_baseline(): void
    {
        // no params → mk2 normal 0 shards = 120/min @ 15 MW (matches raw_materials proxy energy)
        $r = ExtractionCalc::calc('Iron Ore', 120, []);

        $this->assertEqualsWithDelta(120, $r['rate_per'], 1e-9);
        $this->assertEqualsWithDelta(15, $r['power'], 1e-6);
    }

    public function test_shards_clamped_to_three(): void
    {
        // shards > 3 clamps to 3 (250%)
        $r = ExtractionCalc::calc('Iron Ore', 1, ['miner' => 'mk2', 'shards' => 9]);

        $this->assertEqualsWithDelta(2.5, $r['overclock'], 1e-9);
    }

    public function test_biomass_raws_are_not_extractable(): void
    {
        // V74: biomass/organic raws have no extractor — 'none', not a miner default
        foreach (['Wood', 'Leaves', 'Mycelia', 'Alien Protein', 'Hog Remains'] as $raw) {
            $this->assertSame('none', ExtractionCalc::extractorType($raw), "{$raw} must be non-extractable");
            $this->assertFalse(ExtractionCalc::isExtractable($raw));
        }
    }

    public function test_extractable_raws_keep_their_extractor_type(): void
    {
        $this->assertSame('miner', ExtractionCalc::extractorType('Iron Ore'));
        $this->assertSame('miner', ExtractionCalc::extractorType('SAM'));
        $this->assertSame('water', ExtractionCalc::extractorType('Water'));
        $this->assertSame('oil', ExtractionCalc::extractorType('Crude Oil'));
        $this->assertSame('well', ExtractionCalc::extractorType('Nitrogen Gas'));

        foreach (['Iron Ore', 'Water', 'Crude Oil', 'Nitrogen Gas', 'SAM'] as $raw) {
            $this->assertTrue(ExtractionCalc::isExtractable($raw));
        }
    }

    public function test_calc_on_non_extractable_returns_zeros(): void
    {
        $r = ExtractionCalc::calc('Wood', 120, ['shards' => 3]);

        $this->assertSame('none', $r['type']);
        $this->assertSame(0, $r['count']);
        $this->assertEqualsWithDelta(0, $r['power'], 1e-9);
    }
}
