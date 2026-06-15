<?php

namespace App\Production;

use App\Favorites\Facades\Favorites;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;

class ProductionGlobals
{
    protected Collection $choices;

    protected Collection $overrides;

    protected Collection $favorites;

    protected Collection $imports;

    protected Collection $byproducts;

    protected Collection $used_byproducts;

    protected string $variant;

    protected int $belt_speed;

    protected float $cost_multiplier = 1.0;

    // V58: solver-computed gross output per loop member (product => qty/min). Empty unless a root loop was solved.
    protected Collection $loop_gross;

    public function __construct(Collection|array $choices, Collection|array $overrides, Collection|array|null $favorites, Collection|array $imports, Collection|array $byproducts, string $variant, Collection|array $used_byproducts, Collection|array $loop_gross = [])
    {
        $this->choices = collect($choices);
        $this->overrides = collect($overrides);
        $this->favorites = Favorites::getMappedFavorites($favorites);
        $this->imports = collect($imports);
        $this->byproducts = collect($byproducts);
        $this->variant = $variant;
        $this->belt_speed = request('belt_speed', 780);
        $this->cost_multiplier = max(0.1, min(10.0, (float) request('cost_multiplier', 1.0)));
        $this->used_byproducts = collect($used_byproducts);
        $this->loop_gross = collect($loop_gross);
    }

    public static function make(Collection|array $choices, Collection|array $overrides, Collection|array|null $favorites, Collection|array $imports, Collection|array $byproducts, string $variant, Collection|array $used_byproducts, Collection|array $loop_gross = []): static
    {
        return new static($choices, $overrides, $favorites, $imports, $byproducts, $variant, $used_byproducts, $loop_gross);
    }

    public static function fromArray(array $globals): static
    {
        return static::make(
            choices: $globals['choices'] ?? [],
            overrides: $globals['overrides'] ?? [],
            favorites: $globals['favorites'] ?? null,
            imports: $globals['imports'] ?? [],
            byproducts: $globals['byproducts'] ?? [],
            variant: $globals['variant'] ?? 'mk1',
            used_byproducts: $globals['used_byproducts'] ?? [],
            loop_gross: $globals['loop_gross'] ?? []
        );
    }

    public function isLoopMember(string $name): bool
    {
        return $this->loop_gross->has($name);
    }

    public function getLoopGross(string $name): float
    {
        return (float) $this->loop_gross->get($name);
    }

    public function hasSolvedLoop(): bool
    {
        return $this->loop_gross->isNotEmpty();
    }

    public function getCostMultiplier(): float
    {
        return $this->cost_multiplier;
    }

    public function getVariant(): string
    {
        return $this->variant;
    }

    public function getBeltSpeed(): int
    {
        return $this->belt_speed;
    }

    public function getImports(): ?Collection
    {
        return $this->imports;
    }

    public function isImported(string $ingredient): bool
    {
        return $this->getImports()->contains($ingredient);
    }

    public function isByproduct(string $ingredient): bool
    {
        return $this->getByproducts()->has($ingredient) && $this->getByproduct($ingredient) > 0;
    }

    public function isOverride(string $ingredient): bool
    {
        return isset($this->overrides[$ingredient]);
    }

    public function getByproducts(): ?Collection
    {
        return $this->byproducts;
    }

    public function getByproduct(string $ingredient)
    {
        return $this->byproducts->get($ingredient);
    }

    public function getChoices(): ?Collection
    {
        return $this->choices;
    }

    public function getChoice(Ingredient $ingredient): ?Recipe
    {
        return $this->choices->get($ingredient->name);
    }

    public function getFavorites(): ?Collection
    {
        return $this->favorites;
    }

    public function getFavorite(Ingredient $ingredient): ?Recipe
    {
        return $this->favorites->get($ingredient->name);
    }

    public function getOverrides(): ?Collection
    {
        return $this->overrides;
    }

    public function getOverride(Ingredient $ingredient): ?Recipe
    {
        return $this->overrides[$ingredient->name] ?? null;
    }

    public function addOverride(string $ingredient, Recipe $override): void
    {
        $this->overrides[$ingredient] = $override;
    }

    public function useByproduct($name, $qty)
    {
        if (! isset($this->used_byproducts[$name])) {
            $this->used_byproducts[$name] = 0;
        }

        $this->used_byproducts[$name] += $qty;
    }

    public function availableByproduct($name)
    {
        if (! $this->isByproduct($name)) {
            return 0;
        }

        $total = $this->getByproduct($name);
        $used = $this->used_byproducts[$name] ?? 0;

        return $total - $used;
    }
}
