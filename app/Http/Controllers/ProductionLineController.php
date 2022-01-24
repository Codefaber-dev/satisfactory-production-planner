<?php

namespace App\Http\Controllers;

use App\Factories\Facades\Factories;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductionLineController extends Controller
{
    protected function baseData()
    {
        $factories = Factories::all();

        return compact('factories');
    }

    public function index()
    {
        return Inertia::render('Factories/Index',$this->baseData());
    }

    public function store()
    {
        $attributes = [
            'name' => request('name'),
            'ingredient_id' => request('ingredient_id'),
            'recipe_id' => request('recipe_id'),
            'yield' => request('yield')
        ];

        Factories::create($attributes);

        return redirect()->to('/factories');
    }

    public function update($id)
    {
        $attributes = [
            'name' => request('name'),
            'recipe_id' => request('recipe_id'),
            'yield' => request('yield')
        ];

        Factories::update($id, $attributes);

        return redirect()->to('/factories');
    }

    public function destroy($id)
    {
        Factories::destroy($id);

        return redirect()->to('/factories');
    }
}
