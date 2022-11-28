<?php

namespace App\Http\Controllers;

use App\Factories\Facades\Factories;
use App\MultiFactories\Facades\MultiFactories;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductionLineController extends Controller
{
    protected function baseData()
    {
        $factories = Factories::all();
        $multiFactories = MultiFactories::all();

        return compact('factories','multiFactories');
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
            'yield' => request('yield'),
            'notes' => request()->has('notes') ? request('notes',"") : null,
            'imports' => request()->has('imports') ? request('imports',"") : null,
            'choices' => request()->has('choices') ? request('choices',[]) : []
        ];

        Factories::create($attributes);

        return redirect()->to('/factories');
    }

    public function update($id)
    {
        $attributes = [
            'name' => request('name'),
            'recipe_id' => request('recipe_id'),
            'yield' => request('yield'),
            'notes' => request()->has('notes') ? request('notes',"") : null,
            'imports' => request()->has('imports') ? request('imports',"") : null,
            'choices' => request()->has('choices') ? request('choices',[]) : [],
            'is_shared' => request()->has('is_shared') ? request('is_shared',false) : false
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
