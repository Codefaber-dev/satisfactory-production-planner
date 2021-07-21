<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_have_favorite_recipes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Ingredient::factory()->create();


        $default = Recipe::factory()->create(['product_id' => $product->id, 'alt_recipe' => false]);
        $recipe1 = Recipe::factory()->create(['product_id' => $product->id, 'alt_recipe' => true]);
        $recipe2 = Recipe::factory()->create(['product_id' => $product->id, 'alt_recipe' => true]);

        $this->assertTrue($product->defaultRecipe()->is($default));

        $user->addFavorite($recipe1);

        $this->assertTrue($product->defaultRecipe()->is($recipe1));

        $this->assertTrue($user->favorite_recipes()->first()->is($recipe1));
        $this->assertNotContains($recipe2, $user->favorite_recipes);

        $user->addFavorite($recipe2);
        $this->assertTrue($product->defaultRecipe()->is($recipe2));

        $this->assertTrue($user->favorite_recipes()->first()->is($recipe2));
        $this->assertNotContains($recipe1, $user->favorite_recipes);

        $user->removeFavorite($recipe2);
        $this->assertNotContains($recipe2, $user->favorite_recipes);
        $this->assertTrue($product->defaultRecipe()->is($default));
    }


    /** @test */
    public function it_can_have_favorite_recipes_by_description()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Ingredient::factory()->create();

        $recipe = Recipe::factory()->create(['product_id' => $product->id, 'description' => 'Stitched Modular Frame']);

        $user->addFavoriteByName('Stitched Modular Frame');

        $this->assertTrue($product->defaultRecipe()->is($recipe));

        $this->assertTrue($user->favorite_recipes()->first()->is($recipe));

    }
}
