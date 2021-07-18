<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ingredient extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'raw' => 'bool',
        'tier' => 'int'
    ];

    /**
     * Is it a raw resource
     *
     * @return bool
     */
    public function isRaw(): bool
    {
        return (bool) $this->raw;
    }

    public function scopeOfName(Builder $query, $name)
    {
        return $query->whereName($name)->first();
    }
}
