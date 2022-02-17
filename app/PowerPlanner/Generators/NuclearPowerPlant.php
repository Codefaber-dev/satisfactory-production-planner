<?php

namespace App\PowerPlanner\Generators;

class NuclearPowerPlant extends Base
{
    protected $name = "Nuclear Power Plant";

    protected $gross_output = 2500; // 2500 MW = 150000 MJ / min

    // fuel per min
    protected $fuel = [
        "Uranium Fuel Rod" => 1.5e6 / 7.5e6,
        "Plutonium Fuel Rod" => 1.5e6 / 15e6,
    ];

    protected $inputs = [
        "Water" => 300
    ];

    protected $waste = [
        "Uranium Waste" => 10,
        "Plutonium Waste" => 1
    ];

    protected $build_cost = [
        "Concrete" => 250,
        "Heavy Modular Frame" => 25,
        "Supercomputer" => 5,
        "Cable" => 100,
        "Alclad Aluminum Sheet" => 100
    ];
}
