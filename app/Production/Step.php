<?php

namespace App\Production;

use App\Production\Concerns\CalculatesSteps;
use App\Production\Concerns\CastsToArray;
use App\Production\Concerns\Getters;
use App\Production\Concerns\Setters;

class Step
{
    use Setters, Getters, CalculatesSteps, CastsToArray;

    public static function make($product, $qty, $recipe = null, $parent = null, $overrides = [], $chain = []): static
    {
        $step = (new static)
            ->setProduct($product)
            ->setQty($qty)
            ->setRecipe($recipe)
            ->setOverrides($overrides)
            ->setParent($parent)
            ->setChain($chain);

        $step->calculate();

        return $step;
    }
}
