<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T124: model-level caches (r()/i()/b(), baseRecipe, efficiency flags) use a
 * finite TTL instead of rememberForever, and observers still invalidate them
 * on save (B17).
 */
class ModelCacheTtlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        config()->set('cache.default', 'array');
        Cache::flush();
    }

    #[Test]
    public function helper_lookups_cache_and_return_models(): void
    {
        $recipe = r('Iron Plate');
        $ingredient = i('Iron Plate');
        $building = b('Constructor');

        $this->assertInstanceOf(\App\Models\Recipe::class, $recipe);
        $this->assertInstanceOf(\App\Models\Ingredient::class, $ingredient);
        $this->assertInstanceOf(\App\Models\Building::class, $building);

        $this->assertTrue(Cache::has('recipes.Iron Plate'));
        $this->assertTrue(Cache::has('ingredients.Iron Plate'));
        $this->assertTrue(Cache::has('buildings.Constructor'));
    }

    #[Test]
    public function recipe_save_still_invalidates_model_caches(): void
    {
        $recipe = r('Iron Plate');
        i('Iron Plate')->baseRecipe();

        $this->assertTrue(Cache::has('recipes.Iron Plate'));
        $this->assertTrue(Cache::has("base_recipe.{$recipe->product_id}"));

        $recipe->save();

        $this->assertFalse(Cache::has('recipes.Iron Plate'));
        $this->assertFalse(Cache::has("base_recipe.{$recipe->product_id}"));
        $this->assertFalse(Cache::has("recipe.{$recipe->id}.energy_efficient"));
    }

    #[Test]
    public function no_remember_forever_remains_on_model_lookups(): void
    {
        // Guard against regression: the model-level call sites must not use
        // rememberForever (finite TTL only). Version-keyed caches (release
        // notes) are exempt — their keys rotate.
        $sources = [
            app_path('Helpers/helpers.php'),
            app_path('Models/Recipe.php'),
            app_path('Models/Ingredient.php'),
        ];

        foreach ($sources as $file) {
            $this->assertStringNotContainsString(
                'rememberForever',
                file_get_contents($file),
                "$file still uses rememberForever"
            );
        }
    }
}
