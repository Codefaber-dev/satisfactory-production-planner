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
        // no byproducts
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
        // no byproducts
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
        // no byproducts
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
                "base_per_min" => 0.75,
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
                    "base_per_min" => 8.75,
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
                        "Electromagnetic Control Rod" => 3.75,
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
        // no byproducts
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
                "alt_recipe" => true,
            ],
            "Iron Ingot" => [
                "description" => "Iron Alloy Ingot",
                "base_yield" => 5,
                "base_per_min" => 50,
                "ingredients" => [
                    "Copper Ore" => 20,
                    "Iron Ore" => 20,
                ],
                "alt_recipe" => true,
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
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Compacted Steel Ingot",
                    "base_yield" => 10,
                    "base_per_min" => 37.5,
                    "ingredients" => [
                        "Iron Ore" => 22.5,
                        "Compacted Coal" => 11.25,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Solid Steel Ingot",
                    "base_yield" => 3,
                    "base_per_min" => 60,
                    "ingredients" => [
                        "Iron Ingot" => 40,
                        "Coal" => 40,
                    ],
                    "alt_recipe" => true,
                ],
            ],
        ],

        // alts done
        // byproducts done
        "Refinery" => [
            "Alumina Solution" => [
                [
                    "base_yield" => 12,
                    "base_per_min" => 120,
                    "ingredients" => [
                        "Bauxite" => 120,
                        "Water" => 180,
                    ],
                    "byproducts" => [
                        "Silica" => 50,
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
                    "base_per_min" => 360,
                    "ingredients" => [
                        "Alumina Solution" => 240,
                        "Coal" => 120,
                    ],
                    "byproducts" => [
                        "Water" => 120,
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
                    "byproducts" => [
                        "Water" => 105,
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
                    "byproducts" => [
                        "Polymer Resin" => 30,
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
                "byproducts" => [
                    "Polymer Resin" => 20,
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
                    "byproducts" => [
                        "Heavy Oil Residue" => 10,
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
                "byproducts" => [
                    "Heavy Oil Residue" => 20,
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
                    "byproducts" => [
                        "Heavy Oil Residue" => 20,
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

        // alts done
        // byproducts done
        "Packager" => [
            "Alumina Solution" => [
                "description" => "Unpackage Alumina Solution",
                "base_yield" => 2,
                "base_per_min" => 120,
                "byproducts" => [
                    "Empty Canister" => 120,
                ],
                "ingredients" => [
                    "Packaged Alumina Solution" => 120
                ],
                "alt_recipe" => true
            ],
            "Packaged Alumina Solution" => [
                "base_yield" => 2,
                "base_per_min" => 120,
                "ingredients" => [
                    "Alumina Solution" => 120,
                    "Empty Canister" => 120,
                ],
            ],
            "Fuel" => [
                "description" => "Unpackage Fuel",
                "base_yield" => 2,
                "base_per_min" => 60,
                "byproducts" => [
                    "Empty Canister" => 60,
                ],
                "ingredients" => [
                    "Packaged Fuel" => 60
                ],
                "alt_recipe" => true
            ],
            "Packaged Fuel" => [
                "base_yield" => 2,
                "base_per_min" => 40,
                "ingredients" => [
                    "Fuel" => 40,
                    "Empty Canister" => 40,
                ],
            ],
            "Heavy Oil Residue" => [
                "description" => "Unpackage Heavy Oil Residue",
                "base_yield" => 2,
                "base_per_min" => 20,
                "byproducts" => [
                    "Empty Canister" => 20,
                ],
                "ingredients" => [
                    "Packaged Heavy Oil Residue" => 20
                ],
                "alt_recipe" => true
            ],
            "Packaged Heavy Oil Residue" => [
                "base_yield" => 2,
                "base_per_min" => 30,
                "ingredients" => [
                    "Heavy Oil Residue" => 30,
                    "Empty Canister" => 30,
                ],
            ],
            "Liquid Biofuel" => [
                "description" => "Unpackage Liquid Biofuel",
                "base_yield" => 2,
                "base_per_min" => 60,
                "byproducts" => [
                    "Empty Canister" => 60,
                ],
                "ingredients" => [
                    "Packaged Liquid Biofuel" => 60
                ],
                "alt_recipe" => true
            ],
            "Packaged Liquid Biofuel" => [
                "base_yield" => 2,
                "base_per_min" => 40,
                "ingredients" => [
                    "Liquid Biofuel" => 40,
                    "Empty Canister" => 40,
                ],
            ],
            "Nitric Acid" => [
                "description" => "Unpackage Nitric Acid",
                "base_yield" => 2,
                "base_per_min" => 20,
                "byproducts" => [
                    "Empty Canister" => 20,
                ],
                "ingredients" => [
                    "Packaged Nitric Acid" => 20
                ],
                "alt_recipe" => true
            ],
            "Packaged Nitric Acid" => [
                "base_yield" => 1,
                "base_per_min" => 30,
                "ingredients" => [
                    "Nitric Acid" => 30,
                    "Empty Canister" => 30,
                ],
            ],
            "Nitrogen Gas" => [
                "description" => "Unpackage Nitrogen Gas",
                "base_yield" => 2,
                "base_per_min" => 240,
                "byproducts" => [
                    "Empty Fluid Tank" => 60,
                ],
                "ingredients" => [
                    "Packaged Nitrogen Gas" => 60
                ],
                "alt_recipe" => true
            ],
            "Packaged Nitrogen Gas" => [
                "base_yield" => 1,
                "base_per_min" => 60,
                "ingredients" => [
                    "Nitrogen Gas" => 240,
                    "Empty Fluid Tank" => 60,
                ],
            ],
            "Crude Oil" => [
                "description" => "Unpackage Oil",
                "base_yield" => 2,
                "base_per_min" => 60,
                "byproducts" => [
                    "Empty Canister" => 60,
                ],
                "ingredients" => [
                    "Packaged Oil" => 60
                ],
                "alt_recipe" => true
            ],
            "Packaged Oil" => [
                "base_yield" => 2,
                "base_per_min" => 30,
                "ingredients" => [
                    "Crude Oil" => 30,
                    "Empty Canister" => 30,
                ],
            ],
            "Sulfuric Acid" => [
                "description" => "Unpackage Sulfuric Acid",
                "base_yield" => 2,
                "base_per_min" => 60,
                "byproducts" => [
                    "Empty Canister" => 60,
                ],
                "ingredients" => [
                    "Packaged Sulfuric Acid" => 60
                ],
                "alt_recipe" => true
            ],
            "Packaged Sulfuric Acid" => [
                "base_yield" => 2,
                "base_per_min" => 40,
                "ingredients" => [
                    "Sulfuric Acid" => 40,
                    "Empty Canister" => 40,
                ],
            ],
            "Turbofuel" => [
                "description" => "Unpackage Turbofuel",
                "base_yield" => 2,
                "base_per_min" => 20,
                "byproducts" => [
                    "Empty Canister" => 20,
                ],
                "ingredients" => [
                    "Packaged Turbofuel" => 20
                ],
                "alt_recipe" => true
            ],
            "Packaged Turbofuel" => [
                "base_yield" => 2,
                "base_per_min" => 20,
                "ingredients" => [
                    "Turbofuel" => 20,
                    "Empty Canister" => 20,
                ],
            ],
            "Water" => [
                "description" => "Unpackage Water",
                "base_yield" => 2,
                "base_per_min" => 120,
                "byproducts" => [
                    "Empty Canister" => 120,
                ],
                "ingredients" => [
                    "Packaged Water" => 120
                ],
                "alt_recipe" => true
            ],
            "Packaged Water" => [
                "base_yield" => 2,
                "base_per_min" => 60,
                "ingredients" => [
                    "Water" => 60,
                    "Empty Canister" => 60,
                ],
            ],
        ],

        // alts done
        // no byproducts
        "Manufacturer" => [
            "Adaptive Control Unit" => [
                "base_yield" => 2,
                "base_per_min" => 1,
                "ingredients" => [
                    "Automated Wiring" => 7.5,
                    "Circuit Board" => 5,
                    "Heavy Modular Frame" => 1,
                    "Computer" => 1,
                ],
            ],
            "Automated Wiring" => [
                "description" => "High-Speed Wiring",
                "base_yield" => 4,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Stator" => 3.75,
                    "Wire" => 75,
                    "High-Speed Connector" => 1.875,
                ],
                "alt_recipe" => true,
            ],
            "Beacon" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 7.5,
                    "ingredients" => [
                        "Iron Plate" => 22.5,
                        "Iron Rod" => 7.5,
                        "Wire" => 112.5,
                        "Cable" => 15,
                    ],
                ],
                [
                    "description" => "Crystal Beacon",
                    "base_yield" => 20,
                    "base_per_min" => 10,
                    "ingredients" => [
                        "Steel Beam" => 2,
                        "Steel Pipe" => 8,
                        "Crystal Oscillator" => 0.5,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Battery" => [
                "description" => "Classic Battery",
                "base_yield" => 4,
                "base_per_min" => 30,
                "ingredients" => [
                    "Sulfur" => 45,
                    "Alclad Aluminum Sheet" => 52.5,
                    "Plastic" => 60,
                    "Wire" => 90,
                ],
                "alt_recipe" => true,
            ],
            "Computer" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 2.5,
                    "ingredients" => [
                        "Circuit Board" => 25,
                        "Cable" => 22.5,
                        "Plastic" => 45,
                        "Screw" => 130,
                    ],
                ],
                [
                    "description" => "Caterium Computer",
                    "base_yield" => 1,
                    "base_per_min" => 3.75,
                    "ingredients" => [
                        "Circuit Board" => 26.25,
                        "Quickwire" => 105,
                        "Rubber" => 45,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Crystal Oscillator" => [
                [
                    "base_yield" => 2,
                    "base_per_min" => 1,
                    "ingredients" => [
                        "Quartz Crystal" => 18,
                        "Cable" => 14,
                        "Reinforced Iron Plate" => 2.5,
                    ],
                ],
                [
                    "description" => "Insulated Crystal Oscillator",
                    "base_yield" => 1,
                    "base_per_min" => 1.9,
                    "ingredients" => [
                        "Quartz Crystal" => 18.75,
                        "Rubber" => 13.125,
                        "AI Limiter" => 1.875,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Encased Uranium Cell" => [
                "description" => "Infused Uranium Cell",
                "base_yield" => 4,
                "base_per_min" => 20,
                "ingredients" => [
                    "Uranium" => 25,
                    "Silica" => 15,
                    "Sulfur" => 25,
                    "Quickwire" => 75,
                ],
                "alt_recipe" => true,
            ],
            "Gas Filter" => [
                "base_yield" => 1,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Coal" => 37.5,
                    "Rubber" => 15,
                    "Fabric" => 15,
                ],
            ],
            "Heavy Modular Frame" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 2,
                    "ingredients" => [
                        "Modular Frame" => 10,
                        "Steel Pipe" => 30,
                        "Encased Industrial Beam" => 10,
                        "Screw" => 200,
                    ],
                ],
                [
                    "description" => "Heavy Flexible Frame",
                    "base_yield" => 1,
                    "base_per_min" => 3.8,
                    "ingredients" => [
                        "Modular Frame" => 18.75,
                        "Encased Industrial Beam" => 11.25,
                        "Rubber" => 75,
                        "Screw" => 390,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Heavy Encased Frame",
                    "base_yield" => 3,
                    "base_per_min" => 2.8,
                    "ingredients" => [
                        "Modular Frame" => 7.5,
                        "Encased Industrial Beam" => 9.375,
                        "Steel Pipe" => 33.75,
                        "Concrete" => 20.625,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "High-Speed Connector" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 3.8,
                    "ingredients" => [
                        "Quickwire" => 210,
                        "Cable" => 37.5,
                        "Circuit Board" => 3.75,
                    ],
                ],
                [
                    "description" => "Silicon High-Speed Connector",
                    "base_yield" => 2,
                    "base_per_min" => 3,
                    "ingredients" => [
                        "Quickwire" => 90,
                        "Silica" => 37.5,
                        "Circuit Board" => 3,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Iodine Infused Filter" => [
                "base_yield" => 1,
                "base_per_min" => 3.8,
                "ingredients" => [
                    "Gas Filter" => 3.75,
                    "Quickwire" => 30,
                    "Aluminum Casing" => 3.75,
                ],
            ],
            "Magnetic Field Generator" => [
                "base_yield" => 2,
                "base_per_min" => 1,
                "ingredients" => [
                    "Versatile Framework" => 2.5,
                    "Electromagnetic Control Rod" => 1,
                    "Battery" => 5,
                ],
            ],
            "Modular Engine" => [
                "base_yield" => 1,
                "base_per_min" => 1,
                "ingredients" => [
                    "Motor" => 2,
                    "Rubber" => 15,
                    "Smart Plating" => 2,
                ],
            ],
            "Motor" => [
                "description" => "Rigour Motor",
                "base_yield" => 6,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Rotor" => 3.75,
                    "Stator" => 3.75,
                    "Crystal Oscillator" => 1.25,
                ],
                "alt_recipe" => true,
            ],
            "Nobelisk" => [
                "description" => "Seismic Nobelisk",
                "base_yield" => 4,
                "base_per_min" => 6,
                "ingredients" => [
                    "Black Powder" => 12,
                    "Steel Pipe" => 12,
                    "Crystal Oscillator" => 1.5,
                ],
                "alt_recipe" => true,
            ],
            "Portable Miner" => [
                "description" => "Automated Miner",
                "base_yield" => 1,
                "base_per_min" => 1,
                "ingredients" => [
                    "Motor" => 1,
                    "Steel Pipe" => 4,
                    "Iron Rod" => 4,
                    "Iron Plate" => 2,
                ],
            ],
            "Plutonium Fuel Rod" => [
                "base_yield" => 1,
                "base_per_min" => 0.3,
                "ingredients" => [
                    "Encased Plutonium Cell" => 7.5,
                    "Steel Beam" => 4.5,
                    "Electromagnetic Control Rod" => 1.5,
                    "Heat Sink" => 2.5,
                ],
            ],
            "Radio Control Unit" => [
                [
                    "base_yield" => 2,
                    "base_per_min" => 2.5,
                    "ingredients" => [
                        "Aluminum Casing" => 40,
                        "Crystal Oscillator" => 1.25,
                        "Computer" => 1.25,
                    ],
                ],
                [
                    "description" => "Radio Connection Unit",
                    "base_yield" => 1,
                    "base_per_min" => 3.8,
                    "ingredients" => [
                        "Heat Sink" => 15,
                        "High-Speed Connector" => 7.5,
                        "Quartz Crystal" => 45,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Radio Control System",
                    "base_yield" => 3,
                    "base_per_min" => 4.5,
                    "ingredients" => [
                        "Crystal Oscillator" => 1.5,
                        "Circuit Board" => 15,
                        "Aluminum Casing" => 90,
                        "Rubber" => 45,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Rifle Cartridge" => [
                "base_yield" => 5,
                "base_per_min" => 15,
                "ingredients" => [
                    "Beacon" => 3,
                    "Steel Pipe" => 30,
                    "Black Powder" => 30,
                    "Rubber" => 30,
                ],
            ],
            "Supercomputer" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 1.9,
                    "ingredients" => [
                        "Computer" => 3.75,
                        "AI Limiter" => 3.75,
                        "High-Speed Connector" => 5.625,
                        "Plastic" => 52.5,
                    ],
                ],
                [
                    "description" => "Super-State Computer",
                    "base_yield" => 1,
                    "base_per_min" => 2.4,
                    "ingredients" => [
                        "Computer" => 3.6,
                        "Electromagnetic Control Rod" => 2.4,
                        "Battery" => 24,
                        "Wire" => 54,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Smart Plating" => [
                "description" => "Plastic Smart Plating",
                "base_yield" => 2,
                "base_per_min" => 5,
                "ingredients" => [
                    "Reinforced Iron Plate" => 2.5,
                    "Rotor" => 2.5,
                    "Plastic" => 7.5,
                ],
                "alt_recipe" => true,
            ],
            "Thermal Propulsion Rocket" => [
                "base_yield" => 2,
                "base_per_min" => 1,
                "ingredients" => [
                    "Modular Engine" => 2.5,
                    "Turbo Motor" => 1,
                    "Cooling System" => 3,
                    "Fused Modular Frame" => 1,
                ],
            ],
            "Turbo Motor" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 1.9,
                    "ingredients" => [
                        "Cooling System" => 7.5,
                        "Radio Control Unit" => 3.75,
                        "Motor" => 7.5,
                        "Rubber" => 45,
                    ],
                ],
                [
                    "description" => "Turbo Electric Motor",
                    "base_yield" => 3,
                    "base_per_min" => 2.8,
                    "ingredients" => [
                        "Motor" => 6.5625,
                        "Radio Control Unit" => 8.4375,
                        "Electromagnetic Control Rod" => 4.6875,
                        "Rotor" => 6.5625,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => "Turbo Pressure Motor",
                    "base_yield" => 2,
                    "base_per_min" => 3.8,
                    "ingredients" => [
                        "Motor" => 7.5,
                        "Pressure Conversion Cube" => 1.875,
                        "Packaged Nitrogen Gas" => 45,
                        "Stator" => 15,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Quantum Server" => [
                "base_yield" => 1,
                "base_per_min" => 1,
                "ingredients" => [
                    "Supercomputer" => 10,
                    "AI Limiter" => 50,
                    "Heavy Modular Frame" => 25,
                ],
            ],
            "Uranium Fuel Rod" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 0.4,
                    "ingredients" => [
                        "Encased Uranium Cell" => 20,
                        "Encased Industrial Beam" => 1.2,
                        "Electromagnetic Control Rod" => 2,
                    ],
                ],
                [
                    "description" => "Uranium Fuel Unit",
                    "base_yield" => 3,
                    "base_per_min" => 0.6,
                    "ingredients" => [
                        "Encased Uranium Cell" => 20,
                        "Electromagnetic Control Rod" => 2,
                        "Crystal Oscillator" => 0.6,
                        "Beacon" => 1.2,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            "Versatile Framework" => [
                "description" => "Flexible Framework",
                "base_yield" => 2,
                "base_per_min" => 7.5,
                "ingredients" => [
                    "Modular Frame" => 3.75,
                    "Steel Beam" => 22.5,
                    "Rubber" => 30,
                ],
                "alt_recipe" => true,
            ],
        ],

        // alts done
        // byproducts done
        "Blender" => [
            "Aluminum Scrap" => [
                "description" => "Instant Scrap",
                "base_yield" => 30,
                "base_per_min" => 300,
                "ingredients" => [
                    "Bauxite" => 150,
                    "Coal" => 100,
                    "Sulfuric Acid" => 50,
                    "Water" => 60,
                ],
                "byproducts" => [
                    "Water" => 50
                ],
                "alt_recipe" => true
            ],
            "Battery" => [
                "base_yield" => 1,
                "base_per_min" => 20,
                "ingredients" => [
                    "Sulfuric Acid" => 50,
                    "Alumina Solution" => 40,
                    "Aluminum Casing" => 20,
                ],
                "byproducts" => [
                    "Water" => 30
                ]
            ],
            "Cooling System" => [
                [
                    "description" => "Cooling Device",
                    "base_yield" => 2,
                    "base_per_min" => 3.8,
                    "ingredients" => [
                        "Heat Sink" => 9.375,
                        "Motor" => 1.875,
                        "Nitrogen Gas" => 45,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "base_yield" => 1,
                    "base_per_min" => 6,
                    "ingredients" => [
                        "Heat Sink" => 12,
                        "Rubber" => 12,
                        "Water" => 30,
                        "Nitrogen Gas" => 150,
                    ],
                ]
            ],
            "Encased Uranium Cell" => [
                "base_yield" => 5,
                "base_per_min" => 25,
                "ingredients" => [
                    "Uranium" => 50,
                    "Concrete" => 15,
                    "Sulfuric Acid" => 40,
                ],
                "byproducts" => [
                    "Sulfuric Acid" => 10
                ]
            ],
            "Fuel" => [
                "description" => "Diluted Fuel",
                "base_yield" => 10,
                "base_per_min" => 100,
                "ingredients" => [
                    "Heavy Oil Residue" => 50,
                    "Water" => 100,
                ],
                "alt_recipe" => true,
            ],
            "Fused Modular Frame" => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 1.5,
                    "ingredients" => [
                        "Heavy Modular Frame" => 1.5,
                        "Aluminum Casing" => 75,
                        "Nitrogen Gas" => 37.5,
                    ],
                ],
                [
                    "description" => "Heat-Fused Frame",
                    "base_yield" => 1,
                    "base_per_min" => 3,
                    "ingredients" => [
                        "Heavy Modular Frame" => 3,
                        "Aluminum Ingot" => 150,
                        "Nitric Acid" => 24,
                        "Fuel" => 30,
                    ],
                    "alt_recipe" => true
                ],
            ],
            "Nitric Acid" => [
                "base_yield" => 3,
                "base_per_min" => 30,
                "ingredients" => [
                    "Nitrogen Gas" => 120,
                    "Water" => 30,
                    "Iron Plate" => 10,
                ],
            ],
            "Non-fissile Uranium" => [
                [
                    "description" => "Fertile Uranium",
                    "base_yield" => 20,
                    "base_per_min" => 100,
                    "ingredients" => [
                        "Uranium" => 25,
                        "Uranium Waste" => 25,
                        "Nitric Acid" => 15,
                        "Sulfuric Acid" => 25,
                    ],
                    "byproducts" => [
                        "Water" => 40
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "base_yield" => 20,
                    "base_per_min" => 50,
                    "ingredients" => [
                        "Uranium Waste" => 37.5,
                        "Silica" => 25,
                        "Nitric Acid" => 15,
                        "Sulfuric Acid" => 15,
                    ],
                    "byproducts" => [
                        "Water" => 15
                    ],
                ],
            ],
            "Turbofuel" => [
                "description" => "Turbo Blend Fuel",
                "base_yield" => 6,
                "base_per_min" => 45,
                "ingredients" => [
                    "Fuel" => 15,
                    "Heavy Oil Residue" => 30,
                    "Sulfur" => 22.5,
                    "Petroleum Coke" => 22.5,
                ],
                "alt_recipe" => true
            ],
        ],

        // alts done
        // no byproducts
        "Particle Accelerator" => [
            "Encased Plutonium Cell" => [
                "description" => "Instant Plutonium Cell",
                "base_yield" => 20,
                "base_per_min" => 10,
                "ingredients" => [
                    "Non-fissile Uranium" => 75,
                    "Aluminum Casing" => 10,
                ],
                "alt_recipe" => true
            ],
            "Plutonium Pellet" => [
                "base_yield" => 30,
                "base_per_min" => 30,
                "ingredients" => [
                    "Non-fissile Uranium" => 100,
                    "Uranium Waste" => 25,
                ],
            ],
            "Nuclear Pasta" => [
                "base_yield" => 1,
                "base_per_min" => 0.5,
                "ingredients" => [
                    "Copper Powder" => 100,
                    "Pressure Conversion Cube" => 0.5,
                ],
            ],
        ],

        // no alts
        "Nuclear Power Plant" => [
            "Uranium Waste" => [
                "base_yield" => 50,
                "base_per_min" => 10,
                "ingredients" => [
                    "Uranium Fuel Rod" => 0.2,
                    "Water" => 300,
                ],
            ],
            "Plutonium Waste" => [
                "base_yield" => 10,
                "base_per_min" => 1,
                "ingredients" => [
                    "Plutonium Fuel Rod" => 0.1,
                    "Water" => 300,
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

                        if (get_class($ingredient) !== Ingredient::class) {
                            throw new InvalidArgumentException("Could not find ingredient {$name}");
                        }

                        $recipe_model->addIngredient($ingredient, $qty);
                    });

                    if ( isset($recipe['byproducts']))
                        collect($recipe['byproducts'])->each(function ($qty, $name) use ($recipe_model) {

                            $ingredient = Ingredient::ofName($name);

                            if (get_class($ingredient) !== Ingredient::class) {
                                throw new InvalidArgumentException("Could not find ingredient {$name}");
                            }

                            $recipe_model->addByproduct($ingredient, $qty);
                        });

                }
            });
        });
    }
}
