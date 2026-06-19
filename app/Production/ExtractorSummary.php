<?php

namespace App\Production;

use App\Models\Building;
use Illuminate\Support\Collection;

/**
 * V76: turn each `extract`-mode raw into an extractor "building detail" row so it
 * surfaces in the building summary + parts list + plan power total, alongside the
 * recipe-step buildings. The extraction math lives in ExtractionCalc (V60).
 *
 * V77: the extractor buildings are now seeded (BuildingSeeder), so each row's
 * build_cost is read from the seeded building's mk1 variant recipe — extract raws
 * contribute build-cost materials to the parts list / total_build_cost. When no
 * building is found, build_cost falls back to empty (count + power only).
 */
class ExtractorSummary
{
    /**
     * @param  Collection<string, float>|array<string, float>  $rawMaterials  raw → plan total/min
     * @param  array<string, array{mode?: string, purity?: string, miner?: string, shards?: int, recipe?: string}>  $rawSources
     * @return array<int, array<string, mixed>>
     */
    public static function build(Collection|array $rawMaterials, array $rawSources): array
    {
        $rows = [];

        foreach (collect($rawMaterials) as $raw => $demand) {
            $config = $rawSources[$raw] ?? [];

            if (($config['mode'] ?? 'import') !== 'extract') {
                continue;
            }

            // never run the calc for a non-extractable raw (V74)
            if (! ExtractionCalc::isExtractable($raw)) {
                continue;
            }

            $calc = ExtractionCalc::calc($raw, (float) $demand, $config);

            if ($calc['count'] <= 0) {
                continue;
            }

            $shards = max(0, min(3, (int) ($config['shards'] ?? 0)));
            $building = static::buildingName($calc['type'], $config);

            $rows[] = [
                'product' => $raw,
                'building' => $building,
                'variant_name' => $building, // building-summary group key
                'num_buildings' => $calc['count'],
                'exact_buildings' => $calc['count'],
                'power_usage' => round($calc['power'], 4),
                'build_cost' => static::buildCost($building, $calc['count']),
                'clock_speed' => round($calc['overclock'] * 100, 4),
                'footprint' => [
                    'power_shards' => $calc['count'] * $shards,
                    'somersloops' => 0,
                ],
            ];
        }

        return $rows;
    }

    /**
     * Display name for the extractor (no seeded building to read from).
     *
     * @param  array{miner?: string}  $config
     */
    protected static function buildingName(string $type, array $config): string
    {
        return match ($type) {
            'water' => 'Water Extractor',
            'oil' => 'Oil Extractor',
            'well' => 'Resource Well Pressurizer',
            default => 'Miner '.match ($config['miner'] ?? 'mk2') {
                'mk1' => 'Mk.1',
                'mk3' => 'Mk.3',
                default => 'Mk.2',
            },
        };
    }

    /**
     * Build cost from the seeded extractor building's mk1 variant recipe (V77),
     * scaled by extractor count. Empty when the building isn't seeded.
     *
     * @return array<string, int>
     */
    protected static function buildCost(string $buildingName, int $count): array
    {
        $variant = optional(Building::ofName($buildingName))->variant('mk1');

        if (! $variant) {
            return [];
        }

        return $variant->recipe
            ->mapWithKeys(fn ($ingredient) => [
                $ingredient->name => (int) ceil($ingredient->pivot->qty * $count),
            ])
            ->all();
    }
}
