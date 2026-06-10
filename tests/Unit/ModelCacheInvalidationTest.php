<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelCacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    #[Test]
    public function saving_a_recipe_forgets_recipe_key(): void
    {
        $recipe = Recipe::factory()->create(['description' => 'Test Recipe']);

        Cache::forever('recipes.Test Recipe', 'stale');
        Cache::forever("recipe.{$recipe->id}.energy_efficient", true);
        Cache::forever("recipe.{$recipe->id}.resource_efficient", false);
        Cache::forever("base_recipe.{$recipe->product_id}", 'stale');
        Cache::forever("most_energy_efficient_recipe.{$recipe->product_id}", 'stale');
        Cache::forever("most_resource_efficient_recipe.{$recipe->product_id}", 'stale');

        $recipe->touch();

        $this->assertFalse(Cache::has('recipes.Test Recipe'));
        $this->assertFalse(Cache::has("recipe.{$recipe->id}.energy_efficient"));
        $this->assertFalse(Cache::has("recipe.{$recipe->id}.resource_efficient"));
        $this->assertFalse(Cache::has("base_recipe.{$recipe->product_id}"));
        $this->assertFalse(Cache::has("most_energy_efficient_recipe.{$recipe->product_id}"));
        $this->assertFalse(Cache::has("most_resource_efficient_recipe.{$recipe->product_id}"));
    }

    #[Test]
    public function saving_a_recipe_flushes_production_calc_keys(): void
    {
        $recipe = Recipe::factory()->create();

        Cache::forever('production_calc_aabbcc', 'stale');

        $recipe->touch();

        $this->assertFalse(Cache::has('production_calc_aabbcc'));
    }

    #[Test]
    public function saving_an_ingredient_forgets_ingredient_keys(): void
    {
        $ingredient = Ingredient::factory()->create(['name' => 'Test Ore']);

        Cache::forever('ingredients.Test Ore', 'stale');
        Cache::forever("base_recipe.{$ingredient->id}", 'stale');
        Cache::forever("most_energy_efficient_recipe.{$ingredient->id}", 'stale');
        Cache::forever("most_resource_efficient_recipe.{$ingredient->id}", 'stale');

        $ingredient->touch();

        $this->assertFalse(Cache::has('ingredients.Test Ore'));
        $this->assertFalse(Cache::has("base_recipe.{$ingredient->id}"));
        $this->assertFalse(Cache::has("most_energy_efficient_recipe.{$ingredient->id}"));
        $this->assertFalse(Cache::has("most_resource_efficient_recipe.{$ingredient->id}"));
    }

    #[Test]
    public function saving_an_ingredient_flushes_production_calc_keys(): void
    {
        $ingredient = Ingredient::factory()->create();

        Cache::forever('production_calc_ddeeff', 'stale');

        $ingredient->touch();

        $this->assertFalse(Cache::has('production_calc_ddeeff'));
    }
}
