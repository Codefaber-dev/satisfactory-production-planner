<?php

namespace App\Helpers;

use App\Models\Recipe;

class RawIngredientCalculator
{
    protected $raw = [];

    public static function calc(Recipe $recipe, $use_alts = false, $qty = 1)
    {
        if ( auth()->guest() )
            auth()->loginUsingId(1);

        $c = new static;
        $c->calculate($recipe, $use_alts, $qty);

        return $c->raw;
    }

    public function calculate($recipe, $use_alts, $qty = 1)
    {
        if ( ! $recipe->ingredients )
            dd("recipe has no ingredients",$recipe);

        return $recipe
            ->ingredients
            ->map(function($ingredient) use ($recipe, $qty, $use_alts){
                $amount_needed = $ingredient->pivot->base_qty * $qty/$recipe->base_per_min;

                if ($ingredient->isRaw())
                    return $this->addRaw($ingredient->name, $amount_needed);
                elseif ($use_alts) {
                    return $this->calculate($ingredient->defaultRecipe(), $use_alts, $amount_needed);
                }

                return $this->calculate($ingredient->baseRecipe(), $use_alts, $amount_needed);

            });
    }

    protected function addRaw($name, $qty)
    {
        if (isset($this->raw[$name]))
            $this->raw[$name] += $qty;
        else
            $this->raw[$name] = $qty;
    }
}
