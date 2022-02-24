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

        $this->results = static::groupAndSortResults($this->raw_results);

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

    public function getByproducts()
    {
        return $this->raw_results->pluck('byproducts')->filter()->sumByKey();
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

    public function getOverviews(): Collection
    {
        return $this->results->map(function($tier) {
           return $tier->map(function($product) {
              return $product->production->filter(fn($row) => $row['overview'])->pluck('overviews');
           })->collapse()->filter();
        })->collapse()
            ->map(fn($overview) => [$overview["c100"]["product"] . "|" . $overview["c100"]["recipe"] => [
                "clock" => "c100",
                "selected_variant_name" => $overview["c100"]["selected_variant_name"],
                "overviews" => $overview,
                "overview" => $overview["c100"]
            ]])
            ->collapse();
    }

    public static function groupAndSortResults(Collection $results): Collection
    {
        return $results->sortBy(['tier', 'name'])->groupBy(['tier', 'name'])
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
                                $variant = $group->dataGet("0.variant");
                                $belt_speed = $group->dataGet("0.belt_speed");
                                $overview = $recipe ? BuildingOverview::make($recipe, $qty, $belt_speed, $variant) : null;
                                $overview_150 = $recipe ? BuildingOverview::make($recipe, $qty, $belt_speed, $variant, 150) : null;
                                $overview_200 = $recipe ? BuildingOverview::make($recipe, $qty, $belt_speed, $variant, 200) : null;
                                $overview_250 = $recipe ? BuildingOverview::make($recipe, $qty, $belt_speed, $variant, 250) : null;

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
                                    "overviews" => [
                                        "c100" => $overview ? $overview->toArray() : null,
                                        "c150" => $overview_150 ? $overview_150->toArray() : null,
                                        "c200" => $overview_200 ? $overview_200->toArray() : null,
                                        "c250" => $overview_250 ? $overview_250->toArray() : null,
                                    ],
                                    "power_usage" => $power_usage,
                                    "tier" => $group->dataGet("0.tier"),
                                    "variant" => $group->dataGet("0.variant"),
                                ];
                            })->values(),
                    ]];
                })->collapse();
            });
    }
}
