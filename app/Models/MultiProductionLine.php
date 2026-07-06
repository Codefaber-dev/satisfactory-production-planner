<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MultiProductionLine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'outputs' => 'array',
        'choices' => 'array',
        'raw_sources' => 'array',
        'import_notes' => 'array',
        'auto_package_recycle' => 'bool',
    ];

    protected $appends = [
        'hash_id',
    ];

    public function getHashIdAttribute()
    {
        return 'ml_'.app('Hashids')->encode($this->id);
    }

    /**
     * B61/V109: a real static finder, NOT a scope — a scope returning null made
     * callScope() hand callers the Builder, which fataled on ->getPlanUrl().
     */
    public static function ofHashId($hashId): ?self
    {
        $id = app('Hashids')->decode((string) Str::of($hashId)->after('ml_'));

        return empty($id) ? null : static::find($id[0]);
    }

    public function getPlanUrl(): string
    {
        $atts = $this->toArray();
        $outputs = collect($atts['outputs']);
        $product = $outputs->map(fn ($output) => $output['product']['name'])->all();
        $yield = $outputs->map(fn ($output) => $output['yield'])->all();
        $recipe = $outputs->map(fn ($output) => $output['recipe']['description'] ?? $output['product']['name'])->all();
        $choices = $atts['choices'] ?? [];
        $raw_sources = $atts['raw_sources'] ?? [];
        $import_notes = $atts['import_notes'] ?? [];
        $auto_package_recycle = ! empty($atts['auto_package_recycle']) ? 1 : 0;

        return "/dashboard/multi?imports={$atts['imports']}&variant=mk1&".http_build_query(compact('product', 'yield', 'recipe', 'choices', 'raw_sources', 'import_notes', 'auto_package_recycle'));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
