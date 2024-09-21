<?php

namespace App\Enums;

trait Recipeable
{
    public function toRecipe(): \App\Models\Recipe
    {
        return r($this->value);
    }
}
