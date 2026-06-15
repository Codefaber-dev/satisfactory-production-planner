<?php

namespace App\Production;

use App\Production\Concerns\Assertions;
use App\Production\Concerns\CalculatesSteps;
use App\Production\Concerns\CastsToArray;
use App\Production\Concerns\Getters;
use App\Production\Concerns\Setters;

class Step
{
    use Assertions, CalculatesSteps, CastsToArray, Getters, Setters;

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

        // V58: a member of a solved loop produces at the solver-computed gross,
        // not the parent-propagated demand.
        if ($globals->isLoopMember($step->getName())) {
            $step->setQty($globals->getLoopGross($step->getName()));
        }

        // When the root loop is solved, its recipes are kept as chosen — skip the
        // legacy force-base override (Setters::overrideFavoritesIfNecessary).
        if (! $parent && ! $globals->hasSolvedLoop()) {
            $step->overrideFavoritesIfNecessary();
        }

        if (! $step->check()) {
            $step->useCompatibleRecipe();
        }

        $step->calculate();

        return $step;
    }
}
