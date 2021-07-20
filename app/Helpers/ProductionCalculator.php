<?php

namespace App\Helpers;

use App\Models\Ingredient;
use App\Models\Recipe;
use InvalidArgumentException;

class ProductionCalculator
{
    protected $product;

    protected $qty;

    protected $parts = [];

    protected $recipe;

    public static function calc($ingredient, $qty)
    {
        return (new static(Ingredient::ofName($ingredient), $qty))->calculate();
    }

    public function __construct(Ingredient $ingredient, $qty)
    {
        $this->product = $ingredient;

        $this->qty = $qty;

        $this->recipe = $this->getRecipe($this->product);
    }

    protected function calculate()
    {
        //$this->parts[$this->getKeyName($this->product)] = $this->qty;

        $this->calculateSubRecipe($this->recipe, $this->qty);

        return [
            "parts per minute" => collect($this->parts)->sortKeys()->all()
        ];
    }

    protected function calculateSubRecipe(Recipe $recipe, $qty)
    {
        $recipe->ingredients->each(function($ingredient) use ($qty, $recipe) {

            // how many times per minute we need to make the recipe
            $multiplier = $qty/$recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = $multiplier * $ingredient->pivot->base_qty;

            // if we have the ingredient in the parts list then increment it, otherwise add it
            if (isset($this->parts[$ingredient->name]))
                $this->parts[$this->getKeyName($ingredient)] += $sub_qty;
            else
                $this->parts[$this->getKeyName($ingredient)] = $sub_qty;

            // continue calculating until we get to all raw ingredients
            if (! $ingredient->isRaw() )
                $this->calculateSubRecipe(
                    $this->getRecipe($ingredient),
                    $sub_qty
                );
        });
    }

    protected function getRecipe(Ingredient $ingredient)
    {
        $recipe = $ingredient->recipes()->first();

        if (! $recipe)
            return new Recipe;


        return $recipe; //->firstWhere('alt_recipe',false);
    }

    protected function getKeyName(Ingredient $ingredient) : string
    {
        return "{$ingredient->tier} - {$ingredient->name}";
    }
}
