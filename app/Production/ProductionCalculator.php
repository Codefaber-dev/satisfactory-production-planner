<?php

namespace App\Production;

use App\Favorites\Facades\Favorites;
use App\Enums\Building as BuildingEnum;
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
        // V61/V63: fold raw convert/unpackage source modes into the calc — the chosen
        // recipe becomes a normal recipe pick, and an unpackage raw's Packaged input
        // defaults to import. Idempotent (safe across recalc passes).
        $this->mergeRawSources();

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

        // Raw products sourced via convert/unpackage are boundary in the static
        // catalog (all raws are cut there), so add them explicitly — a raw↔raw convert
        // cycle (e.g. Iron Ore ⇄ Limestone ⇄ Sulfur) only becomes visible here (V61).
        $candidates = collect(LoopCatalog::all())->pluck('members')->flatten()
            ->merge($this->rawRecipeProducts())
            ->unique();

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
        //     X from its packaged form, so NEVER swap that member; re-source the PACKAGED
        //     member instead (and if it has no loop-free recipe, fall through to auto-import
        //     it). Hard-excluded, not merely penalised: when the packaged member lacks an
        //     alternate (e.g. Packaged Turbofuel), the unpackage-self member is the only one
        //     with a loop-free recipe and a soft penalty would still swap the user's pick.
        //  2. prefer non-user-chosen members;
        //  3. then the most resource-efficient swap.
        $candidates = collect($members)
            ->reject(fn ($m) => $this->unpackagesItself($m, $recipeOf))
            ->map(fn ($m) => ['product' => $m, 'recipe' => $this->loopFreeRecipe($m, $members)])
            ->filter(fn ($c) => $c['recipe'] !== null)
            ->sortBy(fn ($c) => ($userPicked->contains($c['product']) ? 1e9 : 0)
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

    /**
     * V61/V63: fold raw convert/unpackage source modes into the existing recipe-pick
     * + import machinery. The selected recipe is added as a choice (so it flows through
     * loop detection, getChoice, and the Step walk), and an unpackage raw's Packaged
     * input ingredient(s) default to import.
     */
    protected function mergeRawSources(): void
    {
        $choices = [];
        $imports = [];

        foreach ($this->rawSourceConfigs() as $name => $config) {
            $ingredient = i($name);

            // V79: the recipe lives in `choices` (the main RecipePicker), not in
            // raw_sources. Use the user's choice when present; otherwise default it
            // (first convert recipe / the Unpackage recipe) so the raw becomes
            // recipe-bearing and the step renders the picker.
            $recipe = $this->choices->get($name)
                ?? $this->defaultRawRecipe($ingredient, $config['mode'] ?? 'import');

            if (! $recipe) {
                continue;
            }

            $choices[$name] = $recipe;

            // V63(a): the Packaged input of an unpackage raw is imported by default
            // (the loop is broken on the packaged side — "import the packaged contents").
            if (($config['mode'] ?? null) === 'unpackage') {
                foreach ($recipe->ingredients as $packagedInput) {
                    $imports[] = $packagedInput->name;
                }
            }
        }

        $this->choices = $this->choices->merge($choices);
        $this->imports = collect($this->imports)->merge($imports)->unique()->values()->all();
    }

    /**
     * Default recipe for a convert/unpackage raw with no explicit choice (V79):
     * the Unpackage recipe (by name) for unpackage, else the first Converter
     * ore-conversion recipe producing the raw.
     */
    protected function defaultRawRecipe(Ingredient $ingredient, string $mode): ?Recipe
    {
        if ($mode === 'unpackage') {
            return $ingredient->recipes
                ->first(fn ($r) => str_starts_with($r->description ?? '', 'Unpackage'));
        }

        // convert = a Converter ore-conversion recipe (raw from another raw +
        // Reanimated SAM, T68) — not just any alt that happens to produce the raw.
        return $ingredient->recipes
            ->first(fn ($r) => optional($r->building)->name === BuildingEnum::CONVERTER->value);
    }

    /**
     * Raw source-mode configs in a recipe-bearing mode (convert + unpackage), keyed
     * by raw. V79: gated on mode only — the recipe no longer lives here.
     *
     * @return \Illuminate\Support\Collection<string, array{mode: string}>
     */
    protected function rawSourceConfigs(): Collection
    {
        return collect(request('raw_sources', []))
            ->filter(fn ($config) => is_array($config)
                && in_array($config['mode'] ?? 'import', ['convert', 'unpackage'], true));
    }

    /** Raw product names sourced via a convert/unpackage recipe (V61/V63 loop candidates). */
    protected function rawRecipeProducts(): Collection
    {
        return $this->rawSourceConfigs()->keys();
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
