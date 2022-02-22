<?php

namespace App\MultiFactories;

use App\MultiFactories\Contracts\MultiFactoriesContract;

class MultiFactoriesRepository
{
    protected MultiFactoriesContract $implementation;

    public function __construct(MultiFactoriesContract $implementation)
    {
        $this->implementation = $implementation;
    }

    public function __call($name, $arguments)
    {
        return $this->implementation->$name(...$arguments);
    }
}
