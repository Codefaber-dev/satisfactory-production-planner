<?php

namespace Tests\Feature;

use App\Favorites\Facades\Favorites;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoritesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
    }

    #[Test]
    public function index_renders_favorites_page_for_guest(): void
    {
        $response = $this->get(route('favorites'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Favorites/Index'));
    }

    #[Test]
    public function index_renders_favorites_page_for_auth_user(): void
    {
        $response = $this->actingAsUser()->get(route('favorites'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Favorites/Index'));
    }

    #[Test]
    public function store_sets_favorite_and_redirects(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();

        $response = $this->post(route('favorites.store'), ['recipe' => $recipe->id]);

        $response->assertRedirect(route('favorites'));
    }

    #[Test]
    public function store_ignores_missing_recipe_and_still_redirects(): void
    {
        $response = $this->post(route('favorites.store'), ['recipe' => 99999]);

        $response->assertRedirect(route('favorites'));
    }

    #[Test]
    public function destroy_clears_favorite_and_redirects(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();
        Favorites::set($ingredient, $recipe);

        $response = $this->delete(route('favorites.destroy', $ingredient->id));

        $response->assertRedirect(route('favorites'));
    }

    #[Test]
    public function destroy_all_clears_all_favorites_and_redirects(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();
        Favorites::set($ingredient, $recipe);

        $response = $this->delete(route('favorites.destroyAll'));

        $response->assertRedirect(route('favorites'));
    }

    #[Test]
    public function guest_store_persists_to_redis(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();

        $this->post(route('favorites.store'), ['recipe' => $recipe->id]);

        $set = Favorites::all();
        $this->assertTrue($set->contains(fn ($r) => $r->id === $recipe->id));
    }

    #[Test]
    public function guest_redis_key_has_ttl_after_store(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();

        $this->post(route('favorites.store'), ['recipe' => $recipe->id]);

        // Verify key has a TTL (not -1 = no expiry)
        $cacheTag = 'favorites.'.session()->getId();
        $ttl = Redis::ttl($cacheTag);
        $this->assertGreaterThan(0, $ttl);
    }
}
