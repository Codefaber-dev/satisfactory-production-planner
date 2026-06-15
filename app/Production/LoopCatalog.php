<?php

namespace App\Production;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;

/**
 * Stage 1 of circular-dependency resolution (V58): precompute the candidate
 * loop catalog over the full recipe catalog. Pure graph work — Tarjan SCC over
 * product -> ingredient edges — so it is unit-testable without a database.
 */
class LoopCatalog
{
    /**
     * Detect candidate loop clusters in a recipe set.
     *
     * @param  array<int, array{product: string, recipe: ?string, ingredients: array<int, string>}>  $recipes
     * @param  array<int, string>  $boundary  raw/import ingredient names — edges to/from these are cut
     * @return array<int, array{members: array<int, string>, enabledBy: array<int, array{product: string, recipe: string}>}>
     */
    public static function detect(array $recipes, array $boundary = []): array
    {
        $isBoundary = array_fill_keys($boundary, true);

        // build adjacency: product -> set of ingredient names (non-boundary only)
        $adjacency = [];
        foreach ($recipes as $recipe) {
            $product = $recipe['product'];

            if (isset($isBoundary[$product])) {
                continue;
            }

            $adjacency[$product] ??= [];

            foreach ($recipe['ingredients'] as $ingredient) {
                if (isset($isBoundary[$ingredient])) {
                    continue;
                }

                $adjacency[$product][$ingredient] = true;
                $adjacency[$ingredient] ??= [];
            }
        }

        $sccs = static::tarjan($adjacency);

        // a cluster = an SCC with >1 member, or a single node with a self-loop
        $clusters = [];
        foreach ($sccs as $scc) {
            $isLoop = count($scc) > 1
                || (count($scc) === 1 && isset($adjacency[$scc[0]][$scc[0]]));

            if (! $isLoop) {
                continue;
            }

            $members = array_fill_keys($scc, true);

            $clusters[] = [
                'members' => $scc,
                'enabledBy' => static::enablingRecipes($recipes, $members, $isBoundary),
            ];
        }

        return $clusters;
    }

    /**
     * Cached catalog over the live recipe set, invalidated on Recipe/Ingredient
     * save (App\Observers — they forget the 'loop_catalog' key).
     */
    public static function all(): array
    {
        return Cache::rememberForever('loop_catalog', function () {
            $recipes = Recipe::all()->map(fn (Recipe $r) => [
                'product' => $r->product->name,
                'recipe' => $r->description ?? $r->product->name,
                'ingredients' => $r->ingredients->pluck('name')->all(),
            ])->all();

            $boundary = Ingredient::where('raw', true)->pluck('name')->all();

            return static::detect($recipes, $boundary);
        });
    }

    /**
     * Recipes whose product is in the cluster and that consume a (non-boundary)
     * ingredient also in the cluster — i.e. the picks that create the loop.
     */
    protected static function enablingRecipes(array $recipes, array $members, array $isBoundary): array
    {
        $seen = [];
        $enabling = [];

        foreach ($recipes as $recipe) {
            $product = $recipe['product'];

            if (! isset($members[$product])) {
                continue;
            }

            foreach ($recipe['ingredients'] as $ingredient) {
                if (isset($isBoundary[$ingredient]) || ! isset($members[$ingredient])) {
                    continue;
                }

                $description = $recipe['recipe'] ?? $product;
                $key = $product.'|'.$description;

                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $enabling[] = ['product' => $product, 'recipe' => $description];
                }

                break;
            }
        }

        return $enabling;
    }

    /**
     * Tarjan's strongly-connected-components algorithm.
     *
     * @param  array<string, array<string, bool>>  $adjacency
     * @return array<int, array<int, string>>
     */
    protected static function tarjan(array $adjacency): array
    {
        $index = [];
        $lowlink = [];
        $onStack = [];
        $stack = [];
        $counter = 0;
        $sccs = [];

        $strongconnect = function (string $node) use (
            &$strongconnect, &$adjacency, &$index, &$lowlink, &$onStack, &$stack, &$counter, &$sccs
        ): void {
            $index[$node] = $counter;
            $lowlink[$node] = $counter;
            $counter++;
            $stack[] = $node;
            $onStack[$node] = true;

            foreach (array_keys($adjacency[$node] ?? []) as $neighbor) {
                if (! isset($index[$neighbor])) {
                    $strongconnect($neighbor);
                    $lowlink[$node] = min($lowlink[$node], $lowlink[$neighbor]);
                } elseif (! empty($onStack[$neighbor])) {
                    $lowlink[$node] = min($lowlink[$node], $index[$neighbor]);
                }
            }

            if ($lowlink[$node] === $index[$node]) {
                $scc = [];
                do {
                    $w = array_pop($stack);
                    $onStack[$w] = false;
                    $scc[] = $w;
                } while ($w !== $node);
                $sccs[] = $scc;
            }
        };

        foreach (array_keys($adjacency) as $node) {
            if (! isset($index[$node])) {
                $strongconnect($node);
            }
        }

        return $sccs;
    }
}
