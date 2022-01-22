<?php

namespace App\Http\Controllers;

use App\Favorites\Facades\Favorites;
use App\Helpers\ProductionCalculator;
use App\Models\Ingredient;
use App\Models\Recipe;
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

        return compact('products','recipes','favorites');
    }

    public function index()
    {
        return Inertia::render('Production/Index',$this->baseData());
    }

    public function show($ingredient, $qty, $recipe, $variant="mk1")
    {
        $production = ProductionCalculator::calc($ingredient,$qty,$recipe,$variant);
        $product = Ingredient::ofName($ingredient);
        $yield = $qty;
        $recipe = Recipe::ofName($recipe);
        $belt_speed = request('belt_speed',780);
        $diagrams = (bool) request('diagrams',true);

        return Inertia::render('Production/Show',compact('production','product','yield','recipe','variant','belt_speed','diagrams') + $this->baseData());
    }

    public function newYield($ingredient, $qty, $recipe, $variant="mk1")
    {
        $newQty = ProductionCalculator::newYield($ingredient,$qty,$recipe,$variant);
        $belt_speed = request('belt_speed',780);
        $diagrams = (int) request('diagrams',1);

        return redirect()->to("/dashboard/$ingredient/$newQty/$recipe/$variant?belt_speed={$belt_speed}&diagrams={$diagrams}");
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
