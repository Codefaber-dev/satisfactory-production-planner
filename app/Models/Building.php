<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['variants'];

    public function variants()
    {
        return $this->hasMany(BuildingVariant::class);
    }

    /**
     * B61/V109: a real static finder, NOT a scope — a scope returning null made
     * callScope() fall back to the Builder itself, so a missing building handed
     * callers a query builder that fataled on ->variant()/->name.
     */
    public static function ofName($name): ?self
    {
        return static::whereName($name)->first();
    }

    public function variant($name): ?BuildingVariant
    {
        return $this->variants->firstWhere('name', $name);
    }

    public function v($name): ?BuildingVariant
    {
        return $this->variant($name);
    }
}
