<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_requires_auth(): void
    {
        $response = $this->getJson(route('recipes.index'));

        $response->assertUnauthorized();
    }

    #[Test]
    public function index_returns_all_recipes_for_auth_user(): void
    {
        Recipe::factory()->count(3)->create();

        $response = $this->actingAsUser()->getJson(route('recipes.index'));

        $response->assertOk()->assertJsonCount(3);
    }

    #[Test]
    public function show_returns_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $response = $this->actingAsUser()->getJson(route('recipes.show', $recipe));

        $response->assertOk()->assertJsonFragment(['id' => $recipe->id]);
    }

    #[Test]
    public function store_requires_auth(): void
    {
        $response = $this->postJson(route('recipes.store'), []);

        $response->assertUnauthorized();
    }

    #[Test]
    public function store_creates_recipe_and_returns_201(): void
    {
        $ingredient = Ingredient::factory()->create();
        $building = Building::factory()->create();

        $payload = [
            'product_id' => $ingredient->id,
            'building_id' => $building->id,
            'base_per_min' => 30,
            'base_yield' => 1,
            'alt_recipe' => false,
        ];

        $response = $this->actingAsUser()->postJson(route('recipes.store'), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('recipes', [
            'product_id' => $ingredient->id,
            'building_id' => $building->id,
            'base_per_min' => 30,
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->actingAsUser()->postJson(route('recipes.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id', 'building_id', 'base_per_min', 'base_yield']);
    }

    #[Test]
    public function store_validates_product_id_exists(): void
    {
        $building = Building::factory()->create();

        $response = $this->actingAsUser()->postJson(route('recipes.store'), [
            'product_id' => 99999,
            'building_id' => $building->id,
            'base_per_min' => 30,
            'base_yield' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id']);
    }

    #[Test]
    public function update_modifies_recipe_and_returns_202(): void
    {
        $recipe = Recipe::factory()->create(['base_per_min' => 30]);

        $response = $this->actingAsUser()->patchJson(
            route('recipes.update', $recipe),
            ['base_per_min' => 45]
        );

        $response->assertStatus(202);
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id, 'base_per_min' => 45]);
    }

    #[Test]
    public function update_requires_auth(): void
    {
        $recipe = Recipe::factory()->create();

        $response = $this->patchJson(route('recipes.update', $recipe), ['base_per_min' => 45]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function destroy_deletes_recipe_and_returns_202(): void
    {
        $recipe = Recipe::factory()->create();

        $response = $this->actingAsUser()->deleteJson(route('recipes.destroy', $recipe));

        $response->assertStatus(202);
        $this->assertModelMissing($recipe);
    }

    #[Test]
    public function destroy_requires_auth(): void
    {
        $recipe = Recipe::factory()->create();

        $response = $this->deleteJson(route('recipes.destroy', $recipe));

        $response->assertUnauthorized();
    }

    #[Test]
    public function save_triggers_cache_invalidation(): void
    {
        $recipe = Recipe::factory()->create(['description' => 'Cache Test Recipe']);
        Cache::forever('recipes.Cache Test Recipe', 'stale');
        Cache::forever('production_calc_abc123', 'stale');

        $this->actingAsUser()->patchJson(
            route('recipes.update', $recipe),
            ['base_per_min' => 99]
        );

        $this->assertFalse(Cache::has('recipes.Cache Test Recipe'));
        $this->assertFalse(Cache::has('production_calc_abc123'));
    }
}
