<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    protected $recipes = [
        "Smelter" => [
            "Iron Ingot" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],

            "Copper Ingot" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Copper Ore" => 30,
                ],
            ],

            "Caterium Ingot" => [
                "base_yield" => 1,
                "base_per_min" => 15,
                "ingredients" => [
                    "Caterium Ore" => 45,
                ],
            ],

            "Aluminum Ingot" => [
                "alt_recipe" => true,
                "description" => "Pure Aluminum Ingot",
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Aluminum Scrap" => 60,
                ],
            ],

        ],

        "Constructor" => [
            "Aluminum Casing" => [
                "base_yield" => 2,
                "base_per_min" => 60,
                "ingredients" => [
                    "Aluminum Ore" => 90,
                ],
            ],
            "Biomass" => [
                "base_yield" => 5,
                "base_per_min" => 60,
                "ingredients" => [
                    "Leaves" => 120,
                ],
                "description" => "Leaves"
            ],
            "Cable" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Wire" => 60,
                ],
            ],
            "Color Cartridge" => [
                "base_yield" => 10,
                "base_per_min" => 75,
                "ingredients" => [
                    "Flower Petals" => 37.5,
                ],
            ],
            "Concrete" => [
                "base_yield" => 1,
                "base_per_min" => 15,
                "ingredients" => [
                    "Limestone" => 45,
                ],
            ],
            "Copper Powder" => [
                "base_yield" => 5,
                "base_per_min" => 50,
                "ingredients" => [
                    "Copper Ingot" => 300,
                ],
            ],
            "Copper Sheet" => [
                "base_yield" => 1,
                "base_per_min" => 10,
                "ingredients" => [
                    "Copper Ingot" => 20,
                ],
            ],
            "Empty Canister" => [
                "base_yield" => 4,
                "base_per_min" => 60,
                "ingredients" => [
                    "Plastic" => 30,
                ],
            ],
            "Empty Fluid Tank" => [
                "base_yield" => 1,
                "base_per_min" => 60,
                "ingredients" => [
                    "Aluminum Ingot" => 60,
                ],
            ],
            "Iron Plate" => [
                "base_yield" => 2,
                "base_per_min" => 20,
                "ingredients" => [
                    "Iron Ingot" => 30,
                ],
            ],
            "Iron Rod" => [
                "base_yield" => 1,
                "base_per_min" => 15,
                "ingredients" => [
                    "Iron Ingot" => 15,
                ],
            ],
            "Power Shard" => [
                "base_yield" => 1,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Green Power Slug" => 7.5,
                ],
            ],
            "Quartz Crystal" => [
                "base_yield" => 3,
                "base_per_min" => 22.5,
                "ingredients" => [
                    "Raw Quartz" => 37.5,
                ],
            ],
            "Quickwire" => [
                "base_yield" => 5,
                "base_per_min" => 60,
                "ingredients" => [
                    "Caterium Ingot" => 12,
                ],
            ],
            "Screw" => [
                "base_yield" => 4,
                "base_per_min" => 40,
                "ingredients" => [
                    "Iron Rod" => 10,
                ],
            ],
            "Silica" => [
                "base_yield" => 5,
                "base_per_min" => 37.5,
                "ingredients" => [
                    "Raw Quartz" => 22.5,
                ],
            ],
            "Solid Biofuel" => [
                "base_yield" => 4,
                "base_per_min" => 60,
                "ingredients" => [
                    "Biomass" => 120,
                ],
            ],
            "Spiked Rebar" => [
                "base_yield" => 1,
                "base_per_min" => 15,
                "ingredients" => [
                    "Iron Rod" => 15,
                ],
            ],
            "Steel Beam" => [
                "base_yield" => 1,
                "base_per_min" => 15,
                "ingredients" => [
                    "Steel Ingot" => 60,
                ],
            ],
            "Steel Pipe" => [
                "base_yield" => 2,
                "base_per_min" => 20,
                "ingredients" => [
                    "Steel Pipe" => 30,
                ],
            ],
            "Wire" => [
                "base_yield" => 2,
                "base_per_min" => 30,
                "ingredients" => [
                    "Copper Ingot" => 15,
                ],
            ],
        ],

        "Assembler" => [
            "AI Limiter" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Copper Sheet" => 25,
                    "Quickwire" => 100,
                ],
            ],
            "Alclad Aluminum Sheet" => [
                "base_yield" => 3,
                "base_per_min" => 30,
                "ingredients" => [
                    "Aluminum Ingot" => 30,
                    "Copper Ingot" => 10,
                ],
            ],
            "Assembly Director System" => [
                "base_yield" => 1,
                "base_per_min" => 0.8,
                "ingredients" => [
                    "Adaptive Control Unit" => 1.5,
                    "Supercomputer" => 0.75,
                ],
            ],
            "Automated Wiring" => [
                "base_yield" => 1,
                "base_per_min" => 2.5,
                "ingredients" => [
                    "Stator" => 2.5,
                    "Cable" => 50,
                ],
            ],
            "Black Powder" => [
                "base_yield" => 1,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Coal" => 7.5,
                    "Sulfur" => 15,
                ],
            ],
            "Circuit Board" => [
                "base_yield" => 1,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Copper Sheet" => 15,
                    "Plastic" => 30,
                ],
            ],
            "Compacted Coal" => [
                "base_yield" => 5,
                "base_per_min" => 25,
                "ingredients" => [
                    "Coal" => 25,
                    "Sulfur" => 25,
                ],
            ],
            "Electromagnetic Control Rod" => [
                "base_yield" => 2,
                "base_per_min" => 4,
                "ingredients" => [
                    "Stator" => 6,
                    "AI Limiter" => 4,
                ],
            ],
            "Encased Industrial Beam" => [
                "base_yield" => 1,
                "base_per_min" => 6,
                "ingredients" => [
                    "Steel Beam" => 24,
                    "Concrete" => 30,
                ],
            ],
            "Encased Plutonium Cell" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Plutonium Pellet" => 10,
                    "Concrete" => 20,
                ],
            ],
            "Fabric" => [
                "base_yield" => 1,
                "base_per_min" => 15,
                "ingredients" => [
                    "Mycelia" => 15,
                    "Biomass" => 75,
                ],
            ],
            "Heat Sink" => [
                "base_yield" => 1,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Alclad Aluminum Sheet" => 37.5,
                    "Copper Sheet" => 22.5,
                ],
            ],
            "Modular Frame" => [
                "base_yield" => 2,
                "base_per_min" => 2,
                "ingredients" => [
                    "Reinforced Iron Plate" => 3,
                    "Iron Rod" => 12,
                ],
            ],
            "Motor" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Rotor" => 10,
                    "Stator" => 10,
                ],
            ],
            "Nobelisk" => [
                "base_yield" => 1,
                "base_per_min" => 3,
                "ingredients" => [
                    "Black Powder" => 15,
                    "Steel Pipe" => 30,
                ],
            ],
            "Pressure Conversion Cube" => [
                "base_yield" => 1,
                "base_per_min" => 1,
                "ingredients" => [
                    "Fused Modular Frame" => 1,
                    "Radio Control Unit" => 2,
                ],
            ],
            "Reinforced Iron Plate" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Iron Plate" => 30,
                    "Screw" => 60,
                ],
            ],
            "Rotor" => [
                "base_yield" => 1,
                "base_per_min" => 4,
                "ingredients" => [
                    "Iron Rod" => 20,
                    "Screw" => 100,
                ],
            ],
            "Smart Plating" => [
                "base_yield" => 1,
                "base_per_min" => 2,
                "ingredients" => [
                    "Reinforced Iron Plate" => 2,
                    "Rotor" => 2,
                ],
            ],
            "Stator" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Steel Pipe" => 15,
                    "Wire" => 40,
                ],
            ],
            "Versatile Framework" => [
                "base_yield" => 2,
                "base_per_min" => 5,
                "ingredients" => [
                    "Modular Frame" => 2.5,
                    "Steel Beam" => 30,
                ],
            ],
        ],

        "Foundry" => [
            "Aluminum Ingot" => [
                "base_yield" => 4,
                "base_per_min" => 60,
                "ingredients" => [
                    "Aluminum Scrap" => 90,
                    "Silica" => 75,
                ],
            ],
            "Steel Ingot" => [
                "base_yield" => 3,
                "base_per_min" => 45,
                "ingredients" => [
                    "Iron Ore" => 45,
                    "Coal" => 45,
                ],
            ],

        ],

        "Refinery" => [
            "Alumina Solution" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Fuel" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Heavy Oil Residue" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Plastic" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Polymer Resin" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Rubber" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Aluminum Scrap" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Sulphuric Acid" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Turbofuel" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Petroleum Coke" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Liquid Biofuel" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
        ],

        "Packager" => [
            "Packaged Alumina Solution" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Fuel" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Heavy Oil Residue" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Liquid Biofuel" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Nitric Acid" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Nitrogen Gas" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Oil" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Sulphuric Acid" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Turbofuel" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Packaged Water" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
        ],

        "Manufacturer" => [
            "Adaptive Control Unit" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Beacon" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Computer" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Crystal Oscillator" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Gas Filter" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Heavy Modular Frame" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "High-Speed Connector" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Iodine Infused Filter" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Magnetic Field Generator" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Modular Engine" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Plutonium Fuel Rod" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Radio Control Unit" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Rifle Cartridge" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Supercomputer" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Thermal Propulsion Rocket" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Turbo Motor" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Quantum Server" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Uranium Fuel Rod" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
        ],

        "Blender" => [
            "Battery" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Cooling System" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Encased Uranium Cell" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Fused Modular Frame" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Nitric Acid" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Non-fissile Uranium" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
        ],

        "Particle Accelerator" => [
            "Plutonium Pellet" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Nuclear Pasta" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
        ],

        "Nuclear Power Plant" => [
            "Uranium Waste" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
            "Plutonium Waste" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
                ],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->recipes)->each(function ($recipes, $building_name) {
            $building = Building::ofName($building_name);

            collect($recipes)->each(function ($recipe, $product_name) use ($building) {
                $product = Ingredient::ofName($product_name);

                $atts = [
                    'building_id' => $building->id,
                    'product_id' => $product->id,
                    'base_yield' => $recipe['base_yield'],
                    'base_per_min' => $recipe['base_per_min'],
                    'alt_recipe' => isset($recipe['alt_recipe']) && (bool) $recipe['alt_recipe'],
                ];

                if (isset($recipe['description'])) {
                    $atts['description'] = $recipe['description'];
                }

                $recipe = Recipe::create($atts);

                collect($recipe['ingredients'])->each(function ($qty, $name) use ($recipe) {
                    $recipe->addIngredient(Ingredient::ofName($name), $qty);
                });
            });
        });
    }
}
