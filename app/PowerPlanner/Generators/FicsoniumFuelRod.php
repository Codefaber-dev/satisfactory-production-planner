<?php

namespace App\PowerPlanner\Generators;

use App\Enums\Ingredient;

class FicsoniumFuelRod extends Base
{
    protected $name = 'Nuclear Power Plant';

    protected $gross_output = 2500;

    protected $fuel = [
        Ingredient::FICSONIUM_FUEL_ROD->value => 1,
    ];

    protected $inputs = [
        Ingredient::WATER->value => 240,
    ];

    protected $waste = [];

    protected $build_cost = [
        Ingredient::SUPERCOMPUTER->value => 10,
        Ingredient::HEAVY_MODULAR_FRAME->value => 25,
        Ingredient::ALCLAD_ALUMINUM_SHEET->value => 100,
        Ingredient::CABLE->value => 200,
        Ingredient::CONCRETE->value => 250,
    ];
}
