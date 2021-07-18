<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * A recipe yields a product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Ingredient::class,'product_id');
    }

    /**
     * A recipe yields a building
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * A recipe has many ingredients
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot([
                'base_qty',
            ]);
    }

    public function addIngredient(Ingredient $ingredient, $base_qty)
    {
        $this->ingredients()->attach($ingredient, compact('base_qty'));
    }
}
