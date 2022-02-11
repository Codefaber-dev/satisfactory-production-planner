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
use Inertia\Inertia;

class ProductionController extends Controller
{
    protected function baseData()
    {
        $products = Ingredient::processed()->orderBy('name')->get();
        $recipes = $products->map(function($product) {
            return [$product->name => $product->recipes()->with('ingredients')->get()];
        })->collapse();
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
        //$production = Production::make(
        //    product: $product = i($ingredient),
        //    qty: $yield = $qty,
        //    recipe: $recipe = r($recipe),
        //    variant: $variant,
        //);

        $calc = ProductionCalculator::make(
            product: $product = i($ingredient),
            qty: $yield = $qty,
            recipe: $recipe,
            imports: $imports = request()->has('imports') ? explode(",",request("imports")) : [],
            favorites: Favorites::all(),
            variant: $variant
        );

        $production = [
            'results' => $calc->getResults(),
            'raw_materials' => $calc->getRawMaterials(),
            'intermediate_materials' => $calc->getIntermediateMaterials()
        ];

        //dd($production);

        $belt_speed = request('belt_speed',780);

        return Inertia::render('Production/ShowNew',compact('production','product','yield','recipe','variant','belt_speed','imports') + $this->baseData());
    }

    public function newYield($ingredient, $qty, $recipe, $variant="mk1")
    {
        $production = Production::make(
            product: i($ingredient),
            qty: $qty,
            recipe: r($recipe),
            variant: $variant,
        );

        $newQty = $production->getAdjustedQty();
        $belt_speed = request('belt_speed',780);
        $factory = request('factory');
        $imports = $production->getMappedImports();

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
