<?php

namespace App\Production;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\Concerns\ParsesSteps;
use Illuminate\Support\Collection;

class ProductionCalculator
{
    use ParsesSteps;

    // supplied params
    protected Ingredient $product;
    protected ?Recipe $recipe;
    protected $qty;
    protected $overrides;

    // derived params
    protected Step $steps;

    public static function make($product, $qty, $recipe = null, $overrides = []): static
    {
        $production = (new static)
            ->setProduct($product)
            ->setQty($qty)
            ->setRecipe($recipe)
            ->setOverrides($overrides);

        $production->calculate();

        $production->doParse();

        return $production;
    }

    public function setProduct($product): static
    {
        $this->product = i($product);

        return $this;
    }

    public function setRecipe($recipe=null): static
    {
        if ($this->product->isRaw()) {
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

    public function setOverrides($overrides = []): static
    {
        $this->overrides = collect($overrides);

        return $this;
    }

    public function get($key)
    {
        return data_get($this->results,$key);
    }

    public function calculate(): void
    {
        $this->steps = Step::make(
            product: $this->product,
            qty: $this->qty,
            recipe: $this->recipe,
            overrides: $this->overrides
        );
    }

    public function getSteps(): Step
    {
        return $this->steps;
    }

    public function getResults(): array
    {
        return $this->results->toArray();
    }


}
