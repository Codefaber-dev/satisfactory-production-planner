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

        // V58: a solved-loop member is emitted once and produces at the solver's
        // gross (once known); later encounters are cut in CalculatesSteps.
        if ($globals->isLoopMember($step->getName())) {
            $globals->markEmitted($step->getName());

            if ($globals->hasLoopGross($step->getName())) {
                $step->setQty($globals->getLoopGross($step->getName()));
            }
        }

        // Last-resort terminator for cycles the solver/injection didn't resolve
        // (V69 source injection is preemptive in ProductionCalculator; this remains
        // for direct Step usage without loop detection). Swaps to a loop-free recipe.
        if (! $step->check()) {
            $step->useCompatibleRecipe();
        }

        $step->calculate();

        return $step;
    }
}
