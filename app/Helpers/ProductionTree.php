<?php

namespace App\Helpers;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Str;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\Implementations\StringSort;
use function func_get_args;

class ProductionTree
{
    protected Ingredient $product;

    protected Recipe $recipe;

    protected float $qty;

    protected $overrides;

    protected $imports;

    protected $raws;

    protected $processed;

    protected $sorter;

    protected $sorted;

    public $circularWarning = [];

    public $tree;

    public $amounts;

    public function __construct(Ingredient $product, Recipe $recipe, $qty, $overrides = [], $imports = [])
    {
        $this->product = $product;
        $this->recipe = $recipe;
        $this->qty = $qty;
        $this->tree = [];
        $this->amounts = [];
        $this->overrides = collect($overrides);
        $this->imports = collect($imports);
        $this->raws = Ingredient::where('raw', true)->get()->pluck('name');
        $this->processed = Ingredient::processed()->get();

        $sorted = false;

        while ($sorted === false) {
            try {
                $this->sorter = new StringSort();

                $this->imports->each(function ($name) {
                    $this->sorter->add($name);
                });

                $this->raws->each(function ($name) {
                    $this->sorter->add($name);
                });

                $this->processed->each(function ($product) {
                    $recipe = $this->getRecipe($product);

                    if (! $this->imports->contains($product->name)) {
                        $this->sorter->add($recipe->product->name, $recipe->ingredients->map->name->all());
                    }
                });

                $this->sorted = $this->sorter->sort();
                $sorted = true;
            }
            catch(CircularDependencyException $e)
            {
                $this->circularWarning[] = $e->getMessage();
                $this->imports->add($e->getStart());
            }
        }

        //$this->sorter->setCircularInterceptor(function($products){
        //    $offender = $products[0];
        //    if ( ! $this->imports->contains($offender)) {
        //        $this->imports->add($offender);
        //    }
        //    $this->sorter->add($offender,[]);
        //});


    }

    public static function make(Ingredient $product, Recipe $recipe, $qty, $overrides = [], $imports = []): static
    {
        return new static($product, $recipe, $qty, $overrides, $imports);
    }

    public function doWalk()
    {
        $this->walk($this->product, $this->recipe, $this->qty);
    }

    protected function walk(Ingredient $ingredient, Recipe $recipe, $qty, $previous = [])
    {
        $previous[] = $ingredient->name;
        $amountKey = implode("|", $previous);

        if (isset($this->amounts[$amountKey])) {
            return;
        }

        $this->amounts[$amountKey] = $qty;

        if ($this->imports->contains($ingredient->name)) {
            return;
        }

        $recipe->ingredients->each(function ($ing) use ($recipe, $qty, $previous) {
            // how many times per minute we need to make the recipe
            $multiplier = $qty / $recipe->base_per_min;

            // how much of the ingredient we need to make per minute
            $sub_qty = (float) $multiplier * $ing->pivot->base_qty;

            if (! $ing->isRaw()) {
                $this->walk($ing, $this->getRecipe($ing), $sub_qty, $previous);
            }
            else {
                $previous[] = $ing->name;
                $amountKey = implode("|", $previous);
                $this->amounts[$amountKey] = $sub_qty;
            }
        });
    }

    protected function getRecipe(Ingredient $ingredient)
    {
        $recipe = collect($this->overrides)->filter(function ($recipe) use ($ingredient) {
                    return $recipe->product->is($ingredient);
                })->first() ?? $ingredient->defaultRecipe();

        if (! $recipe) {
            return new Recipe;
        }

        return $recipe; //->firstWhere('alt_recipe',false);
    }

    public function getImports()
    {
        return $this->imports;
    }

    public function getSorted()
    {
        return collect($this->sorted);
    }
}
