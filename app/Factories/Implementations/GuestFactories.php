<?php

namespace App\Factories\Implementations;

use App\Factories\Contracts\FactoriesContract;
use App\Models\Ingredient;
use App\Models\ProductionLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use function guest_token;
use function http_build_query;

class GuestFactories implements FactoriesContract
{

    public function all(): Collection
    {
        return collect(Redis::hGetAll($this->getCacheTag()))
            ->values()
            ->map(function($json){
                $atts = json_decode($json,true);
                $product = Ingredient::find($atts['ingredient_id']);
                if (! isset($atts['recipe'])) {
                    $atts['recipe'] = $product->baseRecipe();
                }
                if (! isset($atts['recipe_id'])) {
                    $atts['recipe_id'] = $product->baseRecipe()->id;
                }
                $description = $atts['recipe']->description ?? $product->name;
                $params = http_build_query([
                    "factory" => $atts["id"],
                    "imports" => $atts["imports"],
                    "choices" => $atts["choices"] ?? []
                ]);
                $atts['url'] = "/dashboard/{$product->name}/{$atts['yield']}/{$description}/?{$params}";
                return $atts;
            });
    }

    public function create(array $attributes): ProductionLine
    {
        $attributes['recipe_id'] ??= Ingredient::find($attributes['ingredient_id'])->baseRecipe()->id;

        $line = new ProductionLine($attributes);

        $id = Str::random(16);

        $this->set($id, $line);

        return $line;
    }

    public function update($id, array $attributes): ProductionLine
    {
        $line = new ProductionLine($this->find($id));

        $line->name = (isset($attributes['name']) && !! $attributes['name']) ? $attributes['name'] : $line->name;
        $line->ingredient_id = (isset($attributes['ingredient_id']) && !! $attributes['ingredient_id']) ? $attributes['ingredient_id'] : $line->ingredient_id;
        $line->recipe_id = (isset($attributes['recipe_id']) && !! $attributes['recipe_id']) ? $attributes['recipe_id'] : $line->recipe_id;
        $line->yield = (isset($attributes['yield']) && !! $attributes['yield']) ? $attributes['yield'] : $line->yield;
        $line->notes = (isset($attributes['notes'])) ? $attributes['notes'] : $line->notes;
        $line->imports = (isset($attributes['imports'])) ? $attributes['imports'] : $line->imports;

        $line->recipe_id ??= Ingredient::find($line->ingredient_id)->baseRecipe()->id;

        $this->set($id, $line);

        return $line;
    }

    public function find($id): ProductionLine|array|null
    {
        if ( ! $atts = $this->get($id))
            return null;

        return json_decode($atts, true);
    }

    protected function get($id)
    {
        if(! $raw = Redis::hGet($this->getCacheTag(), "factories.$id")) {
            return null;
        }

        return $raw;
    }

    protected function set($id, ProductionLine $line): void
    {
        $arr = array_merge($line->load('product.recipes.product','recipe.product')->toArray(), compact('id'));
        Redis::hSet($this->getCacheTag(), "factories.{$id}", json_encode($arr));
    }

    protected function unset($id): void
    {
        Redis::hDel($this->getCacheTag(), "factories.{$id}");
    }

    protected function getCacheTag() : string
    {
        $guestToken = guest_token();

        return "factories.{$guestToken}";
    }

    public function destroy($id): void
    {
        $this->unset($id);
    }


}
