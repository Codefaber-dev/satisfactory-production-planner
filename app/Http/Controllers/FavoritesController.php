<?php

namespace App\Http\Controllers;

use App\Favorites\Facades\Favorites;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function store($recipe)
    {
        Favorites::set($recipe->product, $recipe);

        return Favorites::all();
    }
}
