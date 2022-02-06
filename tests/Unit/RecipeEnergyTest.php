<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
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
        // energy cost of a recipe is the energy cost of the extraction of the raw materials,
        // plus the energy cost of production

        $extraction = 1.5*energy('Iron Ore');
        $smelter = 1e6 * Building::ofName("Smelter")->variant("mk1")->base_power;
        $constructor = 1e6 * Building::ofName("Constructor")->variant("mk1")->base_power;
        $yield = r("Iron Plate")->base_per_min;
        $production = ($smelter+$constructor)/$yield;
        $total = $extraction + $production;

        $this->assertEquals($total, energy('Iron Plate'));
    }

    /** @test */
    public function it_calcs_the_energy_cost_of_wire()
    {
        // energy cost of a recipe is the energy cost of the extraction of the raw materials,
        // plus the energy cost of production

        $extraction = 0.5*energy('Copper Ore');
        $smelter = 1e6 * Building::ofName("Smelter")->variant("mk1")->calculatePowerUsage(1);
        $constructor = 1e6 * Building::ofName("Constructor")->variant("mk1")->calculatePowerUsage(0.5);
        $yield = r("Wire")->base_per_min;
        $production = ($smelter+$constructor)/$yield;
        $total = $extraction + $production;

        $this->assertEquals((int)$total, energy('Wire'));
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
