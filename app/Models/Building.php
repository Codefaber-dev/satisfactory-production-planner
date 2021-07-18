<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function variants()
    {
        return $this->hasMany(BuildingVariant::class);
    }

    public function scopeOfName(Builder $query, $name)
    {
        return $query->whereName($name)->first();
    }
}
