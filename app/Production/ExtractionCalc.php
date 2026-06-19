<?php

namespace App\Production;

/**
 * Tier-0 extraction calc (V60). Pure math — no DB. Given a raw ingredient,
 * the requested demand (items/min), and extraction params (node purity, miner
 * tier, power shards), returns per-extractor rate, extractor/node count, and
 * total power for the `extract` source mode (V59).
 *
 * rate    = base × purity × overclock
 * overclock = (100 + 50·shards) / 100   (shards 0–3 → ≤ 250%)
 * count   = ceil(demand / rate)
 * power   = count × extractor_power × overclock^1.321928   (V15 energy model)
 */
class ExtractionCalc
{
    protected const POWER_EXPONENT = 1.321928; // V15

    protected const PURITY = ['impure' => 0.5, 'normal' => 1.0, 'pure' => 2.0];

    // §C extraction data (satisfactory.wiki.gg): rate /min @100% on a normal
    // node, power MW.
    protected const MINER = [
        'mk1' => ['rate' => 60.0, 'power' => 5.0],
        'mk2' => ['rate' => 120.0, 'power' => 15.0],
        'mk3' => ['rate' => 240.0, 'power' => 45.0],
    ];

    // Solid ores worked by a miner (§C). Biomass/organic raws (Wood, Leaves,
    // Mycelia, Alien Protein, creature Remains, …) are NOT here — they are
    // hand-collected/drops, not extractable (V74), so extractorType returns
    // 'none' rather than defaulting them to miner.
    protected const MINER_ORES = [
        'Iron Ore', 'Copper Ore', 'Caterium Ore', 'Coal', 'Limestone',
        'Sulfur', 'Raw Quartz', 'Bauxite', 'Uranium', 'SAM',
    ];

    /**
     * @param  array{purity?: string, miner?: string, shards?: int}  $params
     * @return array{type: string, rate_per: float, count: int, power: float, overclock: float}
     */
    public static function calc(string $raw, float $demand, array $params = []): array
    {
        $type = static::extractorType($raw);

        // V74: non-extractable raws (biomass/organic) have no extractor — zero result.
        if ($type === 'none') {
            return ['type' => 'none', 'rate_per' => 0.0, 'count' => 0, 'power' => 0.0, 'overclock' => 1.0];
        }

        $shards = max(0, min(3, (int) ($params['shards'] ?? 0)));
        $overclock = (100 + 50 * $shards) / 100; // ≤ 2.5

        [$base, $extractorPower, $appliesPurity] = static::profile($type, $params);

        $purity = $appliesPurity
            ? (static::PURITY[$params['purity'] ?? 'normal'] ?? 1.0)
            : 1.0;

        $ratePer = $base * $purity * $overclock;
        $count = $ratePer > 0 ? (int) ceil($demand / $ratePer) : 0;
        $power = $count * $extractorPower * pow($overclock, static::POWER_EXPONENT);

        return [
            'type' => $type,
            'rate_per' => $ratePer,
            'count' => $count,
            'power' => $power,
            'overclock' => $overclock,
        ];
    }

    // Dispatch extractor type by raw (§C / V60). Solid ores + SAM → miner; Water/
    // Crude Oil/Nitrogen Gas have dedicated extractors; everything else (biomass/
    // organic raws) is non-extractable → 'none' (V74). No miner default.
    public static function extractorType(string $raw): string
    {
        return match (true) {
            $raw === 'Water' => 'water',
            $raw === 'Crude Oil' => 'oil',
            $raw === 'Nitrogen Gas' => 'well',
            in_array($raw, self::MINER_ORES, true) => 'miner',
            default => 'none',
        };
    }

    // V74: can this raw be auto-extracted at all?
    public static function isExtractable(string $raw): bool
    {
        return static::extractorType($raw) !== 'none';
    }

    /**
     * Base rate, extractor power, applies-purity for the extractor type.
     *
     * @param  array{purity?: string, miner?: string, shards?: int}  $params
     * @return array{0: float, 1: float, 2: bool}
     */
    protected static function profile(string $type, array $params): array
    {
        return match ($type) {
            // Water: deep-water, not node-bound → no purity/tier, count = extractors.
            'water' => [120.0, 20.0, false],
            // Oil: purity applies, single building (no tier).
            'oil' => [120.0, 40.0, true],
            // Resource Well Pressurizer: aggregate canonical rate, purity applies.
            'well' => [120.0, 150.0, true],
            // Miner: purity + tier + shards.
            default => static::minerProfile($params),
        };
    }

    /**
     * @param  array{miner?: string}  $params
     * @return array{0: float, 1: float, 2: bool}
     */
    protected static function minerProfile(array $params): array
    {
        $tier = static::MINER[$params['miner'] ?? 'mk2'] ?? static::MINER['mk2'];

        return [$tier['rate'], $tier['power'], true];
    }
}
