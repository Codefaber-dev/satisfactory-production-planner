<?php

namespace Tests\Unit;

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
        $belt_speed = 780; // ProductionGlobals default

        $plate_recipe = r('Iron Plate');
        $ingot_recipe = r('Iron Ingot');

        $qty_plate = $plate_recipe->base_per_min * 1000;
        $ingot_ratio = $plate_recipe->ingredients->firstWhere('name', 'Iron Ingot')->pivot->base_qty / $plate_recipe->base_per_min;
        $qty_ingot = $qty_plate * $ingot_ratio;

        // Use energy_per_item from BuildingDetails directly (same source as getTotalEnergy())
        $iron_plate = BuildingDetails::calc($plate_recipe, $qty_plate, $belt_speed)->first()['energy_per_item'];
        $iron_ingots = $ingot_ratio * BuildingDetails::calc($ingot_recipe, $qty_ingot, $belt_speed)->first()['energy_per_item'];
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

        // Use energy_per_item from BuildingDetails directly (same source as getTotalEnergy())
        $wire = BuildingDetails::calc($wire_recipe, $qty_wire, $belt_speed)->first()['energy_per_item'];
        $copper_ingots = $ingot_ratio * BuildingDetails::calc($copper_ingot_recipe, $qty_ingot, $belt_speed)->first()['energy_per_item'];
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
