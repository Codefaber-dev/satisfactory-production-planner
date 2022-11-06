<?php

namespace App\Models;

use App\Favorites\Facades\Favorites;
use ErrorException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

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

    public function scopeOfName(Builder $query, $name, $default = null)
    {
        return $query->whereName($name)->first() ?: $default;
    }

    public function scopeProcessed(Builder $query)
    {
        return $query->where('raw',false);
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

    public function usedInRecipes()
    {
        return Recipe::whereHas('ingredients', function($query) {
            return $query->where('id',$this->id);
        })->get();
    }

    /**
     * Get the default recipe
     *
     * @return \App\Models\Recipe
     */
    public function defaultRecipe(): Recipe
    {
        return Favorites::get($this);
    }

    /**
     * Get the base recipe
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null
     * @throws \ErrorException
     */
    public function baseRecipe(): Recipe
    {
        return Cache::rememberForever("base_recipe.{$this->id}", function(){
            if ( $recipe =  $this->recipes()->firstWhere('alt_recipe',false) )
            return $recipe;

            if ( $recipe = $this->recipes->first() )
                return $recipe;

            throw new ErrorException("Ingredient {$this->name} has no base recipe");
        });
    }

    public function mostEnergyEfficientRecipe()
    {
        return Cache::rememberForever("most_energy_efficient_recipe.{$this->id}", function() {
           return $this->recipes->map(function($recipe){
               try {
                   return [
                       'recipe' => $recipe,
                       'energy' => energy($recipe, false, 1)
                   ];
               }
               catch(InvalidArgumentException $e)
               {}
           })->sortBy('energy')->first()['recipe'];

        });
    }

    public function mostResourceEfficientRecipe()
    {
        return Cache::rememberForever("most_resource_efficient_recipe.{$this->id}", function() {
           return $this->recipes->map(function($recipe){
               try {
                   return [
                       'recipe' => $recipe,
                       'rarity' => rarity($recipe, false, 1)
                   ];
               }
               catch(InvalidArgumentException $e)
               {}
           })->sortBy('rarity')->first()['recipe'];

        });
    }
}
