<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeEnergyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed',['--env' => 'testing']);
    }

    /** @test */
    public function it_seeds_before_each_test()
    {
        $this->assertTrue(Recipe::count() > 30);
    }

    /** @test */
    public function it_calcs_the_energy_cost_of_iron_plate()
    {
        $iron_plate = 60 * Building::ofName("Constructor")->variant("mk1")->base_power / r('Iron Plate')->base_per_min;

        $iron_ingots = 1.5 * 60 * Building::ofName("Smelter")->variant("mk1")->base_power / r('Iron Ingot')->base_per_min;

        $iron_ores = 1.5 * energyStage('Iron Ore');

        $this->assertEquals($total = $iron_ores + $iron_ingots + $iron_plate, energy('Iron Plate'));
    }

    /** @test */
    public function it_calcs_the_energy_cost_of_wire()
    {
        // energy cost of a recipe is the energy cost of the extraction of the raw materials,
        // plus the energy cost of production

        $ore = 0.5*energyStage('Copper Ore');

        $copper_ingots = 0.5 * 60 * Building::ofName("Smelter")->variant("mk1")->base_power / r('Copper Ingot')->base_per_min;

        $wire = 60 * Building::ofName("Constructor")->variant("mk1")->base_power / r('Wire')->base_per_min;

        $this->assertEquals($ore + $copper_ingots + $wire, energy('Wire'));
    }

    ///** @test */
    //public function it_calculates_the_most_efficient_recipe_per_ingredient()
    //{
    //    Ingredient::processed()->get()->map(function($ingredient) {
    //       echo $ingredient->name . "\n";
    //
    //       return $ingredient->mostEnergyEfficientRecipe();
    //    });
    //}
}
