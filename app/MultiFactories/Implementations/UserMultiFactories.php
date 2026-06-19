<?php

namespace App\MultiFactories\Implementations;

use App\Models\MultiProductionLine;
use App\Models\User;
use App\MultiFactories\Contracts\MultiFactoriesContract;
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
        return $this->user->multiFactories->map(function ($mf) {
            $atts = $mf->toArray();
            $outputs = collect($atts['outputs']);
            $product = $outputs->map(fn ($output) => $output['product']['name'])->all();
            $yield = $outputs->map(fn ($output) => $output['yield'])->all();
            $recipe = $outputs->map(fn ($output) => $output['recipe']['description'] ?? $output['product']['name'])->all();
            $choices = $atts['choices'] ?? [];
            $raw_sources = $atts['raw_sources'] ?? [];
            $import_notes = $atts['import_notes'] ?? [];
            $auto_package_recycle = ! empty($atts['auto_package_recycle']) ? 1 : 0;

            $atts['url'] = "/dashboard/multi?multiFactory={$atts['id']}&imports={$atts['imports']}&variant=mk1&".http_build_query(compact('product', 'yield', 'recipe', 'choices', 'raw_sources', 'import_notes', 'auto_package_recycle'));

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

        $line->name = (isset($attributes['name']) && (bool) $attributes['name']) ? $attributes['name'] : $line->name;
        $line->outputs = (isset($attributes['outputs']) && (bool) $attributes['outputs']) ? $attributes['outputs'] : $line->outputs;
        $line->notes = (isset($attributes['notes'])) ? $attributes['notes'] : $line->notes;
        $line->imports = (isset($attributes['imports'])) ? $attributes['imports'] : $line->imports;
        $line->choices = (isset($attributes['choices'])) ? $attributes['choices'] : $line->choices;
        $line->raw_sources = (isset($attributes['raw_sources'])) ? $attributes['raw_sources'] : $line->raw_sources;
        $line->import_notes = (isset($attributes['import_notes'])) ? $attributes['import_notes'] : $line->import_notes;
        $line->auto_package_recycle = (isset($attributes['auto_package_recycle'])) ? $attributes['auto_package_recycle'] : $line->auto_package_recycle;
        $line->is_shared = (isset($attributes['is_shared'])) ? $attributes['is_shared'] : $line->is_shared;

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
