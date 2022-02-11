<?php

use App\Helpers\ProductionCalculator;
use App\Helpers\RawIngredientCalculator;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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

function i($name) {
    $key = is_string($name) ? $name : $name->name;

    return Cache::rememberForever("ingredients.$key", function() use ($name) {
        if (is_string($name)) {
            return Ingredient::ofName($name);
        }

        return $name;
    });
}

function raw($recipe, $use_alts = false, $qty = 1) {
    if ( is_string($recipe) )
        $recipe = r($recipe);

    return RawIngredientCalculator::calc($recipe, $use_alts, $qty);
}

// lower is better
function energy($recipe, $use_alts = false, $qty = 100)
{
    try {
        $raw = raw($recipe, $use_alts, $qty);

        // calc the energy of raw extraction
        $raw_extraction = (int) collect($raw)
            ->reduce(function($carry, $qty, $ingredient) {
                //print_r(compact('carry','qty','ingredient'));
                return $carry + config("raw_materials.energy cost.{$ingredient}")*$qty;
            }, 0) / $qty;

        if (is_string($recipe))
            $recipe = r($recipe);

        // calc energy of production
        $production = ProductionCalculator::calc($recipe->product,$qty,$recipe)->energy();

        return $raw_extraction + $production;
    }
    catch(ErrorException $e)
    {
        if (Str::of($e->getMessage())->contains("no base recipe"))
            return $qty * config("raw_materials.energy cost.{$recipe}");
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
