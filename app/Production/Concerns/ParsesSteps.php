<?php

namespace App\Production\Concerns;

use App\Production\BuildingOverview;
use App\Production\Step;
use Illuminate\Support\Collection;

trait ParsesSteps
{
    protected $raw_results;

    protected Collection $results;

    protected $slim_results;

    protected function parse(Step $steps): void
    {
        $this->raw_results->push($steps->toArray());

        if ($steps->getChildren()) {
            $steps->getChildren()->each(fn($step) => $this->parse($step));
        }
    }

    /**
     * @return void
     */
    protected function doParse(): void
    {
        $this->raw_results = collect();

        $this->parse($this->steps);

        $this->results = $this->raw_results->sortBy(['tier', 'name'])->groupBy(['tier', 'name'])
            ->map(function($products, $tier){
                return $products->map(function($recipes, $product) {
                    $recipes = collect($recipes);
                    return [$product => (object) [
                        "raw" => i($product)->isRaw(),
                        "imported" => $recipes->max('imported'),
                        "overridden" => $recipes->max('overridden'),
                        "total" => $recipes->sum('qty'),
                        "outputs" => $recipes->pluck('outputs')->groupBy('dest')->map->sum('qty')->toArray(),
                        "production" => $recipes
                            ->groupBy(fn($row) => $row['name'].".".$row['description'])
                            ->map(function($group) {
                                $qty = round($group->sum('qty'),4);
                                $recipe = $group->dataGet("0.recipe");
                                $overview = $recipe ? BuildingOverview::make($recipe, $qty, $this->getSteps()->getBeltSpeed(), $this->variant) : null;

                                $power_usage = $overview ? $overview->details->pluck('power_usage') : null;

                                return [
                                    "byproducts" => $group->crossSumByKey("byproducts"),
                                    "description" => $group->dataGet("0.description"),
                                    "imported" => $group->dataGet("0.imported"),
                                    "ingredients" => $group->crossSumByKey("ingredients"),
                                    "name" => $group->dataGet("0.name"),
                                    "outputs" => $group->pluck("outputs"),
                                    "overridden" => $group->dataGet("0.overridden"),
                                    "overrides" => $group->dataGet("0.overrides"),
                                    "qty" => $qty,
                                    "recipe" => $recipe,
                                    "overview" => $overview ? $overview->toArray() : null,
                                    "power_usage" => $power_usage,
                                    "tier" => $group->dataGet("0.tier"),
                                    "variant" => $group->dataGet("0.variant"),
                                ];
                            })->values(),
                    ]];
                })->collapse();
            });

        //$this->slim_results = $this->raw_results->sortBy(['tier', 'name'])->groupBy(['tier', 'name'])
        //    ->map(function($products, $tier){
        //        return $products->map(function($recipes, $product) {
        //            return [$product => (object) [
        //                "imported" => collect($recipes)->max('imported'),
        //                "overridden" => collect($recipes)->max('overridden'),
        //                "total" => collect($recipes)->sum('qty'),
        //                "outputs" => collect($recipes)->pluck('outputs')->groupBy('dest')->map->sum('qty')->toArray(),
        //                "building_overview" => collect($recipes)->pluck('overview')->all()
        //                //"raw_outputs" => collect($recipes)->pluck('outputs')->toArray(),
        //                //"production" => $recipes->toArray()
        //            ]];
        //        })->collapse();
        //    });
    }

    public function getRawMaterials()
    {
        // confirm we have raw materials
        if (! isset($this->results[1])) {
            return collect();
        }

        return $this->results[1]->map(function($val, $name) {
            return round($val->total,4);
        });
    }

    public function getIntermediateMaterials()
    {
        // skip raw if needed
        $intermediate = isset($this->results[1]) ?
            $this->results->skip(1) :
            $this->results;

        return $intermediate->map(function($tier) {
            return $tier->map(function($val, $name) {
                if($name !== $this->product->name) {
                    return round($val->total,4);
                }
                return null;
            })->filter();
        })->collapse();
    }

    public function getAllMaterials()
    {
        return $this->results->map(function($tier) {
            return $tier->map(function($val, $name) {
                if($name !== $this->product->name) {
                    return round($val->total,4);
                }
                return null;
            })->filter();
        })->collapse();
    }
}
