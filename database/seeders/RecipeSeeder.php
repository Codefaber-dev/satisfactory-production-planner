<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class RecipeSeeder extends Seeder
{
    protected $recipes = [
        // alts done
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
                "description" => "Pure Aluminum Ingot",
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Aluminum Scrap" => 60,
                ],
                "alt_recipe" => true,
            ],

        ],

        // alts done
        "Constructor" => [
            "Aluminum Casing" => [
                "base_yield" => 2,
                "base_per_min" => 60,
                "ingredients" => [
                    "Aluminum Ingot" => 90,
                ],
            ],

            "Biomass" => [
                [
                    "description" => "Biomass (Alien Carapace)",
                    "base_yield" => 100,
                    "base_per_min" => 1500,
                    "ingredients" => [
                        "Alien Carapace" => 15,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Biomass (Alien Organs)",
                    "base_yield" => 200,
                    "base_per_min" => 1500,
                    "ingredients" => [
                        "Alien Organs" => 7.5,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Biomass (Leaves)",
                    "base_yield" => 5,
                    "base_per_min" => 60,
                    "ingredients" => [
                        "Leaves" => 120,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Biomass (Mycelia)",
                    "base_yield" => 10,
                    "base_per_min" => 150,
                    "ingredients" => [
                        "Mycelia" => 150,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Biomass (Wood)",
                    "base_yield" => 20,
                    "base_per_min" => 300,
                    "ingredients" => [
                        "Wood" => 60,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Cable" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Wire" => 60,
                ],
            ],
            "Coal" => [
                [
                    "description" => "Biocoal",
                    "base_yield" => 6,
                    "base_per_min" => 45,
                    "ingredients" => [
                        "Biomass" => 37.5,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Charcoal",
                    "base_yield" => 10,
                    "base_per_min" => 150,
                    "ingredients" => [
                        "Wood" => 15,
                    ],
                    "alt_recipe" => true,
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
                [
                    "base_yield" => 4,
                    "base_per_min" => 60,
                    "ingredients" => [
                        "Plastic" => 30,
                    ],
                ],
                [
                    "description" => "Steel Canister",
                    "base_yield" => 2,
                    "base_per_min" => 40,
                    "ingredients" => [
                        "Steel Ingot" => 60,
                    ],
                    "alt_recipe" => true,
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
                [
                    "base_yield" => 1,
                    "base_per_min" => 15,
                    "ingredients" => [
                        "Iron Ingot" => 15,
                    ],
                ],
                [
                    "description" => "Steel Rod",
                    "base_yield" => 4,
                    "base_per_min" => 48,
                    "ingredients" => [
                        "Steel Ingot" => 12,
                    ],
                    "alt_recipe" => true,
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
                [
                    "base_yield" => 4,
                    "base_per_min" => 40,
                    "ingredients" => [
                        "Iron Rod" => 10,
                    ],
                ],
                [
                    "description" => "Cast Screw",
                    "base_yield" => 20,
                    "base_per_min" => 50,
                    "ingredients" => [
                        "Iron Ingot" => 12.5,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Steel Screw",
                    "base_yield" => 52,
                    "base_per_min" => 260,
                    "ingredients" => [
                        "Steel Beam" => 5,
                    ],
                    "alt_recipe" => true,
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
                    "Steel Ingot" => 30,
                ],
            ],
            "Wire" => [
                [
                    "base_yield" => 2,
                    "base_per_min" => 30,
                    "ingredients" => [
                        "Copper Ingot" => 15,
                    ],
                ],
                [
                    "description" => "Caterium Wire",
                    "base_yield" => 8,
                    "base_per_min" => 120,
                    "ingredients" => [
                        "Caterium Ingot" => 15,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Iron Wire",
                    "base_yield" => 9,
                    "base_per_min" => 22.5,
                    "ingredients" => [
                        "Iron Ingot" => 12.5,
                    ],
                    "alt_recipe" => true,
                ],
            ],
        ],

        // alts done
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
            "Aluminum Casing" => [
                "description" => "Alclad Casing",
                "base_yield" => 15,
                "base_per_min" => 112.5,
                "ingredients" => [
                    "Aluminum Ingot" => 150,
                    "Copper Ingot" => 75,
                ],
                "alt_recipe" => true,
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
                [
                    "base_yield" => 1,
                    "base_per_min" => 7.5,
                    "ingredients" => [
                        "Coal" => 7.5,
                        "Sulfur" => 15,
                    ],
                ],
                [
                    "description" => "Fine Black Powder",
                    "base_yield" => 4,
                    "base_per_min" => 15,
                    "ingredients" => [
                        "Compacted Coal" => 3.75,
                        "Sulfur" => 7.5,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Cable" => [
                [
                    "description" => "Insulated Cable",
                    "base_yield" => 20,
                    "base_per_min" => 100,
                    "ingredients" => [
                        "Wire" => 45,
                        "Rubber" => 30,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Quickwire Cable",
                    "base_yield" => 11,
                    "base_per_min" => 27.5,
                    "ingredients" => [
                        "Quickwire" => 7.5,
                        "Rubber" => 5,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Circuit Board" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 7.5,
                    "ingredients" => [
                        "Copper Sheet" => 15,
                        "Plastic" => 30,
                    ],
                ],
                [
                    "description" => "Caterium Circuit Board",
                    "base_yield" => 7,
                    "base_per_min" => 8.8,
                    "ingredients" => [
                        "Quickwire" => 37.5,
                        "Plastic" => 12.5,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Electrode Circuit Board",
                    "base_yield" => 1,
                    "base_per_min" => 5,
                    "ingredients" => [
                        "Rubber" => 30,
                        "Petroleum Coke" => 45,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Silicon Circuit Board",
                    "base_yield" => 5,
                    "base_per_min" => 12.5,
                    "ingredients" => [
                        "Copper Sheet" => 27.5,
                        "Silica" => 27.5,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Compacted Coal" => [
                [
                    "base_yield" => 5,
                    "base_per_min" => 25,
                    "ingredients" => [
                        "Coal" => 25,
                        "Sulfur" => 25,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Computer" => [
                [
                    "description" => "Crystal Computer",
                    "base_yield" => 3,
                    "base_per_min" => 2.8,
                    "ingredients" => [
                        "Circuit Board" => 7.5,
                        "Crystal Oscillator" => 2.8125,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Concrete" => [
                [
                    "description" => "Fine Concrete",
                    "base_yield" => 10,
                    "base_per_min" => 25,
                    "ingredients" => [
                        "Silica" => 7.5,
                        "Limestone" => 30,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Rubber Concrete",
                    "base_yield" => 9,
                    "base_per_min" => 45,
                    "ingredients" => [
                        "Rubber" => 10,
                        "Limestone" => 50,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Electromagnetic Control Rod" => [
                [
                    "base_yield" => 2,
                    "base_per_min" => 4,
                    "ingredients" => [
                        "Stator" => 6,
                        "AI Limiter" => 4,
                    ],
                ],
                [
                    "description" => "Electromagnetic Connection Rod",
                    "base_yield" => 2,
                    "base_per_min" => 8,
                    "ingredients" => [
                        "Stator" => 8,
                        "High-Speed Connector" => 4,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Empty Canister" => [
                "description" => "Coated Iron Canister",
                "base_yield" => 4,
                "base_per_min" => 60,
                "ingredients" => [
                    "Iron Plate" => 30,
                    "Copper Sheet" => 15,
                ],
                "alt_recipe" => true,
            ],
            "Encased Industrial Beam" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 6,
                    "ingredients" => [
                        "Steel Beam" => 24,
                        "Concrete" => 30,
                    ],
                ],
                [
                    "description" => "Encased Industrial Pipe",
                    "base_yield" => 1,
                    "base_per_min" => 4,
                    "ingredients" => [
                        "Steel Pipe" => 28,
                        "Concrete" => 20,
                    ],
                    "alt_recipe" => true,
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
                [
                    "base_yield" => 1,
                    "base_per_min" => 7.5,
                    "ingredients" => [
                        "Alclad Aluminum Sheet" => 37.5,
                        "Copper Sheet" => 22.5,
                    ],
                ],
                [
                    "description" => "Heat Exchanger",
                    "base_yield" => 1,
                    "base_per_min" => 10,
                    "ingredients" => [
                        "Aluminum Casing" => 30,
                        "Rubber" => 30,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Iron Plate" => [
                [
                    "description" => "Coated Iron Plate",
                    "base_yield" => 15,
                    "base_per_min" => 75,
                    "ingredients" => [
                        "Iron Ingot" => 50,
                        "Plastic" => 10,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Steel Coated Plate",
                    "base_yield" => 18,
                    "base_per_min" => 45,
                    "ingredients" => [
                        "Steel Ingot" => 7.5,
                        "Plastic" => 5,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Modular Frame" => [
                [
                    "base_yield" => 2,
                    "base_per_min" => 2,
                    "ingredients" => [
                        "Reinforced Iron Plate" => 3,
                        "Iron Rod" => 12,
                    ],
                ],
                [
                    "description" => "Bolted Frame",
                    "base_yield" => 2,
                    "base_per_min" => 5,
                    "ingredients" => [
                        "Reinforced Iron Plate" => 7.5,
                        "Screw" => 140,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Steeled Frame",
                    "base_yield" => 3,
                    "base_per_min" => 3,
                    "ingredients" => [
                        "Reinforced Iron Plate" => 2,
                        "Steel Pipe" => 10,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Motor" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 5,
                    "ingredients" => [
                        "Rotor" => 10,
                        "Stator" => 10,
                    ],
                ],
                [
                    "description" => "Electric Motor",
                    "base_yield" => 2,
                    "base_per_min" => 7.5,
                    "ingredients" => [
                        "Rotor" => 7.5,
                        "Electromagnetic Control Rod" => 7.5,
                    ],
                    "alt_recipe" => true,
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
            "Plutonium Fuel Rod" => [
                "description" => "Plutonium Fuel Unit",
                "base_yield" => 1,
                "base_per_min" => 0.5,
                "ingredients" => [
                    "Encased Plutonium Cell" => 10,
                    "Pressure Conversion Cube" => 0.5,
                ],
                "alt_recipe" => true,
            ],
            "Pressure Conversion Cube" => [
                "base_yield" => 1,
                "base_per_min" => 1,
                "ingredients" => [
                    "Fused Modular Frame" => 1,
                    "Radio Control Unit" => 2,
                ],
            ],
            "Quickwire" => [
                "description" => "Fused Quickwire",
                "base_yield" => 12,
                "base_per_min" => 90,
                "ingredients" => [
                    "Caterium Ingot" => 7.5,
                    "Copper Ingot" => 37.5,
                ],
                "alt_recipe" => true,
            ],
            "Reinforced Iron Plate" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 5,
                    "ingredients" => [
                        "Iron Plate" => 30,
                        "Screw" => 60,
                    ],
                ],
                [
                    "description" => "Adhered Iron Plate",
                    "base_yield" => 1,
                    "base_per_min" => 3.8,
                    "ingredients" => [
                        "Iron Plate" => 11.25,
                        "Rubber" => 3.75,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Bolted Iron Plate",
                    "base_yield" => 3,
                    "base_per_min" => 15,
                    "ingredients" => [
                        "Iron Plate" => 90,
                        "Screw" => 250,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Stitched Iron Plate",
                    "base_yield" => 3,
                    "base_per_min" => 5.6,
                    "ingredients" => [
                        "Iron Plate" => 18.75,
                        "Wire" => 37.5,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Rotor" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 4,
                    "ingredients" => [
                        "Iron Rod" => 20,
                        "Screw" => 100,
                    ],
                ],
                [
                    "description" => "Copper Rotor",
                    "base_yield" => 3,
                    "base_per_min" => 11.3,
                    "ingredients" => [
                        "Copper Sheet" => 22.5,
                        "Screw" => 195,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Steel Rotor",
                    "base_yield" => 1,
                    "base_per_min" => 5,
                    "ingredients" => [
                        "Steel Pipe" => 10,
                        "Wire" => 30,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Silica" => [
                "description" => "Cheap Silica",
                "base_yield" => 7,
                "base_per_min" => 26.3,
                "ingredients" => [
                    "Raw Quartz" => 11.25,
                    "Limestone" => 18.75,
                ],
                "alt_recipe" => true,
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
                [
                    "base_yield" => 1,
                    "base_per_min" => 5,
                    "ingredients" => [
                        "Steel Pipe" => 15,
                        "Wire" => 40,
                    ],
                ],
                [
                    "description" => "Quickwire Stator",
                    "base_yield" => 2,
                    "base_per_min" => 8,
                    "ingredients" => [
                        "Steel Pipe" => 16,
                        "Quickwire" => 60,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Supercomputer" => [
                "description" => "OC Supercomputer",
                "base_yield" => 1,
                "base_per_min" => 3,
                "ingredients" => [
                    "Radio Control Unit" => 9,
                    "Cooling System" => 9,
                ],
                "alt_recipe" => true,
            ],
            "Versatile Framework" => [
                "base_yield" => 2,
                "base_per_min" => 5,
                "ingredients" => [
                    "Modular Frame" => 2.5,
                    "Steel Beam" => 30,
                ],
            ],
            "Wire" => [
                "description" => "Fused Wire",
                "base_yield" => 30,
                "base_per_min" => 90,
                "ingredients" => [
                    "Copper Ingot" => 12,
                    "Caterium Ingot" => 3,
                ],
                "alt_recipe" => true,
            ],
        ],

        // alts done
        "Foundry" => [
            "Aluminum Ingot" => [
                "base_yield" => 4,
                "base_per_min" => 60,
                "ingredients" => [
                    "Aluminum Scrap" => 90,
                    "Silica" => 75,
                ],
            ],
            "Copper Ingot" => [
                "description" => "Copper Alloy Ingot",
                "base_yield" => 20,
                "base_per_min" => 100,
                "ingredients" => [
                    "Copper Ore" => 50,
                    "Iron Ore" => 25,
                ],
                "alt_recipe" => true
            ],
            "Iron Ingot" => [
                "description" => "Iron Alloy Ingot",
                "base_yield" => 5,
                "base_per_min" => 50,
                "ingredients" => [
                    "Copper Ore" => 20,
                    "Iron Ore" => 20,
                ],
                "alt_recipe" => true
            ],
            "Steel Ingot" => [
                [
                    "base_yield" => 3,
                    "base_per_min" => 45,
                    "ingredients" => [
                        "Iron Ore" => 45,
                        "Coal" => 45,
                    ],
                ],
                [
                    "description" => "Coke Steel Ingot",
                    "base_yield" => 20,
                    "base_per_min" => 100,
                    "ingredients" => [
                        "Iron Ore" => 75,
                        "Petroleum Coke" => 75,
                    ],
                    "alt_recipe" => true
                ],
                [
                    "description" => "Compacted Steel Ingot",
                    "base_yield" => 10,
                    "base_per_min" => 37.5,
                    "ingredients" => [
                        "Iron Ore" => 22.5,
                        "Compacted Coal" => 11.25,
                    ],
                    "alt_recipe" => true
                ],
                [
                    "description" => "Solid Steel Ingot",
                    "base_yield" => 3,
                    "base_per_min" => 60,
                    "ingredients" => [
                        "Iron Ingot" => 40,
                        "Coal" => 40,
                    ],
                    "alt_recipe" => true
                ],
            ],
        ],

        // alts done
        "Refinery" => [
            "Alumina Solution" => [
                [
                    "base_yield" => 12,
                    "base_per_min" => 120,
                    "ingredients" => [
                        "Bauxite" => 120,
                        "Water" => 180,
                    ],
                ],
                [
                    "description" => "Sloppy Alumina",
                    "base_yield" => 12,
                    "base_per_min" => 240,
                    "ingredients" => [
                        "Bauxite" => 200,
                        "Water" => 200,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Aluminum Scrap" => [
                [
                    "base_yield" => 6,
                    "base_per_min" => 30,
                    "ingredients" => [
                        "Alumina Solution" => 240,
                        "Coal" => 120,
                    ],
                ],
                [
                    "description" => "Electrode - Aluminum Scrap",
                    "base_yield" => 20,
                    "base_per_min" => 300,
                    "ingredients" => [
                        "Alumina Solution" => 180,
                        "Petroleum Coke" => 60,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Cable" => [
                "description" => "Coated Cable",
                "base_yield" => 9,
                "base_per_min" => 67.5,
                "ingredients" => [
                    "Heavy Oil Residue" => 15,
                    "Wire" => 37.5,
                ],
                "alt_recipe" => true,
            ],
            "Caterium Ingot" => [
                "description" => "Pure Caterium Ingot",
                "base_yield" => 1,
                "base_per_min" => 12,
                "ingredients" => [
                    "Caterium Ore" => 24,
                    "Water" => 24,
                ],
                "alt_recipe" => true,
            ],
            "Concrete" => [
                "description" => "Wet Concrete",
                "base_yield" => 4,
                "base_per_min" => 80,
                "ingredients" => [
                    "Limestone" => 120,
                    "Water" => 100,
                ],
                "alt_recipe" => true,
            ],
            "Copper Ingot" => [
                "description" => "Pure Copper Ingot",
                "base_yield" => 15,
                "base_per_min" => 37.5,
                "ingredients" => [
                    "Copper Ore" => 15,
                    "Water" => 10,
                ],
                "alt_recipe" => true,
            ],
            "Copper Sheet" => [
                "description" => "Steamed Copper Sheet",
                "base_yield" => 3,
                "base_per_min" => 22.5,
                "ingredients" => [
                    "Copper Ingot" => 22.5,
                    "Water" => 22.5,
                ],
                "alt_recipe" => true,
            ],
            "Fabric" => [
                "description" => "Polyester Fabric",
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Polymer Resin" => 80,
                    "Water" => 50,
                ],
                "alt_recipe" => true,
            ],
            "Fuel" => [
                [
                    "base_yield" => 4,
                    "base_per_min" => 40,
                    "ingredients" => [
                        "Crude Oil" => 60,
                    ],
                ],
                [
                    "description" => "Residual Fuel",
                    "base_yield" => 4,
                    "base_per_min" => 40,
                    "ingredients" => [
                        "Heavy Oil Residue" => 60,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Heavy Oil Residue" => [
                "base_yield" => 4,
                "base_per_min" => 40,
                "ingredients" => [
                    "Crude Oil" => 30,
                ],
                "alt_recipe" => true,
            ],
            "Iron Ingot" => [
                "description" => "Pure Iron Ingot",
                "base_yield" => 13,
                "base_per_min" => 65,
                "ingredients" => [
                    "Iron Ore" => 35,
                    "Water" => 20,
                ],
                "alt_recipe" => true,
            ],
            "Liquid Biofuel" => [
                "base_yield" => 4,
                "base_per_min" => 60,
                "ingredients" => [
                    "Solid Biofuel" => 90,
                    "Water" => 45,
                ],
            ],
            "Packaged Fuel" => [
                "description" => "Diluted Packaged Fuel",
                "base_yield" => 2,
                "base_per_min" => 60,
                "ingredients" => [
                    "Heavy Oil Residue" => 30,
                    "Packaged Water" => 60,
                ],
                "alt_recipe" => true,
            ],
            "Petroleum Coke" => [
                "base_yield" => 12,
                "base_per_min" => 120,
                "ingredients" => [
                    "Heavy Oil Residue" => 40,
                ],
            ],
            "Plastic" => [
                [
                    "base_yield" => 2,
                    "base_per_min" => 20,
                    "ingredients" => [
                        "Crude Oil" => 30,
                    ],
                ],
                [
                    "description" => "Recycled Plastic",
                    "base_yield" => 12,
                    "base_per_min" => 60,
                    "ingredients" => [
                        "Rubber" => 30,
                        "Fuel" => 30,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Residual Plastic",
                    "base_yield" => 2,
                    "base_per_min" => 20,
                    "ingredients" => [
                        "Polymer Resin" => 60,
                        "Water" => 20,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Polymer Resin" => [
                "base_yield" => 13,
                "base_per_min" => 130,
                "ingredients" => [
                    "Crude Oil" => 60,
                ],
                "alt_recipe" => true,
            ],
            "Quartz Crystal" => [
                "description" => "Pure Quartz Crystal",
                "base_yield" => 7,
                "base_per_min" => 52.5,
                "ingredients" => [
                    "Raw Quartz" => 67.5,
                    "Water" => 37.5,
                ],
                "alt_recipe" => true,
            ],
            "Rubber" => [
                [
                    "base_yield" => 2,
                    "base_per_min" => 20,
                    "ingredients" => [
                        "Crude Oil" => 30,
                    ],
                ],
                [
                    "description" => "Recycled Rubber",
                    "base_yield" => 12,
                    "base_per_min" => 60,
                    "ingredients" => [
                        "Plastic" => 30,
                        "Fuel" => 30,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Residual Rubber",
                    "base_yield" => 2,
                    "base_per_min" => 20,
                    "ingredients" => [
                        "Polymer Resin" => 40,
                        "Water" => 20,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Sulfuric Acid" => [
                "base_yield" => 5,
                "base_per_min" => 50,
                "ingredients" => [
                    "Sulfur" => 50,
                    "Water" => 50,
                ],
            ],
            "Turbofuel" => [
                [
                    "base_yield" => 5,
                    "base_per_min" => 18.8,
                    "ingredients" => [
                        "Fuel" => 22.5,
                        "Compacted Coal" => 15,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Turbo Heavy Fuel",
                    "base_yield" => 4,
                    "base_per_min" => 30,
                    "ingredients" => [
                        "Heavy Oil Residue" => 37.5,
                        "Compacted Coal" => 30,
                    ],
                    "alt_recipe" => true,
                ],
            ],
        ],

        // no alts
        "Packager" => [
            "Packaged Alumina Solution" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Iron Ore" => 30,
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
            "Packaged Sulfuric Acid" => [
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

        /*
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
        */
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->recipes)->each(function ($products, $building_name) {
            $building = Building::ofName($building_name);

            collect($products)->each(function ($recipes, $product_name) use ($building) {
                $product = Ingredient::ofName($product_name);

                // wrap the recipes if needed
                $recipes = isset($recipes[0]) ? $recipes : [$recipes];



                foreach ($recipes as $recipe) {
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

                    $recipe_model = Recipe::create($atts);

                    collect($recipe['ingredients'])->each(function ($qty, $name) use ($recipe_model) {

                        $ingredient = Ingredient::ofName($name);

                        if ( get_class($ingredient) !== Ingredient::class )
                            throw new InvalidArgumentException("Could not find ingredient {$name}");

                        $recipe_model->addIngredient($ingredient, $qty);
                    });
                }
            });
        });
    }
}
