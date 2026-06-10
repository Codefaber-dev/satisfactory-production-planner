<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;

use function response;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Ingredient::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewIngredientRequest $request): JsonResponse
    {
        $ingredient = Ingredient::create($request->validated());

        return response()->json($ingredient, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ingredient $ingredient): JsonResponse
    {
        return response()->json($ingredient);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): JsonResponse
    {
        $ingredient->update($request->validated());

        return response()->json($ingredient->fresh(), 202);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $ingredient->delete();

        return response()->json([], 202);
    }
}
