<?php

namespace App\Production\Concerns;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\BuildingDetails;
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
        if ($this->isByproduct()) {
            $this->recycleByproducts();

            if ($this->getQty() === 0) {
                $this->all_byproduct = true;

                return;
            }
        }

        // determine if the current ingredient is raw
        if ($this->isRaw()) {
            return;
        }

        // determine if the current ingredient is imported
        if ($this->isImported()) {
            $this->imported = true;

            return;
        }

        // $this->setOverview(BuildingOverview::make(
        //    recipe: $this->getRecipe(),
        //    qty: $this->getQty(),
        //    belt_speed: $this->getBeltSpeed(),
        //    variant: $this->getVariant()
        // ));

        $this->ingredients = $this->getRecipe()->ingredients;

        $this->byproducts = $this->getRecipe()->byproducts;

        $somersloop_slots = (int) (request('somersloops', [])[$this->getProductKey()] ?? 0);
        $building_name = $this->recipe->building->name;
        $max_slots = BuildingDetails::SLOTS[$building_name] ?? 0;
        $amplifier = $max_slots > 0 ? (1 + $somersloop_slots / $max_slots) : 1.0;

        $this->children = $this->ingredients->map(function ($ingredient) use ($amplifier) {
            $name = $ingredient->name;

            // how many times per minute we need to make the recipe (somersloops amplify output)
            $multiplier = $this->qty / ($this->recipe->base_per_min * $amplifier);

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty * $this->globals->getCostMultiplier();

            // V58: solved-loop members are handled by the linear solver.
            if ($this->globals->isLoopMember($name)) {
                // accumulate external demand — consumption by a step NOT in the same
                // loop (the solver's input). Captured at the edge, so it holds even
                // when the member is cut below.
                if (! $this->globals->sameLoop($this->name, $name)) {
                    $this->globals->addExternalDemand($name, $sub_qty);
                }

                // cut the back-edge (the solver accounts for internal consumption)
                // and emit each member once at gross (later encounters cut).
                if ($this->chain->contains($name) || $this->globals->wasEmitted($name)) {
                    return null;
                }
            }

            // return a new production step
            return static::make(
                product: $ingredient,
                qty: $sub_qty,
                globals: $this->globals,
                recipe: $this->getIntermediateRecipe($ingredient),
                parent: $this->name,
                chain: $this->chain
            );
        })->filter();
    }

    protected function check(): bool
    {
        // a cycle exists if this product already appears among its own ancestors
        return $this->getChain()->filter(fn ($val) => $val === $this->getName())->count() === 1;
    }

    /**
     * Fallback for cycles the linear solver does not handle (genuinely unsolvable /
     * degenerate loops, e.g. the 1:1 Fuel⇄Packaged Fuel no-op, B43): swap to a
     * recipe whose ingredients don't re-enter the chain.
     */
    protected function useCompatibleRecipe(): void
    {
        $recipe = $this->getProduct()->recipes->filter(function (Recipe $recipe) {
            return $recipe->ingredients
                ->map(fn ($ingredient) => $ingredient->name)
                ->intersect($this->getChain())
                ->isEmpty();
        })->sortBy('resource')->first();

        $description = $recipe->description ?? 'default';

        $this->setWarning("Using {$description} recipe for {$this->getName()} to avoid circular dependency.");

        $this->setRecipe($recipe);
    }

    protected function recycleByproducts(): void
    {
        // how much byproduct should be used
        $use_byproduct_qty = min($this->getQty(), $this->availableByproduct());
        $this->useByproduct($this->getName(), $use_byproduct_qty);

        // set the new qty, but not negative
        $this->setQty(max(0, $this->getQty() - $use_byproduct_qty));
    }
}
