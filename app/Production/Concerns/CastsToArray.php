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
            'ingredients' => optional($this->getIngredients())->toArray() ?? [],
            'byproducts' => optional($this->getByproducts())->toArray(),
            'description' => $this->getDescription(),
            'qty' => $this->getQty(),
            'parent' => $this->getParent(),
            'chain' => $this->getChain()->toArray(),
            'overrides' => $this->getOverrides()->toArray(),
            'children' => optional($this->getChildren())->toArray(),
            'outputs' => [
                'dest' => ($this->getParent() ?? 'final'),
                'qty' => $this->getQty(),
                'byproduct_qty' => $this->use_byproduct ?? 0,
            ],
            'imported' => $this->isImported($this->getName()),
            'all_byproduct' => $this->all_byproduct,
            'overridden' => $this->isOverride($this->getName()),
            'overview' => optional($this->getOverview())->toArray() ?? [],
            'power_usage' => $this->getPowerUsage(),
            'total_energy' => $this->getTotalEnergy(),
            'variant' => $this->getVariant(),
            'belt_speed' => $this->getBeltSpeed(),
        ];
    }
}
