<?php

namespace App\Http\Controllers;

use App\Factories\Facades\Factories;
use App\Favorites\Facades\Favorites;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\MultiFactories\Facades\MultiFactories;
use App\Production\Multiplexer;
use App\Production\ProductionCalculator;
use App\Production\ProductionGlobals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;
use function http_build_query;

class ProductionController extends Controller
{
    protected function baseData()
    {
        $products = Ingredient::processed()->orderBy('name')->get();
        $recipes = Cache::remember('all_recipes', now()->addDay(), function() {
            return Recipe::all()->groupBy(fn($recipe) => $recipe->product->name);
        });
        $favorites = Favorites::all();
        $factory = Factories::find(request('factory'));
        $multiFactory = MultiFactories::find(request('multiFactory'));
        $choices = !empty(request('choices',[])) ? collect(request('choices',(object) []))->reject(fn($name) => r($name)->isDefault())->all() : null;
        $even = request('even') ? 1 : 0;


        return compact('products','recipes','favorites','factory','multiFactory','choices','even');
    }

    public function index()
    {
        return Inertia::render('Production/Index',$this->baseData());
    }

    public function show($ingredient, $qty, $recipe, $variant="mk1")
    {
        $choices = collect(request('choices'))->map(fn($name) => r($name));

        $calc = ProductionCalculator::make(
            product: $product = i($ingredient),
            qty: $yield = $qty,
            recipe: $recipe,
            imports: request()->has('imports') ? explode(",",request("imports")) : [],
            variant: $variant,
            choices: $choices
        );

        $production = [
            'results' => $calc->getResults(),
            'byproducts_used' => $calc->getByproductsUsed(),
            'raw_materials' => $calc->getRawMaterials(),
            'intermediate_materials' => $calc->getIntermediateMaterials(),
            'all_materials' => $calc->getAllMaterials(),
            'final' => $calc->getSteps()->toArray(),
            'recipe' => $calc->getSteps()->getRecipe(),
            'overrides' => $calc->getSteps()->getOverrides(),
            'byproducts' => $calc->getByproducts(),
            'overviews' => $calc->getOverviews()
        ];

        //dd($calc->getOverviews());

        $imports = request('imports');

        $belt_speed = request('belt_speed',780);

        return Inertia::render('Production/Show',compact('production','product','yield','recipe','variant','belt_speed','imports') + $this->baseData());
    }

    public function multi()
    {
        $products = collect(request('product'))->map(fn($name) => i($name));
        $yields = request('yield');
        $recipes = collect(request('recipe'))->map(fn($name) => r($name));
        $variant = request('variant');
        $choices = collect(request('choices'))->map(fn($name) => r($name));
        $m = new Multiplexer;

        $multi = compact('products','yields','recipes');

        foreach($products as $key => $product) {
            $qty = $yields[$key];
            $recipe = $recipes[$key];
            $calc = ProductionCalculator::make(
                product: $product,
                qty: $qty,
                recipe: $recipe,
                imports: request()->has('imports') ? explode(",",request("imports")) : [],
                variant: $variant,
                choices: $recipes->map(fn($recipe) => [$recipe->product->name => $recipe])->collapse()->merge($choices)
            );
            $m->add($calc);
        }

        // now recalculate to take advantage of byproducts,
        // do it several times to ensure a steady state
        $m->recalculateUsingByproducts();
        $m->recalculateUsingByproducts();
        $m->recalculateUsingByproducts();


        $production = [
            'results' => $m->getResults(),
            'byproducts_used' => $m->getByproductsUsed(),
            'raw_materials' => $m->getRawMaterials(),
            'intermediate_materials' => $m->getIntermediateMaterials(),
            'all_materials' => $m->getAllMaterials(),
            'final' => $m->getFinals(),
            'recipe' => $m->getRecipes(),
            'overrides' => $m->getOverrides(),
            'byproducts' => $m->getByproducts(),
            'overviews' => $m->getOverviews()
        ];

        $imports = request('imports');

        $belt_speed = request('belt_speed',780);

        return Inertia::render('Production/Show',compact('production','variant','belt_speed','imports','multi') + $this->baseData());
    }

    public function newYield($ingredient, $qty, $recipe, $variant="mk1")
    {
        $choices = request('choices');

        $calc = ProductionCalculator::make(
            product: $product = i($ingredient),
            qty: $yield = $qty,
            recipe: $recipe,
            imports: $imports = request()->has('imports') ? explode(",",request("imports")) : [],
            favorites: Favorites::all(),
            variant: $variant,
            choices: collect($choices)->map(fn($name) => r($name))
        );

        $newQty = $calc->getAdjustedQty();
        $belt_speed = request('belt_speed',780);
        $factory = request('factory');
        $imports = request('imports');
        $choices = http_build_query(compact('choices'));

        return redirect()->to("/dashboard/$ingredient/{$newQty}/$recipe/$variant?belt_speed={$belt_speed}&factory={$factory}&imports={$imports}&{$choices}");
    }


    public function newYieldMulti()
    {
        $choices = request('choices');
        $products = collect(request('product'))->map(fn($name) => i($name));
        $yields = request('yield');
        $recipes = collect(request('recipe'))->map(fn($name) => r($name));
        $variant = request('variant');

        $m = new Multiplexer;

        foreach($products as $key => $product) {
            $qty = $yields[$key];
            $recipe = $recipes[$key];
            $calc = ProductionCalculator::make(
                product: $product,
                qty: $qty,
                recipe: $recipe,
                imports: request()->has('imports') ? explode(",",request("imports")) : [],
                variant: $variant,
                choices: collect($choices)->map(fn($name) => r($name))->merge($recipes->map(fn($recipe) => [$recipe->product->name => $recipe])->collapse())
            );
            $m->add($calc);
        }

        $ratio = $m->ratioOfAvailableRawMaterials();

        foreach($yields as $key => $value) {
            $yields[$key] = floor(10000 * $value * $ratio) / 10000;
        }

        $belt_speed = request('belt_speed',780);
        $factory = request('factory');
        $imports = request('imports');

        return redirect()->to("/dashboard/multi?belt_speed={$belt_speed}&factory={$factory}&imports={$imports}&variant={$variant}&" .
            http_build_query(["choices" => $choices, "product" => request('product'), "yield" => $yields, "recipe" => $recipes->map(fn($recipe) => $recipe->description ?? $recipe->product->name)->all()]));
    }

    public function addFavorite(Recipe $recipe)
    {
        $product = $recipe->product;
        //$yield = 10;
        //$production = ProductionCalculator::calc($product,$yield,$recipe);

        Favorites::set($product,$recipe);

        //return Inertia::render('Production/Show',compact('production','product','yield','recipe') + $this->baseData());

        return redirect()->back();
    }

    public function addSubFavorite(Recipe $recipe)
    {
        Favorites::set($recipe->product, $recipe);

        return redirect()->back();
    }
}
