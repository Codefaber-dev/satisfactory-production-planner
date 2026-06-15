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

        // Forced-recipe fallback for cycles the solver does NOT own — degenerate /
        // unsolvable loops (B43) and direct Step usage without loop detection. Skips
        // products that are solved-loop members (handled by the solver instead).
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
