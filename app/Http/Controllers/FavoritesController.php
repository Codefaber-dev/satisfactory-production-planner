<?php

namespace App\Http\Controllers;

use App\Favorites\Facades\Favorites;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoritesController extends Controller
{
    public function index()
    {
        $products = Ingredient::where('tier', '>', 1)->with('recipes.product')->orderBy('name')->get();
        $favorites = Favorites::all();

        return Inertia::render('Favorites/Index', compact('products', 'favorites'));
    }

    public function store()
    {
        $recipe = Recipe::find(request('recipe'));

        if ($recipe) {
            Favorites::set($recipe->product, $recipe);
        }

        return redirect()->to('/favorites');
    }

    public function destroy($id)
    {
        $product = Ingredient::find($id);
        Favorites::setDefault($product);

        return redirect()->to('/favorites');
    }

    public function destroyAll()
    {
        $favorites = Favorites::all();
        $favorites->each(fn(Recipe $favorite) => Favorites::setDefault($favorite->product));

        return redirect()->to('/favorites');
    }

    public function storePreset()
    {
        $preset = request('preset');

        if ($preset) {
            foreach ($preset as $item) {
                if ( $item['recipe'] === 'default' ) {
                    $recipe = i($item['product'])->baseRecipe();
                }
                else {
                    $recipe = r($item['recipe']);
                }

                if ($recipe) {
                    Favorites::set($recipe->product, $recipe);
                }
            }
        }

        return redirect()->to('/favorites');
    }
}
