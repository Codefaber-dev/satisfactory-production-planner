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

    public static function showRecipes($name)
    {
        return static::ofName($name)
            ->recipes()
            ->with(['ingredients','byproducts'])
            ->get()
            ->map(function($recipe) {
                return [
                    $recipe->description ?? "default" => [
                        "yield" => $recipe->base_per_min,
                        "building" => $recipe->building->name,
                        "ingredients" => $recipe->ingredients->map(fn($ingredient) => "$ingredient->name [{$ingredient->pivot->base_qty} ppm]")->all(),
                        "byproducts" => $recipe->byproducts->map(fn($ingredient) => "$ingredient->name [{$ingredient->pivot->base_qty} ppm]")->all(),
                    ]
                ];
            })
            ->collapse()
            ->all();
    }

    public function getRecipeChoices()
    {
        $default = $this->defaultRecipe()->load(['ingredients','byproducts']);
        $others = $this->recipes()->where('id','<>',$default->id)->get()
            ->map(function($recipe) {
                return [ "id:$recipe->id" => $recipe->getChoiceText()];
            });

        return $others->prepend(["id:$default->id" => $default->getChoiceText()])->collapse();


    }

    public function selectChoice($key)
    {
        return $this->getRecipeChoices()->flip()[$key];
    }

    /**
     * An ingredient has many recipes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class,'product_id');
    }

    /**
     * Get the default recipe
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null
     */
    public function defaultRecipe()
    {
        if ( auth()->check() && $recipe = auth()->user()->favorite_recipes()->firstWhere('ingredient_id',$this->id) )
            return $recipe;

        if ( $recipe =  $this->recipes()->firstWhere('alt_recipe',false) )
            return $recipe;

        return $this->recipes->first();
    }
}
