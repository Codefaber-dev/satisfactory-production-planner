<?php

namespace App\Factories\Implementations;

use App\Factories\Contracts\FactoriesContract;
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
        return $this->user->factories;
    }

    public function create(array $attributes): ProductionLine
    {
        return $this->user->factories()->create($attributes);
    }

    public function update($id, array $attributes): ProductionLine
    {
        $line = $this->user->factories()->find($id);

        if (! $line) {
            return new ProductionLine;
        }

        $line->name = (isset($attributes['name']) && !! $attributes['name']) ? $attributes['name'] : $line->name;
        $line->ingredient_id = (isset($attributes['ingredient_id']) && !! $attributes['ingredient_id']) ? $attributes['ingredient_id'] : $line->ingredient_id;
        $line->recipe_id = (isset($attributes['recipe_id']) && !! $attributes['recipe_id']) ? $attributes['recipe_id'] : $line->recipe_id;
        $line->yield = (isset($attributes['yield']) && !! $attributes['yield']) ? $attributes['yield'] : $line->yield;
        $line->notes = (isset($attributes['notes'])) ? $attributes['notes'] : $line->notes;
        $line->imports = (isset($attributes['imports'])) ? $attributes['imports'] : $line->imports;

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
