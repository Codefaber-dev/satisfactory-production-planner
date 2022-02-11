<?php

namespace App\Production\Concerns;

use App\Production\Step;

trait ParsesSteps
{
    protected $raw_results;

    protected $results;

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
                    return [$product => (object) [
                        "imported" => collect($recipes)->max('imported'),
                        "overridden" => collect($recipes)->max('overridden'),
                        "total" => collect($recipes)->sum('qty'),
                        "outputs" => collect($recipes)->pluck('outputs')->groupBy('dest')->map->sum('qty')->toArray(),
                        "production" => $recipes->toArray(),
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
        return $this->results[1]->map(function($val, $name) {
            return $val->total;
        });
    }

    public function getIntermediateMaterials()
    {
        return $this->results->skip(1)->map(function($tier) {
            return $tier->map(function($val, $name) {
                if($name !== $this->product->name) {
                    return $val->total;
                }
                return null;
            })->filter();
        })->collapse();
    }
}
