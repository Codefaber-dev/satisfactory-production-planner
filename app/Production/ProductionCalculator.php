<?php

namespace App\Production;

use App\Enums\Building as BuildingEnum;
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

    // V74: cap on byproduct recalculation passes (was a fixed 3× "for good measure")
    protected const MAX_BYPRODUCT_PASSES = 3;

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

    // V137: root-step output destination override — null for a requested output
    // ('final'); the AWESOME Sink name for an injected recycling calc.
    protected $dest = null;

    // derived params
    protected Step $steps;

    protected $raw_available;

    public static function make(
        $product, $qty, $recipe = null, $overrides = [], $favorites = null, $imports = [], $variant = 'mk1', $choices = [], $byproducts = [], $dest = null
    ): static {

        // V70/B46: make() returns the LIVE calculator object (callers use its
        // methods) but no longer caches it. Forever-caching this object graph
        // (full Step tree + ProductionGlobals collections) produced huge
        // serialized blobs whose unserialize exhausted the 128 MB PHP memory
        // limit. The result cache now lives at the plain-array payload layer
        // (cachedPayload()), keyed by whitelisted inputs under a finite TTL.
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

        // V137: an injected recycling calc's root step reports its output
        // destination (the AWESOME Sink) instead of 'final' — it is not a
        // requested output, and the frontend links the dest to the sink step.
        $production->dest = $dest;

        $production->raw_available = ($raw = request('raw')) ? static::parseRaw($raw) : [];

        $production->calculate();

        $production->doParse();

        // if production byproducts can be utilized then calculate again.
        // V74: iterate to steady state with an early exit instead of a fixed
        // 3× — an identical byproduct state means further passes are no-ops,
        // so stopping early is byte-identical to the old fixed-pass behavior.
        if ($production->hasUsableByproducts()) {
            $previous = null;

            for ($pass = 0; $pass < self::MAX_BYPRODUCT_PASSES; $pass++) {
                $production->recalculateUsingByproducts();

                $state = $production->byproductState();

                if ($state === $previous) {
                    break;
                }

                $previous = $state;
            }
        }

        return $production;
    }

    /**
     * Serialized byproduct production/consumption state, used to detect
     * convergence between recalculation passes (V74).
     */
    public function byproductState(): string
    {
        return json_encode([
            'byproducts' => $this->getByproducts(),
            'used' => $this->getByproductsUsed(),
        ]);
    }

    /**
     * Finite cache TTL (seconds) for production result payloads — replaces
     * rememberForever so entries eventually evict instead of growing without
     * bound (V70/B46).
     */
    public const CACHE_TTL = 86400; // 24h

    /**
     * Request params that actually affect the computed plan. The cache key is
     * built from these only — NOT request()->all() (V70/B46): hashing the full
     * request minted a new never-evicted entry per distinct/irrelevant param
     * (factory id, etc.), and pairing that with forever-cached object graphs
     * exhausted the 128 MB PHP memory limit in production.
     */
    protected const CACHE_REQUEST_KEYS = [
        'belt_speed', 'even', 'speedLimit', 'imports', 'somersloops',
        'cost_multiplier', 'power_multiplier', 'building_multiples',
        'building_cost_multiplier', 'raw',
        // raw-source modes + sink recycling change the computed payload
        // (V59/V66/V67); import_notes is display-only and deliberately
        // excluded (V64) so note edits don't mint new cache entries.
        'raw_sources', 'auto_package_recycle',
    ];

    /**
     * Hash of the whitelisted, computation-affecting request params (V70).
     */
    public static function requestCacheSegment(): string
    {
        return md5(collect(request()->only(static::CACHE_REQUEST_KEYS))->toJson());
    }

    /**
     * Build (or read from cache) the plain-array result payload for a single
     * production plan. V70: caches only serializable arrays — never the live
     * ProductionCalculator/Step graph or Eloquent models — under a finite TTL
     * and a whitelisted key. Effective favorites are encoded in the key to
     * prevent cross-user contamination (B13).
     *
     * @return array<string, mixed>
     */
    public static function cachedPayload(
        $product, $qty, $recipe = null, $overrides = [], $favorites = null, $imports = [], $variant = 'mk1', $choices = [], $byproducts = []
    ): array {
        // Encode effective favorites in the cache key to prevent cross-user
        // contamination (B13). When null, resolve session favorite IDs now so
        // the key differs per user.
        $favoritesKey = is_null($favorites)
            ? Favorites::all()->pluck('id')->sort()->values()->all()
            : collect($favorites)->map(fn ($r) => is_object($r) ? $r->id : $r)->sort()->values()->all();

        // V108/B60: the key carries a version segment so entries written by
        // older code (pre-V70 rememberForever'd the live calculator object
        // under the unversioned prefix) can never be read back here. Bump the
        // version whenever the cached shape changes.
        // v5: recycling production folded into results (V137)
        $cacheKey = 'production_calc_v5_'
            .md5(collect(compact('product', 'qty', 'recipe', 'overrides', 'imports', 'variant', 'choices', 'byproducts'))->put('favorites', $favoritesKey)->toJson())
            .static::requestCacheSegment();

        // V108: never trust a cached entry's shape — anything but an array
        // (stale object graph, corrupted entry) is recomputed and overwritten
        // instead of fataling on the array return type.
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $payload = static::make(
            product: $product,
            qty: $qty,
            recipe: $recipe,
            overrides: $overrides,
            favorites: $favorites,
            imports: $imports,
            variant: $variant,
            choices: $choices,
            byproducts: $byproducts,
        )->toPayload();

        Cache::put($cacheKey, $payload, static::CACHE_TTL);

        return $payload;
    }

    /**
     * Plain-array Inertia payload for this plan. The json round-trip guarantees
     * no objects/Eloquent models survive into the cache (V70, T113) — identical
     * in shape to what Inertia serializes to the client.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        // V66/V67/V137: AWESOME Sink recycling. Request params consumed here
        // (auto_package_recycle, belt_speed, raw_sources) are all in
        // CACHE_REQUEST_KEYS, so caching the full payload stays correct (V70).
        //
        // V137: recycling production (Packager steps + container chain) is
        // injected into the plan itself via an internal Multiplexer wrap —
        // packaging renders as normal steps inside `results`, overlapping
        // production merges, chain raws surface at Level 0. `final`/`recipe`/
        // `overrides` stay this calc's own single tree (injected calcs are not
        // requested outputs). With no recycling injection the wrap is inert:
        // every getter below reduces to this calc's own values.
        $multiplexer = new Multiplexer;
        $multiplexer->add($this);

        $recycling = RecyclingCalc::inject($multiplexer, request()->boolean('auto_package_recycle'));

        return json_decode(json_encode([
            'results' => $multiplexer->getResults(),
            'byproducts_used' => $multiplexer->getByproductsUsed(),
            'raw_materials' => $multiplexer->getRawMaterials(),
            'intermediate_materials' => $multiplexer->getIntermediateMaterials(),
            'all_materials' => $multiplexer->getAllMaterials(),
            'final' => $this->getSteps()->toArray(),
            'recipe' => $this->getSteps()->getRecipe(),
            'overrides' => $this->getSteps()->getOverrides(),
            'byproducts' => $multiplexer->getByproducts(),
            'overviews' => $multiplexer->getOverviews(),
            // V76: extract-mode raws → extractor building rows (summary/parts/power);
            // V137: injected-chain raws are inside getRawMaterials already
            'extractors' => ExtractorSummary::build(collect($multiplexer->getRawMaterials()), request('raw_sources', [])),
            'recycling' => $recycling,
            // V137: sink-only — packaging + chain steps live in `results`
            'recycling_steps' => RecyclingSteps::build($recycling, (float) request('belt_speed', 780)),
        ]), true);
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
            // V96: a raw chosen as the OUTPUT with an explicit producing recipe
            // (Unpackage/Convert) is produced via that recipe, not forced to a leaf.
            if ($recipe) {
                $this->recipe = r($recipe);
            }

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
                parent: $this->dest,
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
                parent: $this->dest,
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
        // V97: the requested OUTPUT product is never the injected source — neither
        // swapped nor imported. Importing the output blanks the whole plan; swapping it
        // discards the recipe the user explicitly chose for it. Break on another member.
        $output = optional($this->product)->name;

        $candidates = collect($members)
            ->reject(fn ($m) => $this->unpackagesItself($m, $recipeOf) || $m === $output)
            ->map(fn ($m) => ['product' => $m, 'recipe' => $this->loopFreeRecipe($m, $members)])
            ->filter(fn ($c) => $c['recipe'] !== null)
            ->sortBy(fn ($c) => ($userPicked->contains($c['product']) ? 1e9 : 0)
                + (float) $c['recipe']->resource)
            ->values();

        if ($candidates->isNotEmpty()) {
            $best = $candidates->first();

            return ['product' => $best['product'], 'recipe' => $best['recipe'], 'import' => null];
        }

        // no loop-free recipe for any member → auto-import a NON-output member (prefer a
        // non-user-chosen one); fall back to any member only if the output is the sole one.
        $importable = collect($members)
            ->reject(fn ($m) => $m === $output)
            ->sortBy(fn ($m) => $userPicked->contains($m) ? 1 : 0)
            ->first()
            ?? collect($members)->sortBy(fn ($m) => $userPicked->contains($m) ? 1 : 0)->first();

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
     * by raw. V79: gated on mode — the recipe no longer lives here. V105/B59: the
     * endpoint is public, so keys are untrusted — only names that resolve to a real
     * raw ingredient pass (unknown names would hit defaultRawRecipe(null); non-raws
     * don't take source modes at all).
     *
     * @return Collection<string, array{mode: string}>
     */
    protected function rawSourceConfigs(): Collection
    {
        return collect(request('raw_sources', []))
            ->filter(fn ($config) => is_array($config)
                && in_array($config['mode'] ?? 'import', ['convert', 'unpackage'], true))
            ->filter(fn ($config, $name) => is_string($name)
                && ($ingredient = i($name))
                && $ingredient->isRaw());
    }

    /**
     * Raw product names sourced via a convert/unpackage recipe (V61/V63 loop
     * candidates). V105/B59: only recipe-resolved raws — mergeRawSources sets a
     * choice for every raw it resolves, so a recipe-less raw (e.g. Wood in convert
     * mode) is excluded here rather than blowing up in detectLoops' baseRecipe()
     * call. Requires mergeRawSources to have run first (it does — make():316 vs 318).
     */
    protected function rawRecipeProducts(): Collection
    {
        return $this->rawSourceConfigs()->keys()
            ->filter(fn ($name) => $this->choices->has($name))
            ->values();
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
