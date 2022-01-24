<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['product.recipes.product','recipe.product'];


    public function product()
    {
        return $this->belongsTo(Ingredient::class,'ingredient_id');
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
