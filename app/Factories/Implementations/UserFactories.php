<?php

namespace App\Factories\Implementations;

use App\Factories\Contracts\FactoriesContract;
use App\Models\Ingredient;
use App\Models\ProductionLine;
use App\Models\User;
use Illuminate\Support\Collection;

class UserFactories implements FactoriesContract
{
    protected User $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function all(): Collection
    {
        return $this->user->factories->map(function ($factory) {
            $atts = $factory->toArray();
            $description = $atts['recipe']->description ?? $factory->product->name;
            $params = http_build_query([
                "factory" => $atts["id"],
                "imports" => $atts["imports"],
                "choices" => $atts["choices"] ?? [],
            ]);
            $atts['url'] = "/dashboard/{$factory->product->name}/{$atts['yield']}/{$description}/?{$params}";

            return $atts;
        });
    }

    public function create(array $attributes): ProductionLine
    {
        $attributes['recipe_id'] ??= Ingredient::find($attributes['ingredient_id'])->baseRecipe()->id;

        return $this->user->factories()->create($attributes);
    }

    public function update($id, array $attributes): ProductionLine
    {
        $line = $this->user->factories()->find($id);

        if (! $line) {
            return new ProductionLine;
        }

        $line->name = (isset($attributes['name']) && ! ! $attributes['name']) ? $attributes['name'] : $line->name;
        $line->ingredient_id = (isset($attributes['ingredient_id']) && ! ! $attributes['ingredient_id']) ?
            $attributes['ingredient_id'] : $line->ingredient_id;
        $line->recipe_id = (isset($attributes['recipe_id']) && ! ! $attributes['recipe_id']) ?
            $attributes['recipe_id'] : $line->recipe_id;
        $line->yield = (isset($attributes['yield']) && ! ! $attributes['yield']) ? $attributes['yield'] : $line->yield;
        $line->notes = (isset($attributes['notes'])) ? $attributes['notes'] : $line->notes;
        $line->imports = (isset($attributes['imports'])) ? $attributes['imports'] : $line->imports;
        $line->choices = (isset($attributes['choices'])) ? $attributes['choices'] : $line->choices;
        $line->is_shared = (isset($attributes['is_shared'])) ? $attributes['is_shared'] : $line->is_shared;

        $line->recipe_id ??= Ingredient::find($line->ingredient_id)->baseRecipe()->id;

        $line->save();

        return $line;
    }

    public function find($id): ProductionLine|array|null
    {
        return $this->user->factories()->find($id);
    }

    public function destroy($id): void
    {
        optional($this->user->factories()->find($id))->delete();
    }
}
