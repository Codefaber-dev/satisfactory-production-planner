<?php

namespace App\Production\Concerns;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Production\BuildingOverview;
use App\Production\ProductionGlobals;
use Illuminate\Support\Collection;

trait Setters
{
    protected Ingredient $product;

    protected ?Recipe $recipe = null;

    protected $qty;

    // protected $overrides;
    // protected $favorites;
    // protected $imports;
    public ProductionGlobals $globals;

    // derived parameters
    protected $name;

    protected $parent;

    protected $chain;

    protected $ingredients;

    protected $byproducts;

    protected $warning;

    protected $imported = false;

    protected $all_byproduct = false;

    protected ?BuildingOverview $overview = null;

    public function setProduct($product): static
    {
        $this->product = i($product);
        $this->name = $this->product->name;

        return $this;
    }

    public function setRecipe($recipe = null): static
    {
        if ($this->product->isRaw()) {
            return $this;
        }

        if ($recipe) {
            $this->recipe = r($recipe);
        } else {
            $this->recipe = $this->product->baseRecipe();
        }

        return $this;
    }

    public function setQty($qty = 100): static
    {
        $this->qty = $qty;

        return $this;
    }

    public function setParent($parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function setChain($chain = []): static
    {
        $this->chain = collect($chain);
        $this->chain->push($this->name);

        return $this;
    }

    public function setImports(Collection|array $imports): static
    {
        $this->imports = collect($imports);

        return $this;
    }

    // public function setFavorites(Collection|array $favorites): static
    // {
    //    $this->favorites = collect($favorites);
    //
    //    $this->overrideFavoritesIfNecessary();
    //
    //    return $this;
    // }

    public function setWarning(string $message): static
    {
        $this->warning = $message;

        return $this;
    }

    public function setGlobals(ProductionGlobals $globals): static
    {
        $this->globals = $globals;

        return $this;
    }

    public function setOverview(BuildingOverview $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function useByproduct(string $ingredient, float $qty): void
    {
        $this->globals->useByproduct($ingredient, $qty);

        $this->use_byproduct = $qty;
    }

    /**
     * Forced-recipe fallback (V58/B43) for degenerate loops the linear solver can't
     * own — kept as a fallback, but SKIPPED for products that are solved-loop members
     * (the solver handles those, keeping the user's recipes).
     */
    protected function overrideFavoritesIfNecessary(): void
    {
        $favorites = $this->getFavorites()->values()->pluck('description');

        // The forced override only exists to break a loop. Skip it when the looped
        // product is a solved-loop member (solver owns it) OR is imported (B44 — the
        // import already breaks the loop; forcing a recipe would ignore the import
        // and wrongly raise the "circular dependencies" banner).

        // scenario 1, recycled rubber and recycled plastic
        if ($favorites->contains('Recycled Rubber') && $favorites->contains('Recycled Plastic')
            && ! $this->skipOverride('Rubber')) {
            $this->addOverride('Rubber', r('Rubber'));
        }

        if ($favorites->contains('Recycled Rubber') && $this->getRecipe()->is(r('Recycled Plastic'))
            && ! $this->skipOverride('Rubber')) {
            $this->addOverride('Rubber', r('Rubber'));
        }

        if ($favorites->contains('Recycled Plastic') && $this->getRecipe()->is(r('Recycled Rubber'))
            && ! $this->skipOverride('Plastic')) {
            $this->addOverride('Plastic', r('Plastic'));
        }

        // scenario 2, packaged fuel (degenerate 1:1 Fuel⇄Packaged Fuel loop)
        if (! $this->skipOverride('Packaged Fuel')
            && $this->getIntermediateRecipe(i('Fuel'))->is(r('Unpackage Fuel'))
            && $this->getIntermediateRecipe(i('Packaged Fuel'))->is(r('Packaged Fuel'))) {
            $this->addOverride('Packaged Fuel', r('Diluted Packaged Fuel'));
        }
    }

    // V58/B44: a forced loop-breaking override is unneeded (and wrong) when the
    // product is a solved-loop member or is imported.
    protected function skipOverride(string $product): bool
    {
        return $this->globals->isLoopMember($product) || $this->globals->isImported($product);
    }
}
