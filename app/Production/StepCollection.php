<?php

namespace App\Production;

use Illuminate\Support\Collection;

class StepCollection extends Collection
{
    public function getRecipe()
    {
        // not multi
        if ($this->count() === 1) {
            return $this->first()->getRecipe();
        }

        return $this->map(fn ($step) => $step->getRecipe());
    }

    public function getOverrides()
    {
        // not multi
        if ($this->count() === 1) {
            return $this->first()->getOverrides()->filter();
        }

        return $this->map(fn ($step) => $step->getOverrides()->filter())->collapse();
    }
}
