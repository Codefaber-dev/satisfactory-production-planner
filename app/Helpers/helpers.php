<?php

use App\Models\Building;
use App\Production\ProductionCalculator;
use App\Helpers\RawIngredientCalculator;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

function guest_token(): string
{
    return Session::getId();
}

function forgetRecipe($name) {
    $key = is_string($name) ? $name : ($name->description ?? $name->product->name);
    Cache::forget("recipes.$key");
}

function forgetIngredient($name) {
    $key = is_string($name) ? $name : $name->name;
    Cache::forget("ingredients.$key");
}

function r($name, $force=false) {
    $key = is_string($name) ? $name : ($name->description ?? $name->product->name);

    if($force) {
        Cache::forget("recipes.$key");
    }

    return Cache::rememberForever("recipes.$key", function() use ($name) {
        if (is_string($name)) {
            return Recipe::ofName($name);
        }
        return $name;
    });
}

function i($name, bool $force = false) {
    $key = is_string($name) ? $name : $name->name;

    if ($force) {
        Cache::forget("ingredients.$key");
    }

    return Cache::rememberForever("ingredients.$key", function() use ($name) {
        if (is_string($name)) {
            return Ingredient::ofName($name);
        }

        return $name;
    });
}

function b($name) {
    return Building::whereName($name)->first();
}

function raw($recipe, $use_alts = false, $qty = 1) {
    if ( is_string($recipe) )
        $recipe = r($recipe);

    return RawIngredientCalculator::calc($recipe, $use_alts, $qty);
}

function energyStage(Recipe|string $recipe)
{
    if (is_string($recipe)) {
        try {
            $recipe = r($recipe);

            return 60 * $recipe->building->variant("mk1")->base_power / $recipe->base_per_min;
        }
        catch(ErrorException)
        {
            return config("raw_materials.energy cost.{$recipe}");
        }
    }

    return 60 * $recipe->building->variant("mk1")->base_power / $recipe->base_per_min;
}

// lower is better
function energy(Recipe|string $recipe, $use_alts = false)
{
    try {
        if(is_string($recipe)) {
            $recipe = r($recipe);
        }

        $qty = $recipe->base_per_min * 1000;

        $raw = raw($recipe, $use_alts, $recipe->base_per_min);

        // calc the energy of raw extraction
        $raw_extraction = collect($raw)
            ->reduce(function($carry, $qty, $ingredient) {
                //print_r(compact('carry','qty','ingredient'));
                return $carry + $qty * config("raw_materials.energy cost.{$ingredient}");
            }, 0) / $recipe->base_per_min;

        // calc energy of production
        $production = ProductionCalculator::make($recipe->product,$qty,$recipe)->getTotalEnergy() / $qty;

        return $raw_extraction + $production;
    }
    catch(ErrorException $e)
    {
        if (Str::of($e->getMessage())->contains("no base recipe")) {
            return config("raw_materials.energy cost.{$recipe}");
        }
    }
    catch(InvalidArgumentException $e)
    {
        if (Str::of($e->getMessage())->contains("Clock speed must be at least 0.01"))
            return "";
    }
}

// lower is better
function rarity($recipe, $use_alts = false, $qty = 100)
{
    $raw = raw($recipe, $use_alts, $qty);

    return (int) (10000 * collect($raw)
        ->reduce(function($carry, $qty, $ingredient) {
            //print_r(compact('carry','qty','ingredient'));
            return $carry + config("raw_materials.rarity.{$ingredient}")*$qty;
        }, 0)) / $qty;
}
