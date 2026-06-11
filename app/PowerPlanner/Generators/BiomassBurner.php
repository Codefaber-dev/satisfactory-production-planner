<?php

namespace App\PowerPlanner\Generators;

use App\Enums\Building;
use App\Enums\Ingredient;

class BiomassBurner extends Base
{
    protected $name = Building::BIOMASS_BURNER->value;

    protected $gross_output = 30; // 30 MW = 1800 MJ / min

    // fuel per min
    protected $fuel = [
        // "Leaves" => 1800 / 15,
        // //"Fabric" => 1800 / 15,
        // "Mycelia" => 1800 / 20,
        // "Flower Petals" => 1800 / 100,
        // "Wood" => 1800 / 100,
        // "Alien Carapace" => 1800 / 250,
        // "Alien Organs" => 1800 / 250,
        Ingredient::BIOMASS->value => 10,
        Ingredient::SOLID_BIOFUEL->value => 4,
        Ingredient::PACKAGED_LIQUID_BIOFUEL->value => 1800 / 750,
    ];

    protected $inputs = [];

    protected $build_cost = [
        Ingredient::IRON_PLATE->value => 15,
        Ingredient::IRON_ROD->value => 15,
        Ingredient::WIRE->value => 25,
    ];
}
