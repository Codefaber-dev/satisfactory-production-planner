<?php

namespace App\Production\Concerns;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\Step;

trait CalculatesSteps
{
    protected $ingredients;

    protected $children;

    protected function calculate(): void
    {
        if ($this->getProduct()->isRaw()) {
            return;
        }

        $this->ingredients = $this->getRecipe()->ingredients;

        $this->byproducts = $this->getRecipe()->byproducts;

        $this->children = $this->ingredients->map(function ($ingredient) {
            // how many times per minute we need to make the recipe
            $multiplier = $this->qty / $this->recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty;

            // return a new production step
            return static::make(
                product: $ingredient,
                qty: $sub_qty,
                recipe: $this->getSubRecipe($ingredient),
                parent: $this->name,
                chain: $this->chain,
                overrides: $this->overrides
            );
        });
    }

    protected function getSubRecipe(Ingredient $ingredient): ?Recipe
    {
        if ($ingredient->isRaw()) {
            return null;
        }

        if ($recipe = $this->getOverride($ingredient)) {
            return $recipe;
        }

        return $ingredient->defaultRecipe();
    }
}
