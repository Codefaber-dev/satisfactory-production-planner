<?php

namespace App\Production\Concerns;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\BuildingOverview;
use App\Production\Step;

trait CalculatesSteps
{
    protected $ingredients;

    protected $children;

    protected $use_byproduct;

    protected function calculate(): void
    {
        // determine if the current ingredient is a byproduct of another step
        if($this->isByproduct($this->getName())) {
            $use_byproduct_qty = min($this->getQty(), $this->getByproduct($this->getName()));
            $new_qty = max(0, $this->getQty() - $use_byproduct_qty);

            $this->setQty($new_qty);

            $this->use_byproduct = $use_byproduct_qty;

            if($new_qty === 0) {
                $this->all_byproduct = true;
                return;
            }
        }
        
        if ($this->getProduct()->isRaw()) {
            return;
        }

        // determine if the current ingredient is imported
        if($this->isImported($this->getName())) {
            $this->imported = true;
            return;
        }



        //$this->setOverview(BuildingOverview::make(
        //    recipe: $this->getRecipe(),
        //    qty: $this->getQty(),
        //    belt_speed: $this->getBeltSpeed(),
        //    variant: $this->getVariant()
        //));

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
                globals: $this->globals,
                recipe: $this->getIntermediateRecipe($ingredient),
                parent: $this->name,
                chain: $this->chain
            );
        });
    }

    protected function check(): bool
    {
        // check the dependencies
        return $this->getChain()->filter(fn($val) => $val === $this->getName())->count() === 1;
    }

    protected function useCompatibleRecipe(): void
    {
        $recipe = $this->getProduct()->recipes->filter(function(Recipe $recipe) {
            return $recipe->ingredients
                ->map(fn($ingredient)=>$ingredient->name)
                ->intersect($this->getChain())
                ->isEmpty();
        })->first();

        $description = $recipe->description ?? "default";

        $this->setWarning("Using {$description} recipe for {$this->getName()} to avoid circular dependency.");

        $this->setRecipe($recipe);
    }
}
