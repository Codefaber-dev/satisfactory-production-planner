<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ChecklistController extends Controller
{
    public function index()
    {
        return Inertia::render('Checklist/Index', [
            // V149: the checklist item set mirrors the DB ingredient set, so newly
            // seeded game data appears without touching product_list.js
            'ingredients' => Cache::remember(
                'checklist_ingredients',
                MODEL_CACHE_TTL,
                fn () => Ingredient::orderBy('name')->get(['name', 'tier', 'raw', 'is_liquid', 'is_ficsmas'])
            ),
        ]);
    }
}
