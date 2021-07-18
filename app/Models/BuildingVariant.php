<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingVariant extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function setRecipe($recipe)
    {
        collect($recipe)
            ->each(function($row) {
                $ingredient = Ingredient::ofName($row['ingredient']);

                $this->recipe()->attach([$ingredient->id => ['qty' => $row['qty']]]);
            });
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function recipe()
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot([
                'qty'
            ]);
    }
}
