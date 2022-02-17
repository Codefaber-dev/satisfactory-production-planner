<?php

namespace App\PowerPlanner\Generators;

class FuelGenerator extends Base
{
    protected $name = "Fuel Generator";

    protected $gross_output = 150; // 150 MW = 9000 MJ / min

    // fuel per min
    protected $fuel = [
        "Fuel" => 9000 / 750,
        "Liquid Biofuel" => 9000 / 750,
        "Turbofuel" => 9000 / 2000
    ];

    protected $inputs = [];

    protected $build_cost = [
        "Computer" => 5,
        "Heavy Modular Frame" => 10,
        "Motor" => 15,
        "Rubber" => 50,
        "Quickwire" => 50
    ];
}
