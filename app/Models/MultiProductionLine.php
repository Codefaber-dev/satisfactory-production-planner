<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MultiProductionLine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        "outputs" => "array",
        "choices" => "array",
    ];

    protected $appends = [
        "hash_id"
    ];

    public function getHashIdAttribute()
    {
        return "ml_" . app('Hashids')->encode($this->id);
    }

    public function scopeOfHashId(Builder $query, $hashId)
    {
        $id = app('Hashids')->decode((string) Str::of($hashId)->after("ml_"));

        if (empty($id))
            return null;

        return static::find($id[0]);
    }

    public function getPlanUrl(): string
    {
        $atts = $this->toArray();
        $outputs = collect($atts['outputs']);
        $product = $outputs->map(fn($output) => $output['product']['name'])->all();
        $yield = $outputs->map(fn($output) => $output['yield'])->all();
        $recipe = $outputs->map(fn($output) => $output['recipe']['description'] ?? $output['product']['name'])->all();
        $choices = $atts['choices'] ?? [];

        return "/dashboard/multi?imports={$atts['imports']}&variant=mk1&" . http_build_query(compact('product','yield','recipe','choices'));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
