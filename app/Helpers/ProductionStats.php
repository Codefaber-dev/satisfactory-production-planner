<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class ProductionStats extends Collection
{
    //public static function make($rows)
    //{
    //    return new static($rows);
    //}

    public function __get($key)
    {
        return $this->get($key);
    }

    public function energy($variant="mk1")
    {
        return (int) ( 1e6 * $this->get("power_usage_mw")->get($variant) / $this->yield );
    }
}
