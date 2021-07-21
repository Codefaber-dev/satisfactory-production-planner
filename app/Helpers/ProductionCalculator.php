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

    protected $recipes = [];

    protected $yield = [];

    protected $power_usage = [];

    protected $build_cost = [];

    protected $recipe;

    public static function calc($ingredient, $qty, $recipe = null)
    {
        return (new static(Ingredient::ofName($ingredient), $qty, $recipe))->calculate();
    }

    public function __construct(Ingredient $ingredient, $qty, $recipe = null)
    {
        $this->product = $ingredient;

        $this->qty = $qty;

        $this->recipe = $recipe ? Recipe::ofName($recipe) : $this->getRecipe($this->product);

        $description = $this->recipe->description ?? "default";

        $this->yield = "{$ingredient->name} [{$description}] ($qty ppm)";
    }

    protected function calculate()
    {
        //$this->parts[$this->getKeyName($this->product)] = $this->qty;

        $this->calculateSubRecipe($this->recipe, $this->qty);

        $this->calculateTotalPowerUsage();

        $this->calculateTotalBuildCost();

        return [
            "yield" => $this->yield,
            "raw materials" => collect($this->parts)->filter(function($val,$key) { return Str::of($key)->startsWith("1");})->all(),
            "parts per minute" => collect($this->parts)->sortKeys()->all(),
            "power_usage_mw" => collect($this->power_usage),
            "build_cost" => $this->build_cost,
            "recipes" => collect($this->recipes)->sortKeys()->all(),
        ];
    }

    protected function calculateSubRecipe(Recipe $recipe, $qty)
    {
        $recipe_qty = isset($this->recipes[$recipe->product->name]) ?
            $this->recipes[$recipe->product->name]['qty_required'] + $qty
            : $qty;

            $this->recipes[$recipe->product->name] = [
                "recipe" => $recipe->description ?? "default",
                "inputs" => $recipe->ingredients
                        ->map(function($ingredient){
                            return "{$ingredient->name} ({$ingredient->pivot->base_qty} ppm)";
                        })->implode(", "),
                "qty_required" => 1*$recipe_qty,
                "base_per_min" => 1*$recipe->base_per_min,
                "building_overview" => $this->getBuildingOverview($recipe,$recipe_qty),
                "building_details" => $this->getBuildingDetails($recipe,$recipe_qty)
            ];

        $recipe->ingredients->each(function($ingredient) use ($qty, $recipe) {

            // how many times per minute we need to make the recipe
            $multiplier = $qty/$recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ingredient->pivot->base_qty;

            // if we have the ingredient in the parts list then increment it, otherwise add it
            if (isset($this->parts[$ingredient->name]))
                $this->parts[$this->getKeyName($ingredient)] += $sub_qty;
            else
                $this->parts[$this->getKeyName($ingredient)] = $sub_qty;

            // continue calculating until we get to all raw ingredients
            if (! $ingredient->isRaw() )
                $this->calculateSubRecipe(
                    $this->getRecipe($ingredient),
                    $sub_qty
                );
        });
    }

    protected function getRecipe(Ingredient $ingredient)
    {
        $recipe = $ingredient->defaultRecipe();

        if (! $recipe)
            return new Recipe;


        return $recipe; //->firstWhere('alt_recipe',false);
    }

    protected function getKeyName(Ingredient $ingredient) : string
    {
        return "{$ingredient->tier} - {$ingredient->name}";
    }

    public function getBuildingOverview($recipe,$qty)
    {
        return $this->getBuildingDetails($recipe,$qty)
            ->map(function($details, $building) {
                return [$building => "[x{$details['num_buildings']} {$details['clock_speed']}%] [{$details['power_usage']} MW]"];
            })->collapse();
    }

    public function getBuildingDetails($recipe,$qty)
    {
        return $recipe->building->variants->map( function($variant) use ($qty,$recipe) {
            // calc number of buildings needed
            $num_buildings = 1*ceil( $qty/$recipe->base_per_min/$variant->multiplier );

            // calc the clock speed for the buildings
            $clock_speed = 1*round(100 * $qty / $num_buildings / $recipe->base_per_min / $variant->multiplier, 4);

            // calc the power_usage for the buildings
            $power_usage = 1*round(1* $num_buildings * $variant->calculatePowerUsage($clock_speed/100),2);

            // calc the build cost
            $build_cost = $variant->recipe->map(function($ingredient) use ($num_buildings) {
               return [$ingredient->name => $ingredient->pivot->qty * $num_buildings];
            })->collapse();

            return ["{$recipe->building->name} ($variant->name)" => ['variant' => $variant->name] + compact('num_buildings','clock_speed','power_usage','build_cost')];
        })->collapse();
    }

    protected function calculateTotalPowerUsage()
    {
        $this->power_usage = collect($this->recipes)
            ->pluck('building_details')
            ->collapse()
            ->values()
            ->groupBy('variant')
            ->map(function($group) {
                if (! is_numeric($group->sum('power_usage')))
                    dd($group->sum('power_usage'));

                return 1* round($group->sum('power_usage'),2);
            });
    }

    protected function calculateTotalBuildCost()
    {
        $this->build_cost = collect($this->recipes)
            ->pluck('building_details')
            ->collapse()
            ->values()
            ->groupBy('variant')
            ->map(function($group) {
                return $group->pluck('build_cost')
                    ->map(function($row) {
                        return $row->map(function($qty,$ingredient) {
                            return compact('qty','ingredient');
                        })->values();
                    })
                    ->collapse()
                    ->groupBy("ingredient")
                    ->map(function($group) {
                        return $group->sum('qty');
                    });
            })
            ;
    }
}
