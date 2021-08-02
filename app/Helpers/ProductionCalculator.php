<?php

namespace App\Helpers;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function round;

class ProductionCalculator
{
    protected $product;

    protected $qty;

    protected $parts = [];

    protected $byproducts = [];

    protected $recipes = [];

    protected $recipe_models = [];

    protected $yield = [];

    protected $power_usage = [];

    protected $build_cost = [];

    protected $recipe;

    protected $selected_variant;

    public static function calc($ingredient, $qty = null, $recipe = null, $variant = "mk1")
    {
        if (is_string($ingredient)) {
            $ingredient = i($ingredient);
        }

        return (new static($ingredient, $qty, $recipe, $variant))->calculate();
    }

    public function __construct(Ingredient $ingredient, $qty = null, $recipe = null, $variant = "mk1")
    {
        $this->product = $ingredient;

        $this->recipe = $recipe ? r($recipe) : $this->getRecipe($this->product);

        $this->qty = $qty ?? $this->recipe->base_per_min;

        $description = $this->recipe->description ?? "default";

        $this->yield = "{$ingredient->name} [{$description}] ($this->qty per min)";

        $this->selected_variant = $variant;
    }

    protected function calculate()
    {
        $this->parts[$this->getKeyName($this->product)] = $this->qty;

        $this->calculateSubRecipe($this->recipe, $this->qty);

        $this->calculateTotalPowerUsage();

        $this->calculateTotalBuildCost();

        return ProductionStats::make([
            "product" => $this->product->name,
            "recipe" => $this->recipe->description ?? "default",
            "yield" => $this->qty,
            "raw materials" => collect($this->parts)->filter(function ($val, $key) {
                return Str::of($key)->startsWith("1");
            })->all(),
            "parts per minute" => collect($this->parts)->sortKeys()->all(),
            "byproducts per minute" => collect($this->byproducts)->sortKeys()->all(),
            "power_usage_mw" => collect($this->power_usage),
            "build_cost" => $this->build_cost,
            "recipes" => collect($this->recipes)->sortKeys()->all(),
            "recipe_models" => $this->recipe_models
        ]);
    }

    protected function calculateSubRecipe(Recipe $recipe, $qty)
    {
        $recipe_qty = isset($this->recipes[$recipe->product->name]) ?
            $this->recipes[$recipe->product->name]['qty_required'] + $qty : $qty;

        $this->recipe_models[$recipe->product->name] = $recipe;

        $this->recipes[$recipe->product->name] = [
            "recipe" => $recipe->description ?? "default",
            "inputs" => $recipe->ingredients->map(function ($ingredient) {
                    $qty = 1 * $ingredient->pivot->base_qty;

                    return "{$ingredient->name} ({$qty} per min)";
                })->implode(", "),
            "byproducts" => $recipe->byproducts->map(function ($ingredient) {
                    $qty = (int) $ingredient->pivot->base_qty;

                    return "{$ingredient->name} ({$qty} per min)";
                })->implode(", "),
            "qty_required" => 1 * $recipe_qty,
            "base_per_min" => 1 * $recipe->base_per_min,
            "building_overview" => $this->getBuildingOverview($recipe, $recipe_qty),
            "building_details" => $details = $this->getBuildingDetails($recipe, $recipe_qty),
            "selected_variant" => $this->selected_variant ?
                $details->keys()->filter(fn($key) => Str::of($key)->contains($this->selected_variant))->first() :
                $details->keys()->first()
        ];

        if ($recipe->has('byproducts')) {
            $recipe->byproducts->each(function ($ingredient) use ($qty, $recipe) {
                if (isset($this->byproducts[$ingredient->name])) {
                    $this->byproducts[$ingredient->name] += ($qty / $recipe->base_per_min) *
                        $ingredient->pivot->base_qty;
                }
                else {
                    $this->byproducts[$ingredient->name] = ($qty / $recipe->base_per_min) *
                        $ingredient->pivot->base_qty;
                }
            });

            $this->byproducts = collect($this->byproducts)->transform(function ($value, $key) {
                return [$key => (int) $value];
            })->collapse()->all();
        }

        $recipe->ingredients->each(function ($ingredient) use ($qty, $recipe) {

            // how many times per minute we need to make the recipe
            $multiplier = $qty / $recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty;

            // if we have the ingredient in the parts list then increment it, otherwise add it
            if (isset($this->parts[$this->getKeyName($ingredient)])) {
                $this->parts[$this->getKeyName($ingredient)] += $sub_qty;
            }
            else {
                $this->parts[$this->getKeyName($ingredient)] = $sub_qty;
            }

            // continue calculating until we get to all raw ingredients
            if (! $ingredient->isRaw()) {
                $this->calculateSubRecipe($this->getRecipe($ingredient), $sub_qty);
            }
        });
    }

    protected function getRecipe(Ingredient $ingredient)
    {
        $recipe = $ingredient->defaultRecipe();

        if (! $recipe) {
            return new Recipe;
        }

        return $recipe; //->firstWhere('alt_recipe',false);
    }

    protected function getKeyName(Ingredient $ingredient): string
    {
        return "{$ingredient->tier} - {$ingredient->name}";
    }

    public function getBuildingOverview($recipe, $qty)
    {
        return $this->getBuildingDetails($recipe, $qty)->map(function ($details, $building) {
                return [$building => "[x{$details['num_buildings']} {$details['clock_speed']}%] [{$details['power_usage']} MW]"];
            })->collapse();
    }

    public function getBuildingDetails($recipe, $qty)
    {
        return $recipe->building->variants->map(function ($variant) use ($qty, $recipe) {
            // calc number of buildings needed
            $num_buildings = 1 * ceil($qty / $recipe->base_per_min / $variant->multiplier);

            // calc the clock speed for the buildings
            $clock_speed = 1 * round(100 * $qty / $num_buildings / $recipe->base_per_min / $variant->multiplier, 4);

            // calc the power_usage for the buildings
            $power_usage = 1 * round(1 * $num_buildings * $variant->calculatePowerUsage($clock_speed / 100), 6);

            // calc the build cost
            $build_cost = $variant->recipe->map(function ($ingredient) use ($num_buildings) {
                return [$ingredient->name => $ingredient->pivot->qty * $num_buildings];
            })->collapse();

            return [
                "{$recipe->building->name} ($variant->name)" => ['variant' => $variant->name] +
                    compact('num_buildings', 'clock_speed', 'power_usage', 'build_cost'),
            ];
        })->collapse();
    }

    protected function calculateTotalPowerUsage()
    {
        $this->power_usage = collect($this->recipes)
            ->pluck('building_details')
            ->collapse()
            ->values()
            ->groupBy('variant')
            ->map(function ($group) {
                if (! is_numeric($group->sum('power_usage'))) {
                    dd($group->sum('power_usage'));
                }

                return 1 * round($group->sum('power_usage'), 6);
            });
    }

    protected function calculateTotalBuildCost()
    {
        $this->build_cost = collect($this->recipes)
            ->pluck('building_details')
            ->collapse()
            ->values()
            ->groupBy('variant')
            ->map(function ($group) {
                return $group->pluck('build_cost')->map(function ($row) {
                        return $row->map(function ($qty, $ingredient) {
                            return compact('qty', 'ingredient');
                        })->values();
                    })->collapse()->groupBy("ingredient")->map(function ($group) {
                        return $group->sum('qty');
                    });
            });
    }
}
