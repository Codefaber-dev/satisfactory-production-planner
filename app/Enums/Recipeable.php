<?php

namespace App\Enums;

use App\Models\Recipe;

trait Recipeable
{
    public function toRecipe(): Recipe
    {
        return r($this->value);
    }
}
