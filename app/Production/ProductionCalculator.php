<?php

namespace App\Production;

use App\Favorites\Facades\Favorites;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\Concerns\ParsesSteps;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Types\Static_;

class ProductionCalculator
{
    use ParsesSteps;

    // supplied params
    protected Ingredient $product;

    protected ?Recipe $recipe = null;

    protected $qty;

    protected $overrides;

    protected ?Collection $favorites;

    protected ?Collection $choices;

    protected ?Collection $byproducts;

    protected $imports;

    protected $variant;

    // derived params
    protected Step $steps;

    protected $raw_available;

    public static function make(
        $product, $qty, $recipe = null, $overrides = [], $favorites = null, $imports = [], $variant = "mk1", $choices = [], $byproducts = []
    ): static {
        $production = (new static)->setProduct($product)
            ->setQty($qty)
            ->setRecipe($recipe)
            ->setOverrides($overrides)
            ->setFavorites($favorites)
            ->setImports($imports)
            ->setVariant($variant)
            ->setChoices($choices)
            ->setByproducts($byproducts);

        $production->raw_available = ($raw = request('raw')) ? static::parseRaw($raw) : [];

        $production->calculate();

        $production->doParse();

        // if production byproducts can be utilized then calculate again

        if ($production->hasUsableByproducts())
        {
            // do it three times for good measure
            $production->recalculateUsingByproducts();
            $production->recalculateUsingByproducts();
            $production->recalculateUsingByproducts();
        }

        return $production;
    }

    public function recalculateUsingByproducts($byproducts = null)
    {
        $this->setByproducts($byproducts ?? $this->getByproducts());

        $this->calculate();

        $this->doParse();
    }

    public function setProduct($product): static
    {
        $this->product = i($product);

        return $this;
    }

    public function setRecipe($recipe = null): static
    {
        if ($this->product->isRaw()) {
            return $this;
        }

        if ($recipe) {
            $this->recipe = r($recipe);
        }
        else {
            $this->recipe = $this->product->baseRecipe();
        }

        if (! $this->recipe) {
            throw new \Exception("No base recipe found for {$this->product->name}");
        }

        return $this;
    }

    public function setQty($qty = 100): static
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

    public function setByproducts($byproducts = []): static
    {
        $this->byproducts = collect($byproducts);

        return $this;
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function get($key)
    {
        return data_get($this->results, $key);
    }

    public function calculate(): void
    {
        $this->steps = Step::make(
            product: $this->product,
            qty: $this->qty,
            globals: ProductionGlobals::make(
                choices: $this->choices,
                overrides: $this->overrides,
                favorites: $this->favorites,
                imports: $this->imports,
                byproducts: $this->byproducts,
                variant: $this->variant
            ),
            recipe: $this->recipe,
        );
    }

    public function getSteps(): Step
    {
        return $this->steps;
    }

    public function getResults(): Collection
    {
        return $this->results;
    }

    public function getRawResults(): Collection
    {
        return $this->raw_results;
    }

    public function getSlimResults()
    {
        return $this->slim_results->toArray();
    }

    public function setFavorites(Collection|array|null $favorites): static
    {
        $this->favorites = collect($favorites);

        return $this;
    }

    public function setChoices(Collection|array|null $choices): static
    {
        $this->choices = collect($choices);

        return $this;
    }

    public function setVariant($variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function getTotalEnergy($variant = 0): float
    {
        /**
         * variants
         * 0 mk1
         * 1 mk2
         * 2 mk3
         * 3 mk4
         */

        return $this->getResults()
            ->skip(1)
            ->map(fn($tier) => $tier->map(fn($product) => collect($product->production)->values())->values())
            ->values()
            ->collapse()
            ->collapse()
            ->pluck('total_energy')
            ->map(fn($details) => collect($details)->values()->all())
            ->crossSum()[$variant];
    }

    public function getPowerUsage($variant = 0): float
    {
        /**
         * variants
         * 0 mk1
         * 1 mk2
         * 2 mk3
         * 3 mk4
         */

        return $this->getResults()
            ->skip(1)
            ->map(fn($tier) => $tier->map(fn($product) => collect($product->production)->values())->values())
            ->values()
            ->collapse()
            ->collapse()
            ->pluck('power_usage')
            ->map(fn($details) => collect($details)->values()->all())
            ->crossSum()[$variant];
    }

    public function getEnergy($variant = 0): int
    {
        return (int) ( 60 * $this->getPowerUsage($variant) / $this->qty );
    }

    public static function parseRaw($raw): array
    {
        return collect(explode(",",$raw))
            ->map(function($pair) {
                [$key,$value] = explode(":",$pair);
                return [$key => (int) $value];
            })
            ->collapse()
            ->all();
    }

    public function getAdjustedQty()
    {
        return floor(10000 * $this->qty * $this->ratioOfAvailableRawMaterials()) / 10000;
    }

    protected function ratioOfAvailableRawMaterials(): float
    {
        return $this->getRawMaterials()
            ->map(function($required, $key) {
               if (isset($this->raw_available[$key]) && $available = $this->raw_available[$key]) {
                   return $available/$required;
               }
               return null;
            })
            ->filter()
            ->min() ?? 1;
    }

    public function getByproductsUsed(): Collection
    {
        return $this->results->flatMap(function($tier) {
            return $tier->map(function($production, $name) {
               return $production->byproduct_outputs;
            });
        })->filter();
    }

    //public function getRawByproductsUsed()
    //{
    //    return collect($this->getByproducts())
    //
    //        ->filter(function($qty, $ingredient){
    //            return i($ingredient)->isRaw();
    //        })
    //        ->filter(function($qty, $ingredient){
    //            return $this->getRawMaterials()->has($ingredient);
    //        });
    //}
}
