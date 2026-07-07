<?php

namespace App\Support;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;

/**
 * Cached plain-array payloads for the shared production-page props (V73).
 *
 * The recipes payload bakes the deterministic appends (tier, energy_efficient,
 * resource_efficient) at cache-fill time so serving a request never recomputes
 * them — previously every Inertia serialization ran the accessors per recipe
 * per request (a cache read per efficiency flag plus a favorites lookup each).
 *
 * The per-user `favorite` flag is deliberately EXCLUDED from the cached entry
 * (B13 cross-user isolation) and merged in per request via withFavorites().
 */
class BaseDataPayload
{
    public const RECIPES_KEY = 'base_data.recipes_payload';

    public const PRODUCTS_KEY = 'base_data.products_payload';

    public const LIQUIDS_KEY = 'base_data.liquids_payload';

    public const TTL = 86400; // 24h — observers flush on Recipe/Ingredient save

    /**
     * Grouped recipes payload (product name => recipe arrays), favorite-free.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function recipes(): array
    {
        return Cache::remember(static::RECIPES_KEY, static::TTL, function () {
            $grouped = Recipe::all()
                ->each(fn (Recipe $recipe) => $recipe->makeHidden('favorite'))
                ->groupBy(fn (Recipe $recipe) => $recipe->product->name);

            // json round-trip guarantees plain arrays only — no Eloquent
            // models survive into the cache (same pattern as V70).
            return json_decode($grouped->toJson(), true);
        });
    }

    /**
     * Processed (non-raw) ingredients payload, name-ordered.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function products(): array
    {
        return Cache::remember(static::PRODUCTS_KEY, static::TTL, function () {
            return json_decode(Ingredient::processed()->orderBy('name')->get()->toJson(), true);
        });
    }

    /**
     * Recipes payload with the per-user favorite flag merged in (immutably).
     *
     * @param  array<int, int>  $favoriteRecipeIds
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function withFavorites(array $favoriteRecipeIds): array
    {
        $ids = array_flip($favoriteRecipeIds);

        return array_map(
            fn (array $group) => array_map(
                fn (array $recipe) => [...$recipe, 'favorite' => isset($ids[$recipe['id']])],
                $group
            ),
            static::recipes()
        );
    }

    /**
     * Names of every liquid/gas ingredient (raw + processed) — pipe materials
     * that never ride belts, so build-diagram manifolds skip them (V117).
     *
     * @return array<int, string>
     */
    public static function liquids(): array
    {
        return Cache::remember(static::LIQUIDS_KEY, static::TTL, function () {
            return Ingredient::where('is_liquid', true)->orderBy('name')->pluck('name')->all();
        });
    }

    public static function flush(): void
    {
        Cache::forget(static::RECIPES_KEY);
        Cache::forget(static::PRODUCTS_KEY);
        Cache::forget(static::LIQUIDS_KEY);
    }
}
