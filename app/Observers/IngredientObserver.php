<?php

namespace App\Observers;

use App\Models\Ingredient;
use App\Production\Concerns\InvalidatesProductionCache;
use Illuminate\Support\Facades\Cache;

class IngredientObserver
{
    use InvalidatesProductionCache;

    public function saved(Ingredient $ingredient): void
    {
        Cache::forget("ingredients.{$ingredient->name}");
        Cache::forget("base_recipe.{$ingredient->id}");
        Cache::forget("most_energy_efficient_recipe.{$ingredient->id}");
        Cache::forget("most_resource_efficient_recipe.{$ingredient->id}");
        Cache::forget('loop_catalog');

        $this->flushProductionCalcKeys();
    }
}
