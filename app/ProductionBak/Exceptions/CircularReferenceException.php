<?php

namespace App\ProductionBak\Exceptions;

use App\Models\Ingredient;
use App\Models\Recipe;
use Throwable;

class CircularReferenceException extends \Exception
{
    protected $parent;
    protected $product;
    protected Recipe $recipe;

    /**
     * @throws \App\ProductionBak\Exceptions\CircularReferenceException
     */
    public static function make($parent, $product, Recipe $recipe)
    {
        throw (new static)
            ->setParent($parent)
            ->setProduct($product)
            ->setRecipe($recipe);
    }

    protected function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    protected function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    protected function setRecipe($recipe)
    {
        $this->recipe = $recipe;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getRecipe()
    {
        return $this->recipe;
    }
}
