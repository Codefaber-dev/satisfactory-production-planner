<?php

namespace App\Favorites;

use App\Favorites\Contracts\FavoritesContract;

class FavoritesRepository
{
    protected FavoritesContract $implementation;

    public function __construct(FavoritesContract $implementation)
    {
        $this->implementation = $implementation;
    }

    public function __call($name, $arguments)
    {
        return $this->implementation->$name(...$arguments);
    }
}
