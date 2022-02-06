<?php

namespace App\Production\Concerns;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;

trait Getters
{
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getTier(): int
    {
        return $this->recipe->alt_tier ?? $this->product->tier;
    }

    public function getOverrides(): ?Collection
    {
        return $this->overrides;
    }

    public function getChain(): ?Collection
    {
        return $this->chain;
    }

    public function getOverride(Ingredient $ingredient): ?Recipe
    {
        return $this->overrides[$ingredient->name] ?? null;
    }

    public function getIngredients(): ?Collection
    {
        if ( ! $this->ingredients)
            return null;

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
        if ( ! $this->byproducts )
            return null;

        return $this->byproducts->map(function($ingredient) {
            // how many times per minute we need to make the recipe
            $multiplier = $this->qty / $this->recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty;
            return [$ingredient->name => $sub_qty];
        })->collapse();

    }
}
