<?php

namespace App\PowerPlanner\Generators;

use Illuminate\Support\Collection;

interface GeneratorContract
{
    public function getNumRequired(): int;

    public function getFuelRequirements(): Collection;
}
