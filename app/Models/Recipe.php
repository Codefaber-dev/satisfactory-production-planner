<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeOfName(Builder $query, $name)
    {
        return $query->firstWhere('description',$name);
    }

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

    /**
     * A recipe has many byproducts
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function byproducts()
    {
        return $this->belongsToMany(Ingredient::class,'byproduct_recipe')
            ->withPivot([
                'base_qty',
            ]);
    }

    public function addIngredient(Ingredient $ingredient, $base_qty)
    {
        $this->ingredients()->attach($ingredient, compact('base_qty'));
    }

    public function addByproduct(Ingredient $ingredient, $base_qty)
    {
        $this->byproducts()->attach($ingredient, compact('base_qty'));
    }

    public function getChoiceText()
    {
        $ppm = $this->base_per_min;
        $description = $this->description ?? 'default';

        $ingredients = $this->ingredients->map(fn($ingredient) => "$ingredient->name [{$ingredient->pivot->base_qty} ppm]")->join(", ");
        $byproducts = $this->byproducts()->count() ? " [" . $this->byproducts->map(fn($ingredient) => ":bp: $ingredient->name [" . (int) $ingredient->pivot->base_qty . " ppm]")->join(", ") . "]" : "";

        return "[" . (int) $ppm . " ppm] {$description} :{$this->building->name}: ($ingredients)$byproducts";
    }
}
