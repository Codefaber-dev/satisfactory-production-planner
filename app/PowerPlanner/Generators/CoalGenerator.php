<?php

namespace App\PowerPlanner\Generators;

use App\Enums\Building;
use App\Enums\Ingredient;

class CoalGenerator extends Base
{
    protected $name = Building::COAL_GENERATOR->value;

    protected $gross_output = 75; // 75 MW = 4500 MJ / min

    // fuel per min
    protected $fuel = [
        Ingredient::COAL->value => 4500 / 300,
        Ingredient::COMPACTED_COAL->value => 4500 / 630,
        Ingredient::PETROLEUM_COKE->value => 4500 / 180,
    ];

    protected $inputs = [
        Ingredient::WATER->value => 45,
    ];

    protected $build_cost = [
        Ingredient::REINFORCED_IRON_PLATE->value => 20,
        Ingredient::ROTOR->value => 10,
        Ingredient::CABLE->value => 30,
    ];
}
