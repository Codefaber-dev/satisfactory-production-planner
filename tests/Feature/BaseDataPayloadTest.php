<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use App\Support\BaseDataPayload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * V73: the baseData() recipes/products props come from a cached plain-array
 * payload — appends baked at fill time, per-user favorite flag merged per
 * request (never cached, B13), invalidated on Recipe/Ingredient save (B17).
 */
class BaseDataPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);

        // Per-process store: isolates cache assertions from parallel workers
        // flushing the shared redis test prefix.
        config()->set('cache.default', 'array');
        Cache::flush();
    }

    #[Test]
    public function recipes_payload_is_plain_arrays_with_appends_baked_and_no_favorite(): void
    {
        $this->get(route('dashboard'))->assertOk();

        $cached = Cache::get(BaseDataPayload::RECIPES_KEY);

        $this->assertIsArray($cached);
        $this->assertNotEmpty($cached);

        // json round-trip succeeding on the raw entry proves no objects survived
        $this->assertSame($cached, json_decode(json_encode($cached), true));

        $recipe = collect($cached)->first()[0];
        $this->assertArrayHasKey('tier', $recipe);
        $this->assertArrayHasKey('energy_efficient', $recipe);
        $this->assertArrayHasKey('resource_efficient', $recipe);
        $this->assertArrayHasKey('ingredients', $recipe);

        // per-user flag must never be baked into the shared entry (B13)
        foreach ($cached as $group) {
            foreach ($group as $r) {
                $this->assertArrayNotHasKey('favorite', $r);
            }
        }
    }

    #[Test]
    public function products_payload_is_cached_plain_arrays(): void
    {
        $this->get(route('dashboard'))->assertOk();

        $cached = Cache::get(BaseDataPayload::PRODUCTS_KEY);

        $this->assertIsArray($cached);
        $this->assertNotEmpty($cached);
        $this->assertSame($cached, json_decode(json_encode($cached), true));
    }

    #[Test]
    public function favorite_flag_is_per_user_even_with_warm_shared_cache(): void
    {
        // Warm the shared payload as a guest with no favorites.
        $this->get(route('dashboard'))->assertOk();

        $recipe = Recipe::whereNotNull('description')->firstOrFail();
        $user = User::factory()->create();
        $user->favorite_recipes()->attach($recipe->id, ['ingredient_id' => $recipe->product_id]);

        // Re-resolve the Favorites singleton (container + facade caches) for
        // the authenticated user.
        $this->app->forgetInstance('Favorites');
        \App\Favorites\Facades\Favorites::clearResolvedInstance('Favorites');
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(function ($page) use ($recipe) {
            $recipes = $page->toArray()['props']['recipes'];
            $flags = collect($recipes)->flatten(1)->keyBy('id');

            $this->assertTrue($flags[$recipe->id]['favorite']);

            // everything the user did not favorite stays false
            $others = $flags->except($recipe->id);
            $this->assertFalse($others->contains(fn ($r) => $r['favorite']));

            return true;
        });

        // Cached entry still favorite-free after the favorited request (B13).
        foreach (Cache::get(BaseDataPayload::RECIPES_KEY) as $group) {
            foreach ($group as $r) {
                $this->assertArrayNotHasKey('favorite', $r);
            }
        }
    }

    #[Test]
    public function recipe_save_flushes_payload_cache(): void
    {
        $this->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(BaseDataPayload::RECIPES_KEY));

        Recipe::first()->save();

        $this->assertFalse(Cache::has(BaseDataPayload::RECIPES_KEY));
        $this->assertFalse(Cache::has(BaseDataPayload::PRODUCTS_KEY));
    }

    #[Test]
    public function ingredient_save_flushes_payload_cache(): void
    {
        $this->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(BaseDataPayload::PRODUCTS_KEY));

        Ingredient::first()->save();

        $this->assertFalse(Cache::has(BaseDataPayload::RECIPES_KEY));
        $this->assertFalse(Cache::has(BaseDataPayload::PRODUCTS_KEY));
    }
}
