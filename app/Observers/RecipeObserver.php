<?php

namespace App\Observers;

use App\Models\Recipe;
use App\Production\Concerns\InvalidatesProductionCache;
use App\Support\BaseDataPayload;
use Illuminate\Support\Facades\Cache;

class RecipeObserver
{
    use InvalidatesProductionCache;

    public function saved(Recipe $recipe): void
    {
        $key = $recipe->description ?? optional($recipe->product)->name;

        if ($key) {
            Cache::forget("recipes.{$key}");
        }

        Cache::forget("recipe.{$recipe->id}.energy_efficient");
        Cache::forget("recipe.{$recipe->id}.resource_efficient");
        Cache::forget("base_recipe.{$recipe->product_id}");
        Cache::forget("most_energy_efficient_recipe.{$recipe->product_id}");
        Cache::forget("most_resource_efficient_recipe.{$recipe->product_id}");
        Cache::forget('loop_catalog');

        // T154: a Package recipe edit invalidates its fluid's package_recipe lookup
        foreach ($recipe->ingredients as $ingredient) {
            Cache::forget("package_recipe.{$ingredient->name}");
        }

        BaseDataPayload::flush();

        $this->flushProductionCalcKeys();
    }
}
