<?php

namespace App\ProductionBak;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\ProductionBak\Exceptions\CircularReferenceException;
use Illuminate\Support\Str;

class Production
{
    protected $steps;

    /**
     * @var \App\Models\Ingredient
     */
    public $product;

    protected $qty;

    protected $raw_available;

    /**
     * @var \App\Models\Recipe|null
     */
    protected $recipe;

    protected $imports;

    protected $overrides;

    protected $variant;

    public $parsed;

    public $recipes;

    public $raw;

    public $intermediate;

    public $warnings;

    public function __construct(Ingredient $product, $qty, ?Recipe $recipe = null, $imports = null, $variant = "mk1", $overrides = [])
    {
        $this->product = $product;
        $this->qty = $qty;
        $this->recipe = $recipe;
        $this->raw_available = ($raw = request('raw')) ? static::parseRaw($raw) : [];
        $this->overrides = collect($overrides);
        $this->variant = $variant;


        $this->imports ??= request("imports") ? explode(",",request("imports")) : null;

        // calculate the production steps
        $this->calculateProductionSteps($this->overrides);

        // parse the production steps
        $this->doParse();
    }

    protected function calculateProductionSteps($overrides = [])
    {
        $this->overrides = $this->overrides->merge($overrides);

        $this->steps = ProductionStep::make(
            product: $this->product,
            qty: $this->qty,
            recipe: $this->recipe,
            imports: collect($this->imports),
            variant: $this->variant,
            key: Str::random(64),
            overrides: $this->overrides
        );

        //try {
        //
        //}
        //catch(CircularReferenceException $exception)
        //{
        //    $overrides = [
        //        $exception->getProduct() => $exception->getRecipe()
        //    ];
        //
        //    $this->calculateProductionSteps($overrides);
        //}
    }

    public static function make(Ingredient $product, $qty, ?Recipe $recipe = null, $imports = null, $variant = "mk1", $overrides = []): static
    {
        return new static(
            product: $product,
            qty: $qty,
            recipe: $recipe,
            imports: $imports,
            variant: $variant,
            overrides: $overrides
        );
    }

    protected function parse(ProductionStep $steps)
    {
        $this->parsed->push($steps->toArray());

        if($steps->children) {
            $steps->children->each(fn($step) => $this->parse($step));
        }
    }

    /**
     * @return void
     */
    protected function doParse(): void
    {
        $this->parsed = collect();

        $this->parse($this->steps);

        $this->parsed = $this->parsed
            ->sortBy(['tier', 'name'])
            ->groupBy(['tier','name'])
            ->map(function($products, $tier){
                return $products->map(function($recipes, $product) {
                    return [$product => (object) [
                        "outputs" => collect($recipes)->pluck('outputs')->collapse()->toArray(),
                        "production" => $recipes->toArray()
                    ]];
                })->collapse();
            });

        $this->recipes = $this->parsed->skip(1)->map(function($level){
            return $level->map(function($recipes, $product) {
                return [$product => collect($recipes->production)->pluck('recipe')->toArray()];
            })->collapse();
        })->collapse();

        $this->raw = $this->getRawRequired();

        $this->intermediate = $this->getIntermediateRequired();

        $this->warnings = $this->getWarnings();

        dd($this->warnings);

        //dd($this->steps->getCachedOverrides());
    }

    public function getRawRequired()
    {
        return $this->parsed[1]->map(function($prod,$key) {
            return [
                $key => round(1e6 * collect($prod->production)->sum('qty')) / 1e6
            ];
        })->collapse();
    }

    public function getIntermediateRequired()
    {
        return $this->parsed->skip(1)->map(function($level) {
            return $level->map(function($prod,$key) {
                if ($key === $this->product->name) return null;
                return [
                    $key => round(1e6 * collect($prod->production)->sum('qty')) / 1e6
                ];
            })->filter()->collapse();
        })->collapse();
    }

    public function getAdjustedQty()
    {
        return floor($this->qty * $this->ratioOfAvailableRawMaterials());
    }

    protected function ratioOfAvailableRawMaterials(): float
    {
        return $this->getRawRequired()
            ->map(function($required, $key) {
               if (isset($this->raw_available[$key]) && $available = $this->raw_available[$key]) {
                   return $available/$required;
               }
               return null;
            })
            ->filter()
            ->min() ?? 1;
    }

    public static function parseRaw($raw): array
    {
        return collect(explode(",",$raw))
            ->map(function($pair) {
                [$key,$value] = explode(":",$pair);
                return [$key => (int) $value];
            })
            ->collapse()
            ->all();

    }

    public function getMappedImports()
    {
        return collect($this->imports)->map(
            function($key) {
                return $key ? [$key => true] : null;
            }
        )->filter()->collapse();
    }

    public function getWarnings()
    {
        return $this->parsed->map(function($products) {
           return $products->map(function($product) {
               return collect($product->production)->pluck("warning")->filter();
           })->collapse();
        })->collapse()->unique();
    }
}
