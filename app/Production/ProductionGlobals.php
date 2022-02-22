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
    protected string $variant;
    protected int $belt_speed;

    public function __construct(Collection|array $choices, Collection|array $overrides, Collection|array|null $favorites, Collection|array $imports, string $variant)
    {
        $this->choices = collect($choices);
        $this->overrides = collect($overrides);
        $this->favorites = ! is_null($favorites) ?
            collect($favorites) :
            Favorites::all()->map(fn($recipe) => [$recipe->product->name => $recipe])->collapse();
        $this->imports = collect($imports);
        $this->variant = $variant;
        $this->belt_speed = request('belt_speed',780);
    }

    public static function make(Collection|array $choices, Collection|array $overrides, Collection|array|null $favorites, Collection|array $imports, string $variant): static
    {
        return new static($choices, $overrides, $favorites, $imports, $variant);
    }

    public static function fromArray(array $globals): static
    {
        return static::make(
            choices: $globals['choices'] ?? [],
            overrides: $globals['overrides'] ?? [],
            favorites: $globals['favorites'] ?? null,
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
}
