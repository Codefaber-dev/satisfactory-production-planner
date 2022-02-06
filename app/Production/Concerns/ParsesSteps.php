<?php

namespace App\Production\Concerns;

use App\Production\Step;

trait ParsesSteps
{
    protected $results;

    protected function parse(Step $steps): void
    {
        $this->results->push($steps->toArray());

        if ($steps->getChildren()) {
            $steps->getChildren()->each(fn($step) => $this->parse($step));
        }
    }

    /**
     * @return void
     */
    protected function doParse(): void
    {
        $this->results = collect();

        $this->parse($this->steps);

        $this->results = $this->results->sortBy(['tier', 'name'])->groupBy(['tier', 'name'])
            ->map(function($products, $tier){
                return $products->map(function($recipes, $product) {
                    return [$product => (object) [
                        "total" => collect($recipes)->sum('qty'),
                        "outputs" => collect($recipes)->pluck('outputs')->collapse()->toArray(),
                        "production" => $recipes->toArray()
                    ]];
                })->collapse();
            });
    }
}
