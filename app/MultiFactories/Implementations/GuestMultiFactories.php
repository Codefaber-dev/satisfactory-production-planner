<?php

namespace App\MultiFactories\Implementations;

use App\MultiFactories\Contracts\MultiFactoriesContract;
use App\Models\Ingredient;
use App\Models\MultiProductionLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use function guest_token;

class GuestMultiFactories implements MultiFactoriesContract
{

    public function all(): Collection
    {
        return collect(Redis::hGetAll($this->getCacheTag()))
            ->values()
            ->map(function($json){
                $atts = json_decode($json,true);
                $outputs = collect($atts['outputs']);
                $product = $outputs->map(fn($output) => $output['product']['name'])->all();
                $yield = $outputs->map(fn($output) => $output['yield'])->all();
                $recipe = $outputs->map(fn($output) => $output['recipe']['description'] ?? $output['product']['name'])->all();
                $choices = $atts['choices'] ?? [];

                $atts["url"] = "/dashboard/multi?multiFactory={$atts['id']}&imports={$atts['imports']}&variant=mk1&" . http_build_query(compact('product','yield','recipe','choices'));
                //$product = Ingredient::find($atts['ingredient_id']);
                //if (! isset($atts['recipe'])) {
                //    $atts['recipe'] = $product->baseRecipe();
                //}
                //if (! isset($atts['recipe_id'])) {
                //    $atts['recipe_id'] = $product->baseRecipe()->id;
                //}
                return $atts;
            });
    }

    public function create(array $attributes): MultiProductionLine
    {
        $line = new MultiProductionLine($attributes);

        $id = Str::random(16);

        $this->set($id, $line);

        return $line;
    }

    public function update($id, array $attributes): MultiProductionLine
    {
        $line = new MultiProductionLine($this->find($id));

        $line->name = (isset($attributes['name']) && !! $attributes['name']) ? $attributes['name'] : $line->name;
        $line->outputs = (isset($attributes['outputs']) && !! $attributes['outputs']) ? $attributes['outputs'] : $line->outputs;
        $line->notes = (isset($attributes['notes'])) ? $attributes['notes'] : $line->notes;
        $line->imports = (isset($attributes['imports'])) ? $attributes['imports'] : $line->imports;
        $line->choices = (isset($attributes['choices'])) ? $attributes['choices'] : $line->choices;
        $line->is_shared = (isset($attributes['is_shared'])) ? $attributes['is_shared'] : $line->is_shared;

        $this->set($id, $line);

        return $line;
    }

    public function find($id): MultiProductionLine|array|null
    {
        if ( ! $atts = $this->get($id))
            return null;

        return json_decode($atts, true);
    }

    protected function get($id)
    {
        if(! $raw = Redis::hGet($this->getCacheTag(), "multi-factories.$id")) {
            return null;
        }

        return $raw;
    }

    protected function set($id, MultiProductionLine $line): void
    {
        $atts = array_merge($line->toArray(), compact('id'));
        $this->unset($id);
        Redis::hSet($this->getCacheTag(), "multi-factories.{$id}", json_encode($atts));
    }

    protected function unset($id): void
    {
        Redis::hDel($this->getCacheTag(), "multi-factories.{$id}");
    }

    protected function getCacheTag() : string
    {
        $guestToken = guest_token();

        return "multi-factories.{$guestToken}";
    }

    public function destroy($id): void
    {
        $this->unset($id);
    }


}
