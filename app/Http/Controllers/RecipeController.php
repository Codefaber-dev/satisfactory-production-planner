<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Recipe::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:ingredients,id',
            'building_id' => 'required|exists:buildings,id',
            'base_per_min' => 'required|numeric|min:0',
            'base_yield' => 'required|numeric|min:0',
            'alt_recipe' => 'boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $recipe = Recipe::create($validated);

        return response()->json($recipe->fresh(), 201);
    }

    public function show(Recipe $recipe): JsonResponse
    {
        return response()->json($recipe);
    }

    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:ingredients,id',
            'building_id' => 'sometimes|exists:buildings,id',
            'base_per_min' => 'sometimes|numeric|min:0',
            'base_yield' => 'sometimes|numeric|min:0',
            'alt_recipe' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string|max:255',
        ]);

        $recipe->update($validated);

        return response()->json($recipe->fresh(), 202);
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        $recipe->delete();

        return response()->json([], 202);
    }
}
