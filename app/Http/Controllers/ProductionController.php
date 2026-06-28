<?php

namespace App\Http\Controllers;

use App\Factories\Facades\Factories;
use App\Favorites\Facades\Favorites;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\MultiFactories\Facades\MultiFactories;
use App\Production\Multiplexer;
use App\Production\ProductionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

use function http_build_query;

class ProductionController extends Controller
{
    protected function baseData()
    {
        $products = Ingredient::processed()->orderBy('name')->get();
        $recipes = Cache::remember('all_recipes', now()->addDay(), function () {
            return Recipe::all()->groupBy(fn ($recipe) => $recipe->product->name);
        });
        $favorites = Favorites::all();
        $factory = Factories::find(request('factory'));
        $multiFactory = MultiFactories::find(request('multiFactory'));
        $choices = ! empty(request('choices', [])) ? collect(request('choices', (object) []))->reject(fn ($name) => r($name)->isDefault())->all() : null;
        $even = request('even') ? 1 : 0;
        $speedLimit = request('speedLimit', 'both');

        return compact('products', 'recipes', 'favorites', 'factory', 'multiFactory', 'choices', 'even', 'speedLimit');
    }

    public function index()
    {
        return Inertia::render('Production/Index', $this->baseData());
    }

    public function show($ingredient, $qty, $recipe, $variant = 'mk1')
    {
        $product = i($ingredient);

        if (! $product) {
            abort(404);
        }

        $choices = collect(request('choices'))->map(fn ($name) => r($name));

        // V70: cache only the plain-array payload (finite TTL, whitelisted key),
        // never the live calculator object graph.
        $production = ProductionCalculator::cachedPayload(
            product: $product,
            qty: $yield = $qty,
            recipe: $recipe,
            imports: request()->has('imports') ? explode(',', request('imports')) : [],
            variant: $variant,
            choices: $choices
        );

        $imports = request('imports');
        $belt_speed = request('belt_speed', 780);
        $somersloops = request('somersloops', []);
        $cost_multiplier = max(0.1, min(10.0, (float) request('cost_multiplier', 1.0)));
        $power_multiplier = max(0.1, min(10.0, (float) request('power_multiplier', 1.0)));
        $building_multiples = request('building_multiples', []);
        $building_cost_multiplier = max(0.1, min(10.0, (float) request('building_cost_multiplier', 1.0)));

        return Inertia::render('Production/Show', compact('production', 'product', 'yield', 'recipe', 'variant', 'belt_speed', 'imports', 'somersloops', 'cost_multiplier', 'power_multiplier', 'building_multiples', 'building_cost_multiplier') + $this->baseData());
    }

    public function multi()
    {
        $products = collect(request('product'))->map(fn ($name) => i($name));
        $yields = request('yield');
        $recipes = collect(request('recipe'))->map(fn ($name) => r($name));
        $variant = request('variant');
        $choices = collect(request('choices'))->map(fn ($name) => r($name));
        $multi = compact('products', 'yields', 'recipes');
        $even = request('even') ? 1 : 0;
        $speedLimit = request('speedLimit', 'both');
        $belt_speed = request('belt_speed', 780);
        $imports = request('imports');
        $somersloops = request('somersloops', []);
        $cost_multiplier = max(0.1, min(10.0, (float) request('cost_multiplier', 1.0)));
        $power_multiplier = max(0.1, min(10.0, (float) request('power_multiplier', 1.0)));
        $building_multiples = request('building_multiples', []);
        $building_cost_multiplier = max(0.1, min(10.0, (float) request('building_cost_multiplier', 1.0)));

        // V70/B46: key from whitelisted inputs only — the md5(request()->all())
        // term minted a new never-evicted entry per distinct/irrelevant param.
        $cacheKey = 'multi_production_'
            .md5(collect(compact('products', 'yields', 'recipes', 'variant', 'choices', 'even', 'speedLimit', 'belt_speed', 'imports'))->toJson())
            .ProductionCalculator::requestCacheSegment();

        $production = Cache::remember($cacheKey, ProductionCalculator::CACHE_TTL, function () use ($products, $yields, $recipes, $variant, $choices) {

            $m = new Multiplexer;

            foreach ($products as $key => $product) {
                $qty = $yields[$key];
                $recipe = $recipes[$key];
                $calc = ProductionCalculator::make(
                    product: $product,
                    qty: $qty,
                    recipe: $recipe,
                    imports: request()->has('imports') ? explode(',', request('imports')) : [],
                    variant: $variant,
                    choices: $recipes->map(fn ($recipe) => [$recipe->product->name => $recipe])->collapse()->merge($choices)
                );
                $m->add($calc);
            }

            // now recalculate to take advantage of byproducts,
            // do it several times to ensure a steady state
            $m->recalculateUsingByproducts();
            $m->recalculateUsingByproducts();
            $m->recalculateUsingByproducts();

            // V70: json round-trip → plain arrays only (no objects/Eloquent) in
            // the cached payload, identical in shape to the Inertia props.
            return json_decode(json_encode([
                'results' => $m->getResults(),
                'byproducts_used' => $m->getByproductsUsed(),
                'raw_materials' => $m->getRawMaterials(),
                'intermediate_materials' => $m->getIntermediateMaterials(),
                'all_materials' => $m->getAllMaterials(),
                'final' => $m->getFinals(),
                'recipe' => $m->getRecipes(),
                'overrides' => $m->getOverrides(),
                'byproducts' => $m->getByproducts(),
                'overviews' => $m->getOverviews(),
            ]), true);
        });

        return Inertia::render('Production/Show', compact('production', 'variant', 'belt_speed', 'imports', 'multi', 'somersloops', 'cost_multiplier', 'power_multiplier', 'building_multiples', 'building_cost_multiplier') + $this->baseData());
    }

