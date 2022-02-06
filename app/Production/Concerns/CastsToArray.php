<?php

namespace App\Production\Concerns;

trait CastsToArray
{
    public function toArray(): array
    {
        return [
            'tier' => $this->getTier(),
            'name' => $this->getName(),
            'recipe' => $this->getRecipe(),
            'ingredients' => optional($this->getIngredients())->toArray(),
            'byproducts' => optional($this->getByproducts())->toArray(),
            'description' => $this->getDescription(),
            'qty' => $this->getQty(),
            'parent' => $this->getParent(),
            'chain' => $this->getChain()->toArray(),
            'overrides' => $this->getOverrides()->toArray(),
            'children' => optional($this->getChildren())->toArray(),
            'outputs' => [
                ($this->getParent() ?? 'final') => $this->getQty()
            ]
        ];
    }
}
