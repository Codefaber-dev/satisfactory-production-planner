<?php

namespace Tests\Unit;

use App\Factories\Implementations\GuestFactories;
use App\Favorites\Implementations\GuestFavorites;
use App\Models\Ingredient;
use App\MultiFactories\Implementations\GuestMultiFactories;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestRedisTtlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function guest_favorites_set_assigns_ttl(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();
        $favorites = new GuestFavorites();
        $favorites->set($ingredient, $recipe);

        $ttl = Redis::ttl($favorites->getCacheTag());
        $expected = config('session.lifetime') * 60;

        $this->assertGreaterThanOrEqual($expected, $ttl);
    }

    #[Test]
    public function guest_factories_create_assigns_ttl(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();
        $factories = new GuestFactories();
        $factories->create([
            'ingredient_id' => $ingredient->id,
            'recipe_id' => $recipe->id,
            'yield' => 10,
            'imports' => 0,
        ]);

        $cacheTag = 'factories.' . guest_token();
        $ttl = Redis::ttl($cacheTag);
        $expected = config('session.lifetime') * 60;

        $this->assertGreaterThanOrEqual($expected, $ttl);
    }

    #[Test]
    public function guest_multi_factories_create_assigns_ttl(): void
    {
        $ingredient = Ingredient::where('name', 'Iron Plate')->firstOrFail();
        $recipe = $ingredient->baseRecipe();
        $multiFactories = new GuestMultiFactories();
        $multiFactories->create([
            'outputs' => [
                [
                    'ingredient_id' => $ingredient->id,
                    'yield' => 10,
                    'recipe_id' => $recipe->id,
                ],
            ],
            'imports' => 0,
            'choices' => [],
        ]);

        $cacheTag = 'multi-factories.' . guest_token();
        $ttl = Redis::ttl($cacheTag);
        $expected = config('session.lifetime') * 60;

        $this->assertGreaterThanOrEqual($expected, $ttl);
    }
}
