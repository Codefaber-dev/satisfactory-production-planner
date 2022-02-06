<?php

namespace App\ProductionBak;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;

class ProductionStep
{
    /**
     * @var \App\Models\Ingredient
     */
    protected $product;

    /**
     * @var \App\Models\Recipe
     */
    protected $recipe;

    protected $qty;

    protected $imports;

    protected $name;

    /**
     * @var array
     */
    protected $parent;

    protected $chain;

    protected $ingredients;

    protected $byproducts;

    protected $import = false;

    protected $warning;

    protected $belt_speed;

    protected $overview;

    protected $variant;

    protected $key;

    protected $overrides;

    public $children;

    public function __construct(Ingredient $product, $qty, ?Recipe $recipe = null, $imports = [], $parent = null, $chain = [], $variant = "mk1", $key = "", $overrides = [])
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->qty = $qty;
        $this->imports = collect($imports);
        $this->key = $key;

        $this->chain = collect($chain);
        $this->chain->push($this->name);
        $this->parent = $parent;
        $this->belt_speed = request('belt_speed',780);
        $this->variant = $variant;
        $this->overrides = collect($overrides);

        // ignore if a raw product
        if ($this->product->isRaw()) {
            return;
        }

        // ignore if an imported product
        if ($this->imports->contains($this->name)) {
            $this->import = true;
            return;
        }

        // manufactured product
        $this->setRecipeAndCalculate($recipe);
    }

    public static function make(Ingredient $product, $qty, ?Recipe $recipe = null, $imports = [], $parent = null, $chain = [], $variant = "mk1", $key = "", $overrides = []): static
    {
        return new static(
            product: $product,
            qty: $qty,
            recipe: $recipe,
            imports: $imports,
            parent: $parent,
            chain: $chain,
            variant: $variant,
            key: $key,
            overrides: $overrides
        );
    }

    protected function calculate(): void
    {
        $this->overview = BuildingOverview::make($this->recipe, $this->qty, $this->belt_speed);

        $this->children = $this->ingredients->map(function($ingredient){
            // how many times per minute we need to make the recipe
            $multiplier = $this->qty / $this->recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty;

            // return a new production step
            return static::make(
                product: $ingredient,
                qty: $sub_qty,
                recipe: $this->getRecipe($ingredient),
                imports: $this->imports,
                parent: $this->name,
                chain: $this->chain,
                variant: $this->variant,
                key: $this->key,
                overrides: $this->overrides
            );
        });
    }

    protected function getRecipe(Ingredient $ingredient): ?Recipe
    {
        if ($ingredient->isRaw()) {
            return null;
        }

        // see if there is an override for this parent-child combo
        if ( isset($this->overrides[$this->name][$this->parent]) ) {

            return $this->overrides[$this->name][$this->parent];
        }

        return $ingredient->defaultRecipe();
    }

    protected function getMappedIngredients()
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

    protected function getMappedByproducts()
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

    public function toArray()
    {
        return collect([
            "name" => $this->name,
            "description" => $this->recipe->description ?? "default",
            "ingredients" => $this->getMappedIngredients(),
            "byproducts" => $this->getMappedByproducts(),
            "tier" => $this->product->tier,
            "qty" => $this->qty,
            "base_per_min" => optional($this->recipe)->base_per_min,
            "import" => $this->import,
            "imports" => $this->imports,
            "outputs" => [
                $this->parent => $this->qty
            ],
            "parent" => $this->parent,
            //"product" => $this->product,
            "recipe" => $this->recipe,
            "warning" => $this->warning,
            "building_overview" => $this->overview,
            "chain" => $this->chain,
            "overrides" => $this->getCachedOverrides()
        ]);
    }

    /**
     * @param \App\Models\Recipe|null $recipe
     * @return void
     * @throws \ErrorException
     */
    protected function setRecipeAndCalculate(?Recipe $recipe): void
    {
        $this->recipe = $recipe;
        $this->ingredients = $recipe->ingredients;
        $this->byproducts = $recipe->byproducts;

        if (! $this->validateRecipe()) {
            $offendingDependencies = $this->ingredients
                ->map(fn($ingredient) => $ingredient->name)
                ->intersect($this->chain)
                ->join(',');
            $this->useCompatibleRecipe();
            $this->cacheOverride($this->name, $this->parent);
            $description = $this->recipe->description ? "alternate recipe: '{$this->recipe->description}'" : "default recipe";
            $this->warning = [
                "problem" => "Circular Dependency",
                "chain" => $this->chain->join('->') . '->' . $offendingDependencies,
                "offenders" => $offendingDependencies,
                "resolution" => "Use {$description} for {$this->name}"
            ];
        }

        $this->calculate();
    }

    /**
     * Ensure none of the previous products in the chain
     *  are ingredients for the current product
     *
     * @return bool
     */
    protected function validateRecipe(): bool
    {
        //if( $this->isCached($this->name, $this->parent)) {
        //    dd($this->toArray());
        //    return true;
        //}


        // otherwise make sure no ingredients are in the dependency chain
        return $this->ingredients
            ->map(fn($ingredient) => $ingredient->name)
            ->intersect($this->chain)
            ->isEmpty();
    }

    /**
     * Switch the recipe to a compatible
     *
     * @return void
     * @throws \ErrorException
     */
    protected function useCompatibleRecipe(): void
    {
        $this->recipe = $this->product->recipes
            ->filter(function($recipe){
                // find recipes without a circular dependency
                return $recipe->ingredients
                    ->map(fn($ingredient) => $ingredient->name)
                    ->intersect($this->chain)
                    ->isEmpty();
            })->first();
        $this->ingredients = $this->recipe->ingredients;
        $this->byproducts = $this->recipe->byproducts;
    }

    protected function cacheOverride($product, $dep)
    {
        $cached = $this->getCachedOverrides();
        Cache::forget($this->key);

        return Cache::remember($this->key, now()->addMinutes(15), function() use ($product, $dep, $cached) {
            $cached["recipes:{$product}->{$dep}"] = $this->product->baseRecipe();
            return $cached;
        });
    }

    protected function isCached($product, $dep)
    {
        return isset($this->getCachedOverrides()["recipes:{$dep}->{$product}"]);

    }

    public function getCachedOverrides()
    {
        return Cache::get($this->key,collect());
    }
}
