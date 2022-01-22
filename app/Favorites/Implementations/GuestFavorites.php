<?php

namespace App\Favorites\Implementations;

use App\Favorites\Contracts\FavoritesContract;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

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
        return "favorites.ingredient.{$ingredient->id}";
    }

    protected function getCacheTag() : string
    {
        $session_id = session()->getId();

        return "favorites.{$session_id}";
    }

    public function all(): Collection
    {
        $ids = collect(Redis::hGetAll($this->getCacheTag()))->values();

        return Recipe::find($ids);
    }
}
