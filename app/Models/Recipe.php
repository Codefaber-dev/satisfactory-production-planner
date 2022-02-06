<?php

namespace App\Models;

use App\Favorites\Facades\Favorites;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class Recipe extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'favorite',
        'tier',
        'energy_efficient',
        'resource_efficient'
    ];

    protected $with = [
        'ingredients',
        'byproducts',
        'building',
        'product'
    ];

    public function getTierAttribute()
    {
        return $this->alt_tier ?? $this->product->tier;
    }

    public function getFavoriteAttribute(): bool
    {
        return Favorites::isFavorite($this);
    }

    public function scopeOfName(Builder $query, $name)
    {
        if ($recipe = $query->firstWhere('description',$name))
            return $recipe->load('ingredients');

        return Ingredient::ofName($name)->baseRecipe()->load('ingredients');
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
        $energy = energy($this,false,1) / 1e6 . " MJ";
        $rarity = rarity($this,false,1);

        $energy = $this->isMostEnergyEfficient() ? "\033[32m{$energy}\033[0m" : $energy;
        $rarity = $this->isMostResourceEfficient() ? "\033[32m{$rarity}\033[0m" : $rarity;

        return "[" . (int) $ppm . " ppm] {$description} :{$this->building->name}: ($ingredients)$byproducts [e:{$energy}, r:{$rarity}]";
    }

    public function getEnergyEfficientAttribute()
    {
        return Cache::rememberForever("recipe.{$this->id}.energy_efficient", fn() => $this->isMostEnergyEfficient());
    }

    public function getResourceEfficientAttribute()
    {
        return Cache::rememberForever("recipe.{$this->id}.resource_efficient", fn() => $this->isMostResourceEfficient());
    }

    public function isMostEnergyEfficient()
    {
        return $this->is($this->product->mostEnergyEfficientRecipe());
    }

    public function isMostResourceEfficient()
    {
        return $this->is($this->product->mostResourceEfficientRecipe());
    }
}
