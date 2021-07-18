<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Models\BuildingVariant;
use App\Models\Ingredient;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingVariantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_name()
    {
        $this->expectException(QueryException::class);

        BuildingVariant::factory()->create([
            'name' => null
        ]);
    }

    /** @test */
    public function it_has_a_building_id()
    {
        $this->expectException(QueryException::class);

        BuildingVariant::factory()->create([
            'name' => null
        ]);
    }

    /** @test */
    public function it_is_associated_with_a_building()
    {
        $building = Building::factory()->create();

        $variant = BuildingVariant::factory()->create([
            'building_id' => $building
        ]);

        $this->assertTrue($variant->building->is($building));
    }

    /** @test */
    public function it_has_a_recipe()
    {
        $ingredient1 = Ingredient::factory()->create();
        $ingredient2 = Ingredient::factory()->create();

        $variant = BuildingVariant::factory()->create();


        $variant->setRecipe([
            [ 'ingredient' => $ingredient1->name, 'qty' => 20 ],
            [ 'ingredient' => $ingredient2->name, 'qty' => 10 ],
        ]);

        $this->assertEquals(20, $variant->recipe()->find($ingredient1->id)->pivot->qty);
        $this->assertEquals(10, $variant->recipe()->find($ingredient2->id)->pivot->qty);
    }
}
