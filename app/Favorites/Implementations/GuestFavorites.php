<?php

namespace App\Favorites\Implementations;

use App\Favorites\Contracts\FavoritesContract;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use function get_class;
use function guest_token;
use function is_object;

class GuestFavorites implements FavoritesContract
{
    /**
     * @throws \ErrorException
     */
    public function get(Ingredient $ingredient): Recipe
    {
        $id = (int) Redis::hGet($this->getCacheTag(), $this->getCacheKey($ingredient));

        if ( $id && $recipe = Recipe::find($id) ) {
            return $recipe;
        }

        return $ingredient->baseRecipe();
    }

    public function set(Ingredient $ingredient, Recipe $recipe): void
    {
        Redis::hSet($this->getCacheTag(), $this->getCacheKey($ingredient), $recipe->id);
    }

    public function setDefault(Ingredient $ingredient): void
    {
        Redis::hDel($this->getCacheTag(), $this->getCacheKey($ingredient));
    }

    public function setByName(Ingredient $ingredient, string $name): void
    {
        if ( ! $recipe = Recipe::ofName($name) ) {
             return;
         }

        $this->set($ingredient, $recipe);
    }

    public function isFavorite(Recipe $recipe): bool
    {
        return $recipe->id === ((int) Redis::hGet($this->getCacheTag(), $this->getCacheKey($recipe->product)));
    }

    protected function getCacheKey(Ingredient $ingredient) : string
    {
        $guestToken = guest_token();

        return "favorites.{$guestToken}.ingredient.{$ingredient->id}";
    }

    public function getCacheTag() : string
    {
        $guestToken = guest_token();

        return "favorites.{$guestToken}";
    }

    public function all(): Collection
    {
        $ids = collect(Redis::hGetAll($this->getCacheTag()))->values();

        return Recipe::find($ids);
    }

    public function getMappedFavorites(null|array|Collection $favorites): Collection
    {
        switch(true) {
            case is_null($favorites) :
            case is_array($favorites) && empty($favorites) :
            case is_object($favorites) && get_class($favorites) === Collection::class && $favorites->isEmpty() :
                return $this->all()->map(fn($recipe) => [$recipe->product->name => $recipe])->collapse();
        }

        return collect($favorites);
    }
}
