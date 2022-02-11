<?php

namespace App\Production\Concerns;

use App\Models\Recipe;
use PHPUnit\Framework\Assert;

trait Assertions
{
    public function assertOverride(string $ingredient, Recipe|string $recipe): void
    {
        Assert::assertTrue(optional($this->getOverride(i($ingredient)))->is(r($recipe)), "The specified recipe was not found in the overrides.");
    }

    public function assertIntermediateRecipe(string $ingredient, Recipe|string $recipe): void
    {
        Assert::assertTrue(optional($this->getIntermediateRecipe(i($ingredient)))->is(r($recipe)), "The specified recipe was not selected for that ingredient.");
    }

    public function assertRecipe(Recipe|string $recipe): void
    {
        Assert::assertTrue(optional($this->getRecipe())->is(r($recipe)), "The specified recipe was not returned.");
    }

    public function assertImported(string $ingredient): void
    {
        Assert::assertTrue($this->isImported($ingredient), "The specified ingredient is not imported.");
    }
}
