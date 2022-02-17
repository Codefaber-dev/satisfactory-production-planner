<?php

namespace App\PowerPlanner\Generators;

class CoalGenerator extends Base
{
    protected $name = "Coal Generator";

    protected $gross_output = 75; // 75 MW = 4500 MJ / min

    // fuel per min
    protected $fuel = [
        "Coal" => 4500 / 300,
        "Compacted Coal" => 4500 / 630,
        "Petroleum Coke" => 4500 / 180
    ];

    protected $inputs = [
        "Water" => 45
    ];

    protected $build_cost = [
        "Reinforced Iron Plate" => 20,
        "Rotor" => 10,
        "Cable" => 30
    ];
}
