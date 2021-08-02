<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function store($recipe)
    {
        request()->user()->addFavorite($recipe);

        return auth()->user()->favorite_recipes;
    }
}
