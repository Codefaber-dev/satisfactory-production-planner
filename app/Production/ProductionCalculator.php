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

    // V58: cap on loop steady-state iterations (productive loops converge in a few)
    protected const MAX_LOOP_ITERATIONS = 15;

    // V58: warnings for loops that could not be solved (fell back to propagated demand)
    protected array $loop_warnings = [];

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
        $detected = $this->detectLoops();
        $loops = $detected['solvable'];
        $loopOf = $this->loopOfMap($loops);
        $injectedOverrides = $detected['overrides']; // V69: product => Recipe (source injection)
        $injectedImports = $detected['imports'];     // V69: products auto-imported to break a loop

        if (empty($loopOf)) {
            // acyclic fast path (+ any injected sources) — no solver iteration needed
            $this->steps = Step::make(
                product: $this->product,
                qty: $this->qty,
                globals: $this->makeGlobals([], [], $injectedOverrides, $injectedImports),
                recipe: $this->recipe,
            );

            return;
        }

        // V58: iterate to steady state. Each pass measures external demand on loop
        // members (incl. nested-loop demand once an upstream loop's gross is known),
        // re-solves, and rebuilds — converging because productive loops are
        // contractive (B42: caterium's Plastic⇄Rubber feeds Fuel⇄Packaged Fuel).
        $gross = [];
        for ($iter = 0; $iter < self::MAX_LOOP_ITERATIONS; $iter++) {
            $globals = $this->makeGlobals($loopOf, $gross, $injectedOverrides, $injectedImports);

            $this->steps = Step::make(
                product: $this->product,
                qty: $this->qty,
                globals: $globals,
                recipe: $this->recipe,
            );

            $external = $globals->getExternalDemand()->all();
            if (isset($loopOf[$this->product->name])) {
                // the requested output is itself external demand on the root member
                $external[$this->product->name] = ($external[$this->product->name] ?? 0) + (float) $this->qty;
            }

            $next = $this->solveLoops($loops, $external);

            if ($this->grossConverged($gross, $next)) {
                return; // current tree was built with $gross ≈ $next
            }

            $gross = $next;
        }
    }

    /**
     * Detect this plan's loops (SCCs of the selected-recipe subgraph, B41; byproduct-
     * supplied products as boundary, B43) and partition them:
     *  - solvable → handed to the linear solver (V58)
     *  - unsolvable & sourceless → a source is *injected* (V69), replacing the old
     *    hardcoded `overrideFavoritesIfNecessary`: swap the best non-user-chosen
     *    member to its most-efficient loop-free alternate recipe, else auto-import.
     *    A member already imported breaks the loop on its own → skip (B44).
     *
     * @return array{solvable: array, overrides: array<string, Recipe>, imports: array<int, string>}
     */
    protected function detectLoops(): array
    {
        $empty = ['solvable' => [], 'overrides' => [], 'imports' => []];

        if ($this->product->isRaw() || ! $this->recipe) {
            return $empty;
        }

        $candidates = collect(LoopCatalog::all())->pluck('members')->flatten()->unique();

        if ($candidates->isEmpty()) {
            return $empty;
        }

        $selection = $this->loopSelection();
        $selectedRecipes = [];
        $recipeOf = [];
        $byproductSupplied = [];

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

            foreach ($recipe->byproducts as $byproduct) {
                $byproductSupplied[$byproduct->name] = true;
            }
        }

        $clusters = LoopCatalog::detect($selectedRecipes, array_keys($byproductSupplied));

        $solvable = [];
        $overrides = [];
        $imports = [];

        foreach ($clusters as $cluster) {
            $recipes = [];
            foreach ($cluster['members'] as $member) {
                $recipes[$member] = [
                    'base_per_min' => (float) $recipeOf[$member]->base_per_min,
                    'inputs' => $recipeOf[$member]->ingredients->mapWithKeys(
                        fn ($i) => [$i->name => (float) $i->pivot->base_qty]
                    )->all(),
                ];
            }

            $probe = array_fill_keys($cluster['members'], 1.0);
            if (LoopSolver::solve($cluster['members'], $recipes, $probe) !== null) {
                $solvable[] = ['members' => $cluster['members'], 'recipes' => $recipes];

                continue;
            }

            // unsolvable: an imported member already breaks it (B44) → no injection
            if (collect($cluster['members'])->contains(fn ($m) => collect($this->imports)->contains($m))) {
                continue;
            }

            // V69: inject a source
            $injection = $this->injectSource($cluster['members'], $recipeOf);
            if ($injection['recipe']) {
                $overrides[$injection['product']] = $injection['recipe'];
                $recipeName = $injection['recipe']->description ?? $injection['product'].' (base)';
                $this->loop_warnings[] = "Auto-sourced {$injection['product']} via {$recipeName} to resolve loop: ".implode(' ⇄ ', $cluster['members']);
            } elseif ($injection['import']) {
                $imports[] = $injection['import'];
                $this->loop_warnings[] = "Auto-imported {$injection['import']} to resolve loop: ".implode(' ⇄ ', $cluster['members']);
            }
        }

        return compact('solvable', 'overrides', 'imports');
    }

    /**
     * V69: choose how to inject a source into a sourceless loop. Prefer swapping a
     * non-user-chosen member to its most-efficient loop-free alternate recipe (never
     * silently swap an explicit user pick); else auto-import a member.
     *
     * @return array{product: ?string, recipe: ?Recipe, import: ?string}
     */
    protected function injectSource(array $members, array $recipeOf): array
    {
        $userPicked = $this->userPickedProducts();

        // Prefer a self-contained recipe-swap (keeps producing) over auto-import.
        // Among members with a loop-free alternate, pick the least-disruptive + cheapest:
        //  1. preserve an unpackage choice — choosing "Unpackage X" deliberately sources
        //     X from its packaged form, so re-source the PACKAGED member instead;
        //  2. prefer non-user-chosen members;
        //  3. then the most resource-efficient swap.
        $candidates = collect($members)
            ->map(fn ($m) => ['product' => $m, 'recipe' => $this->loopFreeRecipe($m, $members)])
            ->filter(fn ($c) => $c['recipe'] !== null)
            ->sortBy(fn ($c) => ($this->unpackagesItself($c['product'], $recipeOf) ? 1e12 : 0)
                + ($userPicked->contains($c['product']) ? 1e9 : 0)
                + (float) $c['recipe']->resource)
            ->values();

        if ($candidates->isNotEmpty()) {
            $best = $candidates->first();

            return ['product' => $best['product'], 'recipe' => $best['recipe'], 'import' => null];
        }

        // no loop-free recipe for any member → auto-import (prefer a non-user-chosen one)
        $importable = collect($members)->sortBy(fn ($m) => $userPicked->contains($m) ? 1 : 0)->first();

        return ['product' => null, 'recipe' => null, 'import' => $importable];
    }

    /**
     * Most resource-efficient recipe for $member whose ingredients don't re-enter the
     * loop. Supersedes useCompatibleRecipe's arbitrary ->first().
     */
    protected function loopFreeRecipe(string $member, array $loopMembers): ?Recipe
    {
        return i($member)->recipes
            ->filter(fn ($recipe) => $recipe->ingredients->pluck('name')->intersect($loopMembers)->isEmpty())
            ->sortBy('resource')
            ->first();
    }

    /**
     * Is $member sourced by unpackaging its own packaged form (recipe consumes
     * "Packaged <member>")? Such a pick deliberately externalizes the packaged
     * form, so the loop should be broken on the packaged side, not here.
     */
    protected function unpackagesItself(string $member, array $recipeOf): bool
    {
        $recipe = $recipeOf[$member] ?? null;

        return $recipe !== null
            && $recipe->ingredients->contains(fn ($i) => $i->name === "Packaged {$member}");
    }

    protected function userPickedProducts(): Collection
    {
        return collect($this->choices->keys())
            ->merge(Favorites::getMappedFavorites($this->favorites)->keys())
            ->unique();
    }

    protected function loopOfMap(array $loops): array
    {
        $map = [];
        foreach ($loops as $id => $loop) {
            foreach ($loop['members'] as $member) {
                $map[$member] = $id;
            }
        }

        return $map;
    }

    /**
     * Solve each loop for member gross output (run-rate × base_per_min) given the
     * measured external demand. Unsolvable loops (singular / non-productive) are
     * skipped with a warning — those members fall back to propagated demand.
     */
    protected function solveLoops(array $loops, array $external): array
    {
        $gross = [];

        foreach ($loops as $loop) {
            $demand = [];
            foreach ($loop['members'] as $member) {
                $demand[$member] = (float) ($external[$member] ?? 0);
            }

            $runRates = LoopSolver::solve($loop['members'], $loop['recipes'], $demand);

            if ($runRates === null) {
                $this->loop_warnings[] = 'Unsolvable loop, using fallback: '.implode(' / ', $loop['members']);

                continue;
            }

            foreach ($runRates as $member => $rate) {
                $gross[$member] = $rate * $loop['recipes'][$member]['base_per_min'];
            }
        }

        return $gross;
    }

    protected function grossConverged(array $previous, array $next): bool
    {
        if (count($previous) !== count($next)) {
            return false;
        }

        foreach ($next as $member => $value) {
            if (! isset($previous[$member]) || abs($previous[$member] - $value) > 1e-6) {
                return false;
            }
        }

        return true;
    }

    protected function makeGlobals(array $loopOf, array $gross, array $extraOverrides = [], array $extraImports = []): ProductionGlobals
    {
        return ProductionGlobals::make(
            choices: $this->choices,
            overrides: collect($this->overrides)->merge($extraOverrides),
            favorites: $this->favorites,
            imports: collect($this->imports)->merge($extraImports)->values()->all(),
            byproducts: $this->byproducts,
            variant: $this->variant,
            used_byproducts: $this->used_byproducts,
            loop_of: $loopOf,
            loop_gross: $gross,
        );
    }

    public function getLoopWarnings(): array
    {
        return $this->loop_warnings;
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
