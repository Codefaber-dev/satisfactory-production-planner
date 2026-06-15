<?php

namespace App\Production;

use App\Favorites\Facades\Favorites;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\Concerns\ParsesSteps;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

    protected ?Collection $used_byproducts;

    protected $imports;

    protected $variant;

    // derived params
    protected Step $steps;

    protected $raw_available;

    public static function make(
        $product, $qty, $recipe = null, $overrides = [], $favorites = null, $imports = [], $variant = 'mk1', $choices = [], $byproducts = []
    ): static {

        // Encode effective favorites in the cache key to prevent cross-user contamination (B13).
        // When null, load recipe IDs from session NOW (outside the closure) so the key differs
        // per user. The closure still receives the original $favorites so ProductionGlobals
        // resolves them in the normal keyed format via getMappedFavorites().
        $favoritesKey = is_null($favorites)
            ? Favorites::all()->pluck('id')->sort()->values()->all()
            : collect($favorites)->map(fn ($r) => is_object($r) ? $r->id : $r)->sort()->values()->all();

        // add request vars to cache key
        $requestVars = request()->all();

        $cacheKey = 'production_calc_'
            .md5(collect(compact('product', 'qty', 'recipe', 'overrides', 'imports', 'variant', 'choices', 'byproducts'))->put('favorites', $favoritesKey)->toJson())
            .md5(collect($requestVars)->toJson());

        return Cache::rememberForever($cacheKey, function () use ($product, $qty, $recipe, $overrides, $favorites, $imports, $variant, $choices, $byproducts) {
            $production = (new static)->setProduct($product)
                ->setQty($qty)
                ->setRecipe($recipe)
                ->setOverrides($overrides)
                ->setFavorites($favorites)
                ->setImports($imports)
                ->setVariant($variant)
                ->setChoices($choices)
                ->setByproducts($byproducts)
                ->setUsedByproducts([]);

            $production->raw_available = ($raw = request('raw')) ? static::parseRaw($raw) : [];

            $production->calculate();

            $production->doParse();

            // if production byproducts can be utilized then calculate again

            if ($production->hasUsableByproducts()) {
                // do it three times for good measure
                $production->recalculateUsingByproducts();
                $production->recalculateUsingByproducts();
                $production->recalculateUsingByproducts();
            }

            return $production;
        });

    }

    public function recalculateUsingByproducts($byproducts = null, $used_byproducts = null)
    {
        $this->setByproducts($byproducts ?? $this->getByproducts());

        $this->setUsedByproducts($used_byproducts);

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
        } else {
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

    public function setUsedByproducts($used_byproducts = []): static
    {
        $this->used_byproducts = collect($used_byproducts);

        return $this;
    }

    public function getUsedByproducts(): ?Collection
    {
        return $this->used_byproducts;
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
                variant: $this->variant,
                used_byproducts: $this->used_byproducts,
                loop_gross: $this->rootLoopGross()
            ),
            recipe: $this->recipe,
        );
    }

    /**
     * V58: when the output product itself sits in an active recipe loop, solve
     * the loop for its members' gross output instead of forcing a substitute
     * recipe. Returns [product => gross qty/min], or [] when not applicable.
     */
    protected function rootLoopGross(): array
    {
        if ($this->product->isRaw() || ! $this->recipe) {
            return [];
        }

        // cheap gate: does the output product participate in any candidate loop?
        $candidates = collect(LoopCatalog::all())->pluck('members')->flatten()->unique();

        if (! $candidates->contains($this->product->name)) {
            return [];
        }

        // detect the *active* loop by running SCC on the selected-recipe subgraph (B41):
        // one effective recipe per candidate product, not the full catalog.
        $selection = $this->loopSelection();
        $selectedRecipes = [];
        $recipeOf = [];

        foreach ($candidates as $member) {
            $recipe = isset($selection[$member]) ? r($selection[$member]) : i($member)->baseRecipe();

            if (! $recipe) {
                continue;
            }

            $recipeOf[$member] = $recipe;
            $selectedRecipes[] = [
                'product' => $member,
                'recipe' => $recipe->description ?? $member,
                'ingredients' => $recipe->ingredients->pluck('name')->all(),
            ];
        }

        $cluster = collect(LoopCatalog::detect($selectedRecipes))
            ->first(fn ($c) => in_array($this->product->name, $c['members'], true));

        if (! $cluster) {
            return [];
        }

        $recipes = [];
        foreach ($cluster['members'] as $member) {
            $recipes[$member] = [
                'base_per_min' => (float) $recipeOf[$member]->base_per_min,
                'inputs' => $recipeOf[$member]->ingredients->mapWithKeys(
                    fn ($i) => [$i->name => (float) $i->pivot->base_qty]
                )->all(),
            ];
        }

        $demand = array_fill_keys($cluster['members'], 0.0);
        $demand[$this->product->name] = (float) $this->qty;

        $runRates = LoopSolver::solve($cluster['members'], $recipes, $demand);

        if ($runRates === null) {
            return [];
        }

        // solver returns run-rates (machine-equivalents); the Step engine wants
        // gross output qty/min = run-rate × base_per_min.
        $gross = [];
        foreach ($runRates as $member => $rate) {
            $gross[$member] = $rate * $recipes[$member]['base_per_min'];
        }

        return $gross;
    }

    /**
     * Effective chosen recipe description per product (choice > favorite > root),
     * used to decide which catalog loops are active.
     */
    protected function loopSelection(): array
    {
        $selection = [];

        foreach (Favorites::getMappedFavorites($this->favorites) as $name => $recipe) {
            $selection[$name] = $recipe->description ?? $name;
        }

        foreach ($this->choices as $name => $recipe) {
            if ($recipe instanceof Recipe) {
                $selection[$name] = $recipe->description ?? $name;
            }
        }

        $selection[$this->product->name] = $this->recipe->description ?? $this->product->name;

        return $selection;
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
            ->map(fn ($tier) => $tier->map(fn ($product) => collect($product->production)->values())->values())
            ->values()
            ->collapse()
            ->collapse()
            ->pluck('total_energy')
            ->map(fn ($details) => collect($details)->values()->all())
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
            ->map(fn ($tier) => $tier->map(fn ($product) => collect($product->production)->values())->values())
            ->values()
            ->collapse()
            ->collapse()
            ->pluck('power_usage')
            ->map(fn ($details) => collect($details)->values()->all())
            ->crossSum()[$variant];
    }

    public function getEnergy($variant = 0): int
    {
        return (int) (60 * $this->getPowerUsage($variant) / $this->qty);
    }

    public static function parseRaw($raw): array
    {
        return collect(explode(',', $raw))
            ->map(function ($pair) {
                [$key,$value] = explode(':', $pair);

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
            ->map(function ($required, $key) {
                if (isset($this->raw_available[$key]) && $available = $this->raw_available[$key]) {
                    return $available / $required;
                }

                return null;
            })
            ->filter()
            ->min() ?? 1;
    }

    public function getByproductsUsed(): Collection
    {
        return $this->results->flatMap(function ($tier) {
            return $tier->map(function ($production, $name) {
                return $production->byproduct_outputs;
            });
        })->filter();
    }

    // public function getRawByproductsUsed()
    // {
    //    return collect($this->getByproducts())
    //
    //        ->filter(function($qty, $ingredient){
    //            return i($ingredient)->isRaw();
    //        })
    //        ->filter(function($qty, $ingredient){
    //            return $this->getRawMaterials()->has($ingredient);
    //        });
    // }
}
