<?php

namespace App\PowerPlanner\Generators;

use App\Enums\Ingredient;
use voku\helper\ASCII;

class FuelGenerator extends Base
{
    protected $name = "Fuel Generator";

    protected $gross_output = 250; // 250 MW = 15000 MJ / min

    // fuel per min
    protected $fuel = [
        Ingredient::FUEL->value => 15000 / 750,
        Ingredient::LIQUID_BIOFUEL->value => 15000 / 750,
        Ingredient::TURBOFUEL->value => 15000 / 2000,
        Ingredient::IONIZED_FUEL->value => 15000 / 3600,
        Ingredient::IONIZED_FUEL->value => 15000 / 5000,
    ];

    protected $inputs = [];

    protected $build_cost = [
        Ingredient::MOTOR->value => 15,
        Ingredient::ENCASED_INDUSTRIAL_BEAM->value => 15,
        Ingredient::COPPER_SHEET->value => 30,
        Ingredient::RUBBER->value => 50,
        Ingredient::QUICKWIRE->value => 50,
    ];
}
