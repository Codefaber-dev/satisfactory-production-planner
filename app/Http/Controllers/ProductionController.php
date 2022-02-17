<?php

namespace App\Http\Controllers;

use App\Factories\Facades\Factories;
use App\Favorites\Facades\Favorites;
//use App\Helpers\ProductionCalculator;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\ProductionCalculator;
use App\ProductionBak\Production;
use App\ProductionBak\ProductionStep;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;

class ProductionController extends Controller
{
    protected function baseData()
    {
        $products = Ingredient::processed()->orderBy('name')->get();
        $recipes = Recipe::all()->groupBy(fn($recipe) => $recipe->product->name);
        $favorites = Favorites::all();
        $factory = Factories::find(request('factory'));


        return compact('products','recipes','favorites','factory');
    }

    public function index()
    {
        return Inertia::render('Production/Index',$this->baseData());
    }

    public function show($ingredient, $qty, $recipe, $variant="mk1")
    {
        $calc = ProductionCalculator::make(
            product: $product = i($ingredient),
            qty: $yield = $qty,
            recipe: $recipe,
            imports: request()->has('imports') ? explode(",",request("imports")) : [],
            variant: $variant
        );

        $production = [
            'results' => $calc->getResults(),
            'raw_materials' => $calc->getRawMaterials(),
            'intermediate_materials' => $calc->getIntermediateMaterials(),
            'all_materials' => $calc->getAllMaterials(),
            'final' => $calc->getSteps()->toArray(),
            'recipe' => $calc->getSteps()->getRecipe(),
            'overrides' => $calc->getSteps()->getOverrides(),
        ];

        $imports = request('imports');


        $belt_speed = request('belt_speed',780);

        return Inertia::render('Production/Show',compact('production','product','yield','recipe','variant','belt_speed','imports') + $this->baseData());
    }

    public function newYield($ingredient, $qty, $recipe, $variant="mk1")
    {
        $calc = ProductionCalculator::make(
            product: $product = i($ingredient),
            qty: $yield = $qty,
            recipe: $recipe,
            imports: $imports = request()->has('imports') ? explode(",",request("imports")) : [],
            favorites: Favorites::all(),
            variant: $variant
        );

        $newQty = $calc->getAdjustedQty();
        $belt_speed = request('belt_speed',780);
        $factory = request('factory');
        $imports = request('imports');

        return redirect()->to("/dashboard/$ingredient/{$newQty}/$recipe/$variant?belt_speed={$belt_speed}&factory={$factory}&imports={$imports}");
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
