<?php

namespace App\Production;

use App\Models\Recipe;

class MultiplexerFactory
{
    protected $products;

    protected $yields;

    protected $recipes;

    /**
     * @param $products
     * @param $yields
     */
    public function __construct($products, $yields)
    {
        $this->products = $products;
        $this->yields = $yields;

    }

    public function getRecipe()
    {
        $recipe = Recipe::make([
            'building_id' => 1,
            'product_id' => i('Factory Output')->id,
            'base_yield' => 1,
            'base_per_min' => 1,
            'alt_recipe' => 0,
        ]);

        $recipe->setIngredients(
            collect($this->products)->combine(collect($this->yields))
        );

        return $recipe;
    }
}
