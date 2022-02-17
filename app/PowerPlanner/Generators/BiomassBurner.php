<?php

namespace App\PowerPlanner\Generators;

class BiomassBurner extends Base
{
    protected $name = "Biomass Burner";

    protected $gross_output = 30; // 30 MW = 1800 MJ / min

    // fuel per min
    protected $fuel = [
        //"Leaves" => 1800 / 15,
        ////"Fabric" => 1800 / 15,
        //"Mycelia" => 1800 / 20,
        //"Flower Petals" => 1800 / 100,
        //"Wood" => 1800 / 100,
        //"Alien Carapace" => 1800 / 250,
        //"Alien Organs" => 1800 / 250,
        "Biomass" => 10,
        "Solid Biofuel" => 4,
        "Packaged Liquid Biofuel" => 1800 / 750,
    ];

    protected $inputs = [];

    protected $build_cost = [
        "Iron Plate" => 15,
        "Iron Rod" => 15,
        "Wire" => 25
    ];

}
