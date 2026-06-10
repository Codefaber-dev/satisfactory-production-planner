<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_product_id()
    {
        $this->expectException(QueryException::class);

        Recipe::factory()->create([
            'product_id' => null,
        ]);
    }

    #[Test]
    public function it_has_a_building_id()
    {
        $this->expectException(QueryException::class);

        Recipe::factory()->create([
            'building_id' => null,
        ]);
    }

    #[Test]
    public function it_has_a_base_yield()
    {
        $this->expectException(QueryException::class);

        Recipe::factory()->create([
            'base_yield' => null,
        ]);
    }

    #[Test]
    public function it_is_associated_with_a_product()
    {
        $product = Ingredient::factory()->create();

        $recipe = Recipe::factory()->create([
            'product_id' => $product->id,
        ]);

        $this->assertTrue($recipe->product->is($product));
    }

    #[Test]
    public function it_is_associated_with_a_building()
    {
        $building = Building::factory()->create();

        $recipe = Recipe::factory()->create([
            'building_id' => $building->id,
        ]);

        $this->assertTrue($recipe->building->is($building));
    }

    #[Test]
    public function it_has_many_ingredients()
    {
        $ingredients = Ingredient::factory()->count(3)->create();

        $recipe = Recipe::factory()->create();

        $recipe->addIngredient($ingredients[0], 5);
        $recipe->addIngredient($ingredients[1], 10);
        $recipe->addIngredient($ingredients[2], 20);

        $this->assertCount(3, $recipe->ingredients);
        $this->assertEquals(5, $recipe->ingredients()->find(1)->pivot->base_qty);
        $this->assertEquals(10, $recipe->ingredients()->find(2)->pivot->base_qty);
        $this->assertEquals(20, $recipe->ingredients()->find(3)->pivot->base_qty);
    }

    #[Test]
    public function it_has_many_byproducts()
    {
        $byproducts = Ingredient::factory()->count(3)->create();

        $recipe = Recipe::factory()->create();

        $recipe->addByproduct($byproducts[0], 5);
        $recipe->addByproduct($byproducts[1], 10);
        $recipe->addByproduct($byproducts[2], 20);

        $this->assertCount(3, $recipe->byproducts);
        $this->assertEquals(5, $recipe->byproducts()->find(1)->pivot->base_qty);
        $this->assertEquals(10, $recipe->byproducts()->find(2)->pivot->base_qty);
        $this->assertEquals(20, $recipe->byproducts()->find(3)->pivot->base_qty);
    }
}
