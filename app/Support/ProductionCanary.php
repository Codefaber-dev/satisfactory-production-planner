<?php

namespace App\Support;

use App\Production\ProductionCalculator;

/**
 * Golden-snapshot ("canary") definitions for the production calculator.
 *
 * The plan set + snapshot extraction live here so the export command and the
 * canary test use identical logic. Snapshots are intentionally slim and
 * deterministic (flattened product totals + total power), letting a refactor
 * prove acyclic plans stay byte-identical and surface exactly which loop plans
 * change (e.g. when T100b replaces forced-recipe loop handling with solving).
 */
class ProductionCanary
{
    public const FIXTURE = 'tests/fixtures/production_canary.json';

    /**
     * Representative single-output plans: acyclic, root loops (solved, T100a),
     * and deeper loops (forced today, change under T100b).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function plans(): array
    {
        return [
            'iron_plate' => ['product' => 'Iron Plate', 'qty' => 30],
            'reinforced_iron_plate' => ['product' => 'Reinforced Iron Plate', 'qty' => 30],
            'modular_frame' => ['product' => 'Modular Frame', 'qty' => 10],
            'rotor' => ['product' => 'Rotor', 'qty' => 20],
            'computer' => ['product' => 'Computer', 'qty' => 30],
            'quickwire_fused' => ['product' => 'Quickwire', 'qty' => 1740, 'recipe' => 'Fused Quickwire'],
            'root_loop_rubber' => [
                'product' => 'Rubber', 'qty' => 100, 'recipe' => 'Recycled Rubber',
                'favorites' => ['Plastic' => 'Recycled Plastic'],
            ],
            'root_loop_plastic' => [
                'product' => 'Plastic', 'qty' => 100, 'recipe' => 'Recycled Plastic',
                'favorites' => ['Rubber' => 'Recycled Rubber'],
            ],
            'caterium_computer' => [
                'product' => 'Computer', 'qty' => 30, 'recipe' => 'Caterium Computer',
                'favorites' => [
                    'Circuit Board' => 'Caterium Circuit Board',
                    'Plastic' => 'Recycled Plastic',
                    'Rubber' => 'Recycled Rubber',
                    'Fuel' => 'Unpackage Fuel',
                    'Packaged Fuel' => 'Packaged Fuel',
                    'Quickwire' => 'Fused Quickwire',
                ],
                'imports' => ['Caterium Ingot', 'Copper Ingot'],
            ],
        ];
    }

    /**
     * Build a plan and return its slim, comparable snapshot.
     *
     * @return array{totals: array<string, float>, power: float}
     */
    public static function snapshot(array $plan): array
    {
        $production = ProductionCalculator::make(
            product: $plan['product'],
            qty: $plan['qty'],
            recipe: $plan['recipe'] ?? null,
            favorites: collect($plan['favorites'] ?? [])->map(fn ($desc) => r($desc))->all(),
            imports: $plan['imports'] ?? [],
        );

        $totals = [];
        $production->getResults()->each(function ($tier, $t) use (&$totals) {
            $tier->each(function ($entry, $name) use (&$totals, $t) {
                $totals["{$t}.{$name}"] = round((float) $entry->total, 4);
            });
        });
        ksort($totals);

        return [
            'totals' => $totals,
            'power' => round((float) $production->getPowerUsage(), 4),
        ];
    }

    /**
     * Snapshot every plan, keyed by plan name.
     */
    public static function snapshotAll(): array
    {
        return collect(static::plans())
            ->map(fn ($plan) => ['plan' => $plan, 'snapshot' => static::snapshot($plan)])
            ->all();
    }
}
