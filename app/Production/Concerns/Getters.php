<?php

namespace App\Production\Concerns;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\BuildingDetails;
use App\Production\BuildingOverview;
use Exception;
use Illuminate\Support\Collection;
use function method_exists;

trait Getters
{
    public function __call($name, $arguments)
    {
        if (method_exists($this,$name)) {
            return $this->$name(...$arguments);
        }

        if (method_exists($this->globals,$name)) {
           return $this->globals->$name(...$arguments);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProduct(): Ingredient
    {
        return $this->product;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function getDescription(): string
    {
        return $this->recipe->description ?? 'default';
    }

    public function getQty(): float
    {
        return $this->qty;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    public function getTier(): int
    {
        return $this->recipe->alt_tier ?? $this->product->tier;
    }

    public function getChain(): ?Collection
    {
        return $this->chain;
    }

    public function getJoinedChain(): string
    {
        return $this->getChain()->join('->');
    }

    public function getChains(): ?Collection
    {
        if ( $children = $this->getChildren() ) {
            return collect([$this->getJoinedChain(), $children->map(fn($child) => $child->getChains())])->filter()->flatten();
        }

        return collect($this->getJoinedChain());
    }

    public function getIngredients(): ?Collection
    {
        if (! $this->ingredients) {
            return null;
        }

        return $this->ingredients->map(function($ingredient) {
            // how many times per minute we need to make the recipe
            $multiplier = $this->qty / $this->recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty;
            return [$ingredient->name => $sub_qty];
        })->collapse();
    }

    public function getByproducts(): ?Collection
    {
        if (! $this->byproducts) {
            return null;
        }

        return $this->byproducts->map(function($ingredient) {
            // how many times per minute we need to make the recipe
            $multiplier = $this->qty / $this->recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty;
            return [$ingredient->name => $sub_qty];
        })->collapse();
    }

    /**
     * Get the recipe for an intermediate product
     *
     * @throws \ErrorException If the ingredient does not have a base recipe
     */
    public function getIntermediateRecipe(Ingredient $ingredient): ?Recipe
    {
        switch(true) {
            // no recipe if the ingredient is raw
            case $ingredient->isRaw() :
                return null;

            // if it's the final product
            case $ingredient->is($this->getProduct());
                return $this->getRecipe();

            // use an override if there is one
            case $recipe = $this->getOverride($ingredient) :
            // use a favorite recipe if there is one
            case $recipe = $this->getFavorite($ingredient) :
                return $recipe;

            // use the base recipe
            default:
                return $ingredient->baseRecipe();
        }

    }

    public function getWarning(): ?string
    {
        return $this->warning;
    }

    public function getWarnings(): ?array
    {
        if ( $children = $this->getChildren() ) {
            return collect([$this->getWarning(), $children->map(fn($child) => $child->getWarnings())])->filter()->flatten()->all();
        }

        return collect($this->getWarning())->all();
    }

    public function getOverview(): ?BuildingOverview
    {
        return $this->overview;
    }

    public function getBuildingDetails(): BuildingDetails
    {
        return $this->getOverview()->details;
    }
}
