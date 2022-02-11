<?php

namespace App\Production;


use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;

class ProductionGlobals
{
    protected Collection $overrides;
    protected Collection $favorites;
    protected Collection $imports;
    protected string $variant;
    protected int $belt_speed;

    public function __construct(Collection|array $overrides, Collection|array $favorites, Collection|array $imports, string $variant)
    {
        $this->overrides = collect($overrides);
        $this->favorites = collect($favorites);
        $this->imports = collect($imports);
        $this->variant = $variant;
        $this->belt_speed = request('belt_speed',780);
    }

    public static function make(Collection|array $overrides, Collection|array $favorites, Collection|array $imports, string $variant): static
    {
        return new static($overrides, $favorites, $imports, $variant);
    }

    public static function fromArray(array $globals): static
    {
        return static::make(
            overrides: $globals['overrides'] ?? [],
            favorites: $globals['favorites'] ?? [],
            imports: $globals['imports'] ?? [],
            variant: $globals['variant'] ?? 'mk1'
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

    public function isOverride(string $ingredient): bool
    {
        return isset($this->overrides[$ingredient]);
    }

    public function getFavorites(): ?Collection
    {
        return $this->favorites;
    }

    public function getFavorite(Ingredient $ingredient): ?Recipe
    {
        return $this->favorites[$ingredient->name] ?? null;
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
}
