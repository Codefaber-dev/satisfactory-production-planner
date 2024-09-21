<?php

namespace App\PowerPlanner\Generators;

use App\Enums\Ingredient;
use Illuminate\Validation\Rules\In;
use IntlGregorianCalendar;

class NuclearPowerPlant extends Base
{
    protected $name = "Nuclear Power Plant";

    protected $gross_output = 2500; // 2500 MW = 150000 MJ / min

    // fuel per min
    protected $fuel = [
        Ingredient::FICSONIUM_FUEL_ROD->value => 1,
        Ingredient::URANIUM_FUEL_ROD->value => 1.5e6 / 7.5e6,
        Ingredient::PLUTONIUM_FUEL_ROD->value => 1.5e6 / 15e6,
    ];

    protected $inputs = [
        Ingredient::WATER->value => 240
    ];

    protected $waste = [
        Ingredient::URANIUM_FUEL_ROD->value => [Ingredient::URANIUM_WASTE->value => 10],
        Ingredient::PLUTONIUM_FUEL_ROD->value => [Ingredient::PLUTONIUM_WASTE->value => 1],
    ];

    protected $build_cost = [
        Ingredient::SUPERCOMPUTER->value => 10,
        Ingredient::HEAVY_MODULAR_FRAME->value => 25,
        Ingredient::ALCLAD_ALUMINUM_SHEET->value => 100,
        Ingredient::CABLE->value => 200,
        Ingredient::CONCRETE->value => 250,
    ];
}
