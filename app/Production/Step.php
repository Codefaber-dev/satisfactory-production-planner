<?php

namespace App\Production;

use App\Production\Concerns\Assertions;
use App\Production\Concerns\CalculatesSteps;
use App\Production\Concerns\CastsToArray;
use App\Production\Concerns\Getters;
use App\Production\Concerns\Setters;

class Step
{
    use Setters, Getters, CalculatesSteps, CastsToArray, Assertions;

    public static function make($product, $qty, ProductionGlobals|array $globals, $recipe = null, $parent = null, $chain = []): static
    {
        if (is_array($globals)) {
            $globals['product'] ??= $product;
            $globals = ProductionGlobals::fromArray($globals);
        }

        $step = (new static)
            ->setProduct($product)
            ->setQty($qty)
            ->setGlobals($globals)
            ->setRecipe($recipe)
            ->setParent($parent)
            ->setChain($chain);

        if (! $parent) {
            $step->overrideFavoritesIfNecessary();
        }

        if (! $step->check()) {
            $step->useCompatibleRecipe();
        }

        $step->calculate();

        return $step;
    }
}
