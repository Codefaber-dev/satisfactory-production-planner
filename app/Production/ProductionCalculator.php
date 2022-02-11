<?php

namespace App\Production;

use App\Favorites\Facades\Favorites;
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
    protected Collection $favorites;
    protected $imports;
    protected $variant;

    // derived params
    protected Step $steps;

    public static function make($product, $qty, $recipe = null, $overrides = [], $favorites = null, $imports = [], $variant = "mk1"): static
    {
        $production = (new static)
            ->setProduct($product)
            ->setQty($qty)
            ->setRecipe($recipe)
            ->setOverrides($overrides)
            ->setFavorites($favorites)
            ->setImports($imports)
            ->setVariant($variant);

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

    public function setImports($imports): static
    {
        $this->imports = $imports;

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
            globals: ProductionGlobals::make(
                overrides: $this->overrides,
                favorites: $this->favorites,
                imports: $this->imports,
                variant: $this->variant
            )
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

    public function getSlimResults()
    {
        return $this->slim_results->toArray();
    }

    public function setFavorites($favorites): static
    {
        $this->favorites = $favorites ?
            collect($favorites) :
            Favorites::all();

        return $this;
    }

    public function setVariant($variant): static
    {
        $this->variant = $variant;

        return $this;
    }
}
