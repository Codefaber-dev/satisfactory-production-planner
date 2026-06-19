<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductionLine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['product.recipes.product', 'recipe.product'];

    protected $casts = [
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
        return 'pl_'.app('Hashids')->encode($this->id);
    }

    public function scopeOfHashId(Builder $query, $hashId)
    {
        $id = app('Hashids')->decode((string) Str::of($hashId)->after('pl_'));

        if (empty($id)) {
            return null;
        }

        return static::find($id[0]);
    }

    public function getPlanUrl(): string
    {
        $description = $this->recipe->description ?? $this->product->name;
        $params = http_build_query([
            'imports' => $this->imports,
            'choices' => $this->choices ?? [],
            'raw_sources' => $this->raw_sources ?? [],
            'import_notes' => $this->import_notes ?? [],
            'auto_package_recycle' => $this->auto_package_recycle ? 1 : 0,
        ]);

        return "/dashboard/{$this->product->name}/{$this->yield}/{$description}/?{$params}";
    }

    public function product()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
