<?php

namespace App\Favorites\Facades;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;


/**
 * @method static Collection all()
 * @method static Recipe get(Ingredient $ingredient)
 * @method static void set(Ingredient $ingredient, Recipe $recipe)
 * @method static void setByName(Ingredient $ingredient, string $name)
 * @method static void setDefault(Ingredient $ingredient)
 * @method static bool isFavorite(Recipe $recipe)
*/
class Favorites extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Favorites';
    }
}
