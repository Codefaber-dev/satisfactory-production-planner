<?php

namespace App\MultiFactories\Implementations;

use App\MultiFactories\Contracts\MultiFactoriesContract;
use App\Models\Ingredient;
use App\Models\MultiProductionLine;
use App\Models\User;
use Illuminate\Support\Collection;

class UserMultiFactories implements MultiFactoriesContract
{
    protected User $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function all(): Collection
    {
        return $this->user->multiFactories->map(function($mf) {
            $atts = $mf->toArray();
            $outputs = collect($atts['outputs']);
            $product = $outputs->map(fn($output) => $output['product']['name'])->all();
            $yield = $outputs->map(fn($output) => $output['yield'])->all();
            $recipe = $outputs->map(fn($output) => $output['recipe']['description'] ?? $output['product']['name'])->all();
            $choices = $atts['choices'] ?? [];

            $atts["url"] = "/dashboard/multi?multiFactory={$atts['id']}&imports={$atts['imports']}&variant=mk1&" . http_build_query(compact('product','yield','recipe','choices'));

            return $atts;
        });
    }

    public function create(array $attributes): MultiProductionLine
    {
        return $this->user->multiFactories()->create($attributes);
    }

    public function update($id, array $attributes): MultiProductionLine
    {
        $line = $this->user->multiFactories()->find($id);

        if (! $line) {
            return new MultiProductionLine;
        }

        $line->name = (isset($attributes['name']) && !! $attributes['name']) ? $attributes['name'] : $line->name;
        $line->outputs = (isset($attributes['outputs']) && !! $attributes['outputs']) ? $attributes['outputs'] : $line->outputs;
        $line->notes = (isset($attributes['notes'])) ? $attributes['notes'] : $line->notes;
        $line->imports = (isset($attributes['imports'])) ? $attributes['imports'] : $line->imports;
        $line->choices = (isset($attributes['choices'])) ? $attributes['choices'] : $line->choices;

        $line->save();

        return $line;
    }

    public function find($id): MultiProductionLine|array|null
    {
        return $this->user->multiFactories()->find($id);
    }

    public function destroy($id): void
    {
        optional($this->user->multiFactories()->find($id))->delete();
    }


}
