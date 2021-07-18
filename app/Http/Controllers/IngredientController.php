<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use function response;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Ingredient::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\NewIngredientRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(NewIngredientRequest $request): \Illuminate\Http\JsonResponse
    {
        $ingredient = Ingredient::create($request->validated());

        return response()->json($ingredient,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Ingredient $ingredient): \Illuminate\Http\JsonResponse
    {
        return response()->json($ingredient);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateIngredientRequest $request
     * @param \App\Models\Ingredient $ingredient
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): \Illuminate\Http\JsonResponse
    {
        $ingredient->update($request->validated());

        return response()->json($ingredient->fresh(), 202);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ingredient $ingredient): \Illuminate\Http\JsonResponse
    {
        $ingredient->delete();

        return response()->json([],202);
    }
}