    public function newYield($ingredient, $qty, $recipe, $variant = 'mk1')
    {
        $choices = request('choices');

        $calc = ProductionCalculator::make(
            product: $product = i($ingredient),
            qty: $yield = $qty,
            recipe: $recipe,
            imports: $imports = request()->has('imports') ? explode(',', request('imports')) : [],
            favorites: Favorites::all(),
            variant: $variant,
            choices: collect($choices)->map(fn ($name) => r($name))
        );

        $newQty = $calc->getAdjustedQty();
        $belt_speed = request('belt_speed', 780);
        $factory = request('factory');
        $imports = request('imports');
        $choices = http_build_query(compact('choices'));

        return redirect()->to("/dashboard/$ingredient/{$newQty}/$recipe/$variant?belt_speed={$belt_speed}&factory={$factory}&imports={$imports}&{$choices}");
    }

    public function newYieldMulti()
    {
        $choices = request('choices');
        $products = collect(request('product'))->map(fn ($name) => i($name));
        $yields = request('yield');
        $recipes = collect(request('recipe'))->map(fn ($name) => r($name));
        $variant = request('variant');

        $m = new Multiplexer;

        foreach ($products as $key => $product) {
            $qty = $yields[$key];
            $recipe = $recipes[$key];
            $calc = ProductionCalculator::make(
                product: $product,
                qty: $qty,
                recipe: $recipe,
                imports: request()->has('imports') ? explode(',', request('imports')) : [],
                variant: $variant,
                choices: collect($choices)->map(fn ($name) => r($name))->merge($recipes->map(fn ($recipe) => [$recipe->product->name => $recipe])->collapse())
            );
            $m->add($calc);
        }

        $ratio = $m->ratioOfAvailableRawMaterials();

        foreach ($yields as $key => $value) {
            $yields[$key] = floor(10000 * $value * $ratio) / 10000;
        }

        $belt_speed = request('belt_speed', 780);
        $factory = request('factory');
        $imports = request('imports');

        return redirect()->to("/dashboard/multi?belt_speed={$belt_speed}&factory={$factory}&imports={$imports}&variant={$variant}&".
            http_build_query(['choices' => $choices, 'product' => request('product'), 'yield' => $yields, 'recipe' => $recipes->map(fn ($recipe) => $recipe->description ?? $recipe->product->name)->all()]));
    }

    public function addFavorite(Recipe $recipe)
    {
        $product = $recipe->product;
        // $yield = 10;
        // $production = ProductionCalculator::calc($product,$yield,$recipe);

        Favorites::set($product, $recipe);

        // return Inertia::render('Production/Show',compact('production','product','yield','recipe') + $this->baseData());

        return redirect()->back();
    }

    public function addSubFavorite(Recipe $recipe)
    {
        Favorites::set($recipe->product, $recipe);

        return redirect()->back();
    }
}
