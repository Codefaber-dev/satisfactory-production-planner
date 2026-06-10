<?php

namespace Tests\Unit;

use App\Enums\Building;
use App\Enums\Ingredient;
use App\PowerPlanner\Generators\BiomassBurner;
use App\PowerPlanner\Generators\CoalGenerator;
use App\PowerPlanner\Generators\FuelGenerator;
use App\PowerPlanner\Generators\NuclearPowerPlant;
use App\PowerPlanner\PowerPlanner;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PowerPlannerGeneratorTest extends TestCase
{
    #[Test]
    public function coal_generator_single_unit_at_75_mw(): void
    {
        $result = CoalGenerator::make(75)->calculate();

        $this->assertEquals(1, $result['num']);
        $this->assertEquals(75, $result['output']);
        $this->assertEqualsWithDelta(15.0, $result['fuel']['Coal'], 1e-9);
        $this->assertEqualsWithDelta(25.0, $result['fuel']['Petroleum Coke'], 1e-9);
        $this->assertEqualsWithDelta(45.0, $result['other']['Water'], 1e-9);
        $this->assertEmpty($result['waste']);
    }

    #[Test]
    public function coal_generator_scales_linearly(): void
    {
        $one = CoalGenerator::make(75)->calculate();
        $two = CoalGenerator::make(150)->calculate();

        $this->assertEquals(2, $two['num']);
        $this->assertEquals(150, $two['output']);
        $this->assertEqualsWithDelta($one['fuel']['Coal'] * 2, $two['fuel']['Coal'], 1e-9);
        $this->assertEqualsWithDelta($one['other']['Water'] * 2, $two['other']['Water'], 1e-9);
    }

    #[Test]
    public function biomass_burner_single_unit_at_30_mw(): void
    {
        $result = BiomassBurner::make(30)->calculate();

        $this->assertEquals(1, $result['num']);
        $this->assertEquals(30, $result['output']);
        $this->assertEqualsWithDelta(10.0, $result['fuel']['Biomass'], 1e-9);
        $this->assertEqualsWithDelta(4.0, $result['fuel']['Solid Biofuel'], 1e-9);
        $this->assertEmpty($result['other']);
        $this->assertEmpty($result['waste']);
    }

    #[Test]
    public function fuel_generator_single_unit_at_250_mw(): void
    {
        $result = FuelGenerator::make(250)->calculate();

        $this->assertEquals(1, $result['num']);
        $this->assertEquals(250, $result['output']);
        $fuelKey = Ingredient::FUEL->value;
        $this->assertEqualsWithDelta(20.0, $result['fuel'][$fuelKey], 1e-9);
    }

    #[Test]
    public function nuclear_plant_single_unit_at_2500_mw(): void
    {
        $result = NuclearPowerPlant::make(2500)->calculate();

        $this->assertEquals(1, $result['num']);
        $this->assertEquals(2500, $result['output']);

        $waterKey = Ingredient::WATER->value;
        $this->assertEqualsWithDelta(240.0, $result['other'][$waterKey], 1e-9);

        // Uranium fuel rod → uranium waste
        $uraniumRodKey = Ingredient::URANIUM_FUEL_ROD->value;
        $uraniumWasteKey = Ingredient::URANIUM_WASTE->value;
        $this->assertArrayHasKey($uraniumRodKey, $result['waste']);
        $this->assertEqualsWithDelta(10.0, $result['waste'][$uraniumRodKey][$uraniumWasteKey], 1e-9);

        // Plutonium fuel rod → plutonium waste
        $plutoniumRodKey = Ingredient::PLUTONIUM_FUEL_ROD->value;
        $plutoniumWasteKey = Ingredient::PLUTONIUM_WASTE->value;
        $this->assertArrayHasKey($plutoniumRodKey, $result['waste']);
        $this->assertEqualsWithDelta(1.0, $result['waste'][$plutoniumRodKey][$plutoniumWasteKey], 1e-9);
    }

    #[Test]
    public function power_planner_returns_all_four_generator_types(): void
    {
        $results = PowerPlanner::make(100)->calculate();

        $this->assertCount(4, $results);
        $names = $results->pluck('name');
        $this->assertTrue($names->contains(Building::BIOMASS_BURNER->value));
        $this->assertTrue($names->contains(Building::COAL_GENERATOR->value));
        $this->assertTrue($names->contains(Building::FUEL_GENERATOR->value));
        $this->assertTrue($names->contains(Building::NUCLEAR_POWER_PLANT->value));
    }

    #[Test]
    public function output_always_meets_or_exceeds_requested_mw(): void
    {
        foreach ([30, 75, 250, 1000, 2501] as $mw) {
            $results = PowerPlanner::make($mw)->calculate();

            $results->each(function ($result) use ($mw) {
                $this->assertGreaterThanOrEqual(
                    $mw,
                    $result['output'],
                    "{$result['name']} output {$result['output']}MW below requested {$mw}MW"
                );
            });
        }
    }

    #[Test]
    public function calculate_result_contains_required_keys(): void
    {
        $result = CoalGenerator::make(75)->calculate();

        foreach (['num', 'name', 'output', 'fuel', 'other', 'waste', 'build_cost', 'image', 'buildable_fuels'] as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: {$key}");
        }
    }

    #[Test]
    public function image_key_is_studly_case_png(): void
    {
        $result = CoalGenerator::make(75)->calculate();
        $this->assertEquals('CoalGenerator.png', $result['image']);

        $result = BiomassBurner::make(30)->calculate();
        $this->assertEquals('BiomassBurner.png', $result['image']);

        $result = NuclearPowerPlant::make(2500)->calculate();
        $this->assertEquals('NuclearPowerPlant.png', $result['image']);
    }
}
