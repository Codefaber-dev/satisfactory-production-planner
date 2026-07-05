<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * V105(b): shared validation + sanitation for the plan params persisted into
 * JSON columns (raw_sources, import_notes) by ProductionLineController and
 * MultiProductionLineController. The calculator side tolerates crafted input
 * (V105(a)); this bounds what is ever stored.
 */
class PlanParams
{
    public const RAW_SOURCE_KEYS = ['mode', 'purity', 'miner', 'shards'];

    public static function rules(): array
    {
        return [
            'raw_sources' => ['sometimes', 'array'],
            'raw_sources.*' => ['array'],
            'raw_sources.*.mode' => ['sometimes', Rule::in(['import', 'extract', 'convert', 'unpackage'])],
            'raw_sources.*.purity' => ['sometimes', Rule::in(['impure', 'normal', 'pure'])],
            'raw_sources.*.miner' => ['sometimes', Rule::in(['mk1', 'mk2', 'mk3'])],
            'raw_sources.*.shards' => ['sometimes', 'integer', 'between:0,3'],
            'import_notes' => ['sometimes', 'array'],
            'import_notes.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Whitelist each raw's config to the known keys — crafted extras are dropped
     * before the map reaches the JSON column.
     */
    public static function sanitizeRawSources(array $rawSources): array
    {
        return array_map(
            fn ($config) => array_intersect_key($config, array_flip(self::RAW_SOURCE_KEYS)),
            $rawSources,
        );
    }
}
