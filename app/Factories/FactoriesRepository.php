<?php

namespace App\Factories;

use App\Factories\Contracts\FactoriesContract;

class FactoriesRepository
{
    protected FactoriesContract $implementation;

    public function __construct(FactoriesContract $implementation)
    {
        $this->implementation = $implementation;
    }

    public function __call($name, $arguments)
    {
        return $this->implementation->$name(...$arguments);
    }
}
