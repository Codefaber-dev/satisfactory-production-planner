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

    public function __construct(Collection|array $choices, Collection|array $overrides, Collection|array|null $favorites, Collection|array $imports, Collection|array $byproducts, string $variant, Collection|array $used_byproducts)
    {
        $this->choices = collect($choices);
        $this->overrides = collect($overrides);
        $this->favorites = Favorites::getMappedFavorites($favorites);
        $this->imports = collect($imports);
        $this->byproducts = collect($byproducts);
        $this->variant = $variant;
        $this->belt_speed = request('belt_speed',780);
        $this->used_byproducts = $used_byproducts;
    }

    public static function make(Collection|array $choices, Collection|array $overrides, Collection|array|null $favorites, Collection|array $imports, Collection|array $byproducts, string $variant, Collection|array $used_byproducts): static
    {
        return new static($choices, $overrides, $favorites, $imports, $byproducts, $variant, $used_byproducts);
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
            used_byproducts: $globals['used_byproducts'] ?? []
        );
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
