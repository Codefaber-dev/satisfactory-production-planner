<?php

namespace App\Favorites\Implementations;

use App\Favorites\Contracts\FavoritesContract;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Collection;

class UserFavorites implements FavoritesContract
{
    protected User $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * @throws \ErrorException
     */
    public function get(Ingredient $ingredient): Recipe
    {
        return $this->user->favorite_recipes()->firstWhere('ingredient_id',$ingredient->id) ?? $ingredient->baseRecipe();
    }

    public function set(Ingredient $ingredient, Recipe $recipe): void
    {
        $this->user->favorite_recipes()->wherePivot('ingredient_id','=',$ingredient->id)->detach();
        $this->user->favorite_recipes()->attach($recipe->id,['ingredient_id' => $ingredient->id]);
    }

    public function setDefault(Ingredient $ingredient): void
    {
        $this->user->favorite_recipes()->wherePivot('ingredient_id','=',$ingredient->id)->detach();
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
        return $this->user->favorite_recipes()->where('id',$recipe->id)->exists();
    }

    public function all(): Collection
    {
        return $this->user->favorite_recipes;
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
