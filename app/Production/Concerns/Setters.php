<?php

namespace App\Production\Concerns;

use App\Models\Ingredient;
use App\Models\Recipe;

trait Setters
{
    protected Ingredient $product;
    protected ?Recipe $recipe = null;
    protected $qty;
    protected $overrides;

    // derived parameters
    protected $name;
    protected $parent;
    protected $chain;
    protected $ingredients;
    protected $byproducts;

    public function setProduct($product): static
    {
        $this->product = i($product);
        $this->name = $this->product->name;

        return $this;
    }

    public function setRecipe($recipe=null): static
    {
        if ( $this->product->isRaw() ) {
            return $this;
        }

        if ($recipe) {
            $this->recipe = r($recipe);
        } else {
            $this->recipe = $this->product->baseRecipe();
        }

        return $this;
    }

    public function setQty($qty=100): static
    {
        $this->qty = $qty;

        return $this;
    }

    public function setOverrides($overrides): static
    {
        $this->overrides = $overrides;

        return $this;
    }

    public function setParent($parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function setChain($chain=[]): static
    {
        $this->chain = collect($chain);
        $this->chain->push($this->name);

        return $this;
    }
}
