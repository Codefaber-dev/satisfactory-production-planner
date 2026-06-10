<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\BuildingDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeEnergyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--env' => 'testing']);
    }

    #[Test]
    public function it_seeds_before_each_test()
    {
        $this->assertTrue(Recipe::count() > 30);
    }

    #[Test]
    public function it_calcs_the_energy_cost_of_iron_plate()
    {
        // energy() uses qty = base_per_min * 1000; even-rows fires at that scale,
        // adjusting clock below 100%. Derive the adjusted clock from BuildingDetails
        // (same source as energy()), then compute expected energy via calculatePowerUsage.
        $belt_speed = 780; // ProductionGlobals default

        $plate_recipe = r('Iron Plate');
        $ingot_recipe = r('Iron Ingot');

        // Sub-stage qty matches what ProductionCalculator calculates: parent_qty * (ingredient_base_qty / parent_base_per_min)
        $qty_plate = $plate_recipe->base_per_min * 1000;
        $ingot_ratio = $plate_recipe->ingredients->firstWhere('name', 'Iron Ingot')->pivot->base_qty / $plate_recipe->base_per_min;
        $qty_ingot = $qty_plate * $ingot_ratio;

        $clock_plate = BuildingDetails::calc($plate_recipe, $qty_plate, $belt_speed)->first()['clock_speed'];
        $clock_ingot = BuildingDetails::calc($ingot_recipe, $qty_ingot, $belt_speed)->first()['clock_speed'];

        $constructor = Building::ofName('Constructor')->variant('mk1');
        $smelter = Building::ofName('Smelter')->variant('mk1');

        $iron_plate = 60 * $constructor->calculatePowerUsage($clock_plate / 100)
            / ($plate_recipe->base_per_min * $clock_plate / 100);

        $iron_ingots = $ingot_ratio * 60 * $smelter->calculatePowerUsage($clock_ingot / 100)
            / ($ingot_recipe->base_per_min * $clock_ingot / 100);

        $iron_ores = $ingot_ratio * energyStage('Iron Ore');

        $this->assertEqualsWithDelta($iron_ores + $iron_ingots + $iron_plate, energy('Iron Plate'), 1e-9);
    }

    #[Test]
    public function it_calcs_the_energy_cost_of_wire()
    {
        $belt_speed = 780;

        $wire_recipe = r('Wire');
        $copper_ingot_recipe = r('Copper Ingot');

        $qty_wire = $wire_recipe->base_per_min * 1000;
        $ingot_ratio = $wire_recipe->ingredients->firstWhere('name', 'Copper Ingot')->pivot->base_qty / $wire_recipe->base_per_min;
        $qty_ingot = $qty_wire * $ingot_ratio;

        $clock_wire = BuildingDetails::calc($wire_recipe, $qty_wire, $belt_speed)->first()['clock_speed'];
        $clock_ingot = BuildingDetails::calc($copper_ingot_recipe, $qty_ingot, $belt_speed)->first()['clock_speed'];

        $constructor = Building::ofName('Constructor')->variant('mk1');
        $smelter = Building::ofName('Smelter')->variant('mk1');

        $wire = 60 * $constructor->calculatePowerUsage($clock_wire / 100)
            / ($wire_recipe->base_per_min * $clock_wire / 100);

        $copper_ingots = $ingot_ratio * 60 * $smelter->calculatePowerUsage($clock_ingot / 100)
            / ($copper_ingot_recipe->base_per_min * $clock_ingot / 100);

        $ore = $ingot_ratio * energyStage('Copper Ore');

        $this->assertEqualsWithDelta($ore + $copper_ingots + $wire, energy('Wire'), 1e-9);
    }

    // #[Test]
    // public function it_calculates_the_most_efficient_recipe_per_ingredient()
    // {
    //    Ingredient::processed()->get()->map(function($ingredient) {
    //       echo $ingredient->name . "\n";
    //
    //       return $ingredient->mostEnergyEfficientRecipe();
    //    });
    // }
}
