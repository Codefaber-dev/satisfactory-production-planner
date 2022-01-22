<?php

namespace App\Favorites\Contracts;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;

interface FavoritesContract
{
    public function all(): Collection;
    public function get(Ingredient $ingredient): Recipe;
    public function set(Ingredient $ingredient, Recipe $recipe): void;
    public function setByName(Ingredient $ingredient, string $name): void;
    public function isFavorite(Recipe $recipe): bool;
}
