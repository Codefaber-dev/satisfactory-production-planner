<?php

namespace App\Production;

use Illuminate\Support\Collection;

class Multiplexer
{
    protected Collection $calcs;

    public function __construct()
    {
        $this->calcs = collect();
    }

    public function add(ProductionCalculator $calc): void
    {
        $this->calcs->push($calc);
    }

    public function getResults()
    {
        $results = $this->calcs->map(fn($calc) => $calc->getRawResults())->collapse();

        return ProductionCalculator::groupAndSortResults($results);
    }

    public function recalculateUsingByproducts()
    {
        $used_byproducts = [];

        $this->calcs->each(function($calc) use (&$used_byproducts) {
           $calc->recalculateUsingByproducts($this->getByproducts(), $used_byproducts);
           $used_byproducts = $calc->getUsedByproducts();
        });
    }

    public function getRawMaterials()
    {
        $results = $this->getResults();

        // confirm we have raw materials
        if (! isset($results[1])) {
            return collect();
        }

        return $results[1]->map(function($val, $name) {
            return round($val->total,4);
        })->filter(fn($val, $name) => $val>0);
    }

    public function getIntermediateMaterials()
    {
        $results = $this->getResults();

        // skip raw if needed
        $intermediate = isset($results[1]) ?
            $results->skip(1) :
            $results;

        return $intermediate->map(function($tier) {
            return $tier->map(function($val, $name) {
                if(! isset($val->outputs['final'])) {
                    return round($val->total,4);
                }
                return null;
            })->filter();
        })->collapse();
    }

    public function getAllMaterials()
    {
        return $this->getResults()->map(function($tier) {
            return $tier->map(function($val, $name) {
                //if(! isset($val->outputs['final'])) {
                    return round($val->total,4);
                //}
                //return null;
            })->filter();
        })->collapse();
    }

    public function getFinals()
    {
        return $this->calcs->map(fn($calc) => $calc->getSteps()->toArray());
    }

    public function getRecipes()
    {
        return $this->calcs->map(fn($calc) => $calc->getSteps()->getRecipe());
    }

    public function getOverrides()
    {
        return $this->calcs->map(fn($calc) => $calc->getSteps()->getOverrides()->filter())->collapse();
    }

    public function getByproducts()
    {
        return $this->calcs->map(fn($calc) => $calc->getByproducts())->sumByKey();
    }

    public function getByproductsUsed()
    {
        return $this->calcs->map(fn($calc) => $calc->getByproductsUsed())->sumByKey();
    }

    public function getOverviews()
    {
        return $this->getResults()->map(function($tier) {
           return $tier->map(function($product) {
              return $product->production->filter(fn($row) => isset($row['overview']))->pluck('overviews');
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

    public function ratioOfAvailableRawMaterials(): float
    {
        $raw_available = ($raw = request('raw')) ? static::parseRaw($raw) : [];

        return $this->getRawMaterials()
            ->map(function($required, $key) use ($raw_available) {
               if (isset($raw_available[$key]) && $available = $raw_available[$key]) {
                   return $available/$required;
               }
               return null;
            })
            ->filter()
            ->min() ?? 1;
    }

    protected static function parseRaw($raw): array
    {
        return collect(explode(",",$raw))
            ->map(function($pair) {
                [$key,$value] = explode(":",$pair);
                return [$key => (int) $value];
            })
            ->collapse()
            ->all();
    }
}
