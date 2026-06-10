<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class RecipeSeeder extends Seeder
{
    protected $recipes = [
        // alts done
        // no byproducts
        'Smelter' => [
            'Iron Ingot' => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Iron Ore' => 30,
                ],
            ],

            'Copper Ingot' => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Copper Ore' => 30,
                ],
            ],

            'Caterium Ingot' => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    'Caterium Ore' => 45,
                ],
            ],

            'Aluminum Ingot' => [
                'description' => 'Pure Aluminum Ingot',
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Aluminum Scrap' => 60,
                ],
                'alt_recipe' => true,
            ],

        ],

        // alts done
        // no byproducts
        'Constructor' => [
            'Aluminum Casing' => [
                'base_yield' => 2,
                'base_per_min' => 60,
                'ingredients' => [
                    'Aluminum Ingot' => 90,
                ],
            ],

            'Biomass' => [
                [
                    'description' => 'Biomass (Alien Organs)',
                    'base_yield' => 200,
                    'base_per_min' => 1500,
                    'ingredients' => [
                        'Alien Organs' => 7.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Leaves)',
                    'base_yield' => 5,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Leaves' => 120,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Mycelia)',
                    'base_yield' => 10,
                    'base_per_min' => 150,
                    'ingredients' => [
                        'Mycelia' => 150,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Wood)',
                    'base_yield' => 20,
                    'base_per_min' => 300,
                    'ingredients' => [
                        'Wood' => 60,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Alien Protein)',
                    'base_yield' => 100,
                    'base_per_min' => 1500,
                    'ingredients' => [
                        'Alien Protein' => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Cable' => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Wire' => 60,
                ],
            ],
            'Coal' => [
                [
                    'description' => 'Biocoal',
                    'base_yield' => 6,
                    'base_per_min' => 45,
                    'ingredients' => [
                        'Biomass' => 37.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Charcoal',
                    'base_yield' => 10,
                    'base_per_min' => 150,
                    'ingredients' => [
                        'Wood' => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Concrete' => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    'Limestone' => 45,
                ],
            ],
            'Copper Powder' => [
                'base_yield' => 5,
                'base_per_min' => 50,
                'ingredients' => [
                    'Copper Ingot' => 300,
                ],
            ],
            'Copper Sheet' => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    'Copper Ingot' => 20,
                ],
            ],
            'Empty Canister' => [
                [
                    'base_yield' => 4,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Plastic' => 30,
                    ],
                ],
                [
                    'description' => 'Steel Canister',
                    'base_yield' => 2,
                    'base_per_min' => 40,
                    'ingredients' => [
                        'Steel Ingot' => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Empty Fluid Tank' => [
                'base_yield' => 1,
                'base_per_min' => 60,
                'ingredients' => [
                    'Aluminum Ingot' => 60,
                ],
            ],
            'Iron Plate' => [
                'base_yield' => 2,
                'base_per_min' => 20,
                'ingredients' => [
                    'Iron Ingot' => 30,
                ],
            ],
            'Iron Rod' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 15,
                    'ingredients' => [
                        'Iron Ingot' => 15,
                    ],
                ],
                [
                    'description' => 'Steel Rod',
                    'base_yield' => 4,
                    'base_per_min' => 48,
                    'ingredients' => [
                        'Steel Ingot' => 12,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Aluminum Rod',
                    'base_yield' => 7,
                    'base_per_min' => 52.5,
                    'ingredients' => [
                        'Aluminum Ingot' => 7.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Power Shard' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        'Blue Power Slug' => 7.5,
                    ],
                ],
                [
                    'description' => 'Power Shard (1)',
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        'Blue Power Slug' => 7.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Power Shard (2)',
                    'base_yield' => 2,
                    'base_per_min' => 10,
                    'ingredients' => [
                        'Yellow Power Slug' => 5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Power Shard (5)',
                    'base_yield' => 5,
                    'base_per_min' => 12.5,
                    'ingredients' => [
                        'Purple Power Slug' => 2.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Quartz Crystal' => [
                'base_yield' => 3,
                'base_per_min' => 22.5,
                'ingredients' => [
                    'Raw Quartz' => 37.5,
                ],
            ],
            'Quickwire' => [
                'base_yield' => 5,
                'base_per_min' => 60,
                'ingredients' => [
                    'Caterium Ingot' => 12,
                ],
            ],
            'Screw' => [
                [
                    'base_yield' => 4,
                    'base_per_min' => 40,
                    'ingredients' => [
                        'Iron Rod' => 10,
                    ],
                ],
                [
                    'description' => 'Cast Screw',
                    'base_yield' => 20,
                    'base_per_min' => 50,
                    'ingredients' => [
                        'Iron Ingot' => 12.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Steel Screw',
                    'base_yield' => 52,
                    'base_per_min' => 260,
                    'ingredients' => [
                        'Steel Beam' => 5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Silica' => [
                'base_yield' => 5,
                'base_per_min' => 37.5,
                'ingredients' => [
                    'Raw Quartz' => 22.5,
                ],
            ],
            'Solid Biofuel' => [
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    'Biomass' => 120,
                ],
            ],
            'Steel Beam' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 15,
                    'ingredients' => [
                        'Steel Ingot' => 60,
                    ],
                ],
                [
                    'description' => 'Aluminum Beam',
                    'base_yield' => 3,
                    'base_per_min' => 22.5,
                    'ingredients' => [
                        'Aluminum Ingot' => 22.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Steel Pipe' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Steel Ingot' => 30,
                    ],
                ],
                [
                    'description' => 'Iron Pipe',
                    'base_yield' => 5,
                    'base_per_min' => 25,
                    'ingredients' => [
                        'Iron Ingot' => 100,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Wire' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 30,
                    'ingredients' => [
                        'Copper Ingot' => 15,
                    ],
                ],
                [
                    'description' => 'Caterium Wire',
                    'base_yield' => 8,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Caterium Ingot' => 15,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Iron Wire',
                    'base_yield' => 9,
                    'base_per_min' => 22.5,
                    'ingredients' => [
                        'Iron Ingot' => 12.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            // UpdateSix combat items
            'Alien Protein' => [
                [
                    'description' => 'Hog Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Hog Remains' => 20,
                    ],
                    'alt_recipe' => false,
                ],
                [
                    'description' => 'Spitter Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Plasma Spitter Remains' => 20,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Hatcher Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Hatcher Remains' => 20,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Stinger Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Stinger Remains' => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Organic Data Capsule' => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    'Alien Protein' => 10,
                ],
            ],
            'Iron Rebar' => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    'Iron Rod' => 15,
                ],
            ],
            // UpdateOneZero new Constructor items
            'Alien DNA Capsule' => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    'Alien Protein' => 10,
                ],
            ],
            'Ficsite Trigon' => [
                'base_yield' => 3,
                'base_per_min' => 30,
                'ingredients' => [
                    'Ficsite Ingot' => 10,
                ],
            ],
            'Reanimated SAM' => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'SAM' => 120,
                ],
            ],
        ],

        // alts done
        // no byproducts
        'Assembler' => [
            'Alclad Aluminum Sheet' => [
                'base_yield' => 3,
                'base_per_min' => 30,
                'ingredients' => [
                    'Aluminum Ingot' => 30,
                    'Copper Ingot' => 10,
                ],
            ],
            'Aluminum Casing' => [
                'description' => 'Alclad Casing',
                'base_yield' => 15,
                'base_per_min' => 112.5,
                'ingredients' => [
                    'Aluminum Ingot' => 150,
                    'Copper Ingot' => 75,
                ],
                'alt_recipe' => true,
            ],
            'Assembly Director System' => [
                'base_yield' => 1,
                'base_per_min' => 0.75,
                'ingredients' => [
                    'Adaptive Control Unit' => 1.5,
                    'Supercomputer' => 0.75,
                ],
            ],
            'Automated Wiring' => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    'Stator' => 2.5,
                    'Cable' => 50,
                ],
            ],
            'Black Powder' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        'Coal' => 15,
                        'Sulfur' => 15,
                    ],
                ],
                [
                    'description' => 'Fine Black Powder',
                    'base_yield' => 6,
                    'base_per_min' => 45,
                    'ingredients' => [
                        'Sulfur' => 7.5,
                        'Compacted Coal' => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Cable' => [
                [
                    'description' => 'Insulated Cable',
                    'base_yield' => 20,
                    'base_per_min' => 100,
                    'ingredients' => [
                        'Wire' => 45,
                        'Rubber' => 30,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Quickwire Cable',
                    'base_yield' => 11,
                    'base_per_min' => 27.5,
                    'ingredients' => [
                        'Quickwire' => 7.5,
                        'Rubber' => 5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Circuit Board' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        'Copper Sheet' => 15,
                        'Plastic' => 30,
                    ],
                ],
                [
                    'description' => 'Caterium Circuit Board',
                    'base_yield' => 7,
                    'base_per_min' => 8.75,
                    'ingredients' => [
                        'Quickwire' => 37.5,
                        'Plastic' => 12.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Electrode Circuit Board',
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        'Rubber' => 30,
                        'Petroleum Coke' => 45,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Silicon Circuit Board',
                    'base_yield' => 5,
                    'base_per_min' => 12.5,
                    'ingredients' => [
                        'Copper Sheet' => 27.5,
                        'Silica' => 27.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Compacted Coal' => [
                [
                    'base_yield' => 5,
                    'base_per_min' => 25,
                    'ingredients' => [
                        'Coal' => 25,
                        'Sulfur' => 25,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Computer' => [
                [
                    'description' => 'Crystal Computer',
                    'base_yield' => 2,
                    'base_per_min' => 3.3333,
                    'ingredients' => [
                        'Circuit Board' => 5,
                        'Crystal Oscillator' => 1.6667,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Concrete' => [
                [
                    'description' => 'Fine Concrete',
                    'base_yield' => 10,
                    'base_per_min' => 50,
                    'ingredients' => [
                        'Silica' => 15,
                        'Limestone' => 60,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Rubber Concrete',
                    'base_yield' => 9,
                    'base_per_min' => 90,
                    'ingredients' => [
                        'Limestone' => 100,
                        'Rubber' => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Electromagnetic Control Rod' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 4,
                    'ingredients' => [
                        'Stator' => 6,
                        'AI Limiter' => 4,
                    ],
                ],
                [
                    'description' => 'Electromagnetic Connection Rod',
                    'base_yield' => 2,
                    'base_per_min' => 8,
                    'ingredients' => [
                        'Stator' => 8,
                        'High-Speed Connector' => 4,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Empty Canister' => [
                'description' => 'Coated Iron Canister',
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    'Iron Plate' => 30,
                    'Copper Sheet' => 15,
                ],
                'alt_recipe' => true,
            ],
            'Encased Industrial Beam' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 6,
                    'ingredients' => [
                        'Steel Beam' => 18,
                        'Concrete' => 36,
                    ],
                ],
                [
                    'description' => 'Encased Industrial Pipe',
                    'base_yield' => 1,
                    'base_per_min' => 4,
                    'ingredients' => [
                        'Steel Pipe' => 24,
                        'Concrete' => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Encased Plutonium Cell' => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    'Plutonium Pellet' => 10,
                    'Concrete' => 20,
                ],
            ],
            'Fabric' => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    'Mycelia' => 15,
                    'Biomass' => 75,
                ],
            ],
            'Heat Sink' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        'Alclad Aluminum Sheet' => 37.5,
                        'Copper Sheet' => 22.5,
                    ],
                ],
                [
                    'description' => 'Heat Exchanger',
                    'base_yield' => 1,
                    'base_per_min' => 10,
                    'ingredients' => [
                        'Aluminum Casing' => 30,
                        'Rubber' => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Iron Plate' => [
                [
                    'description' => 'Coated Iron Plate',
                    'base_yield' => 15,
                    'base_per_min' => 75,
                    'ingredients' => [
                        'Iron Ingot' => 50,
                        'Plastic' => 10,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Modular Frame' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 2,
                    'ingredients' => [
                        'Reinforced Iron Plate' => 3,
                        'Iron Rod' => 12,
                    ],
                ],
                [
                    'description' => 'Bolted Frame',
                    'base_yield' => 2,
                    'base_per_min' => 5,
                    'ingredients' => [
                        'Reinforced Iron Plate' => 7.5,
                        'Screw' => 140,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Steeled Frame',
                    'base_yield' => 3,
                    'base_per_min' => 3,
                    'ingredients' => [
                        'Reinforced Iron Plate' => 2,
                        'Steel Pipe' => 10,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Motor' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        'Rotor' => 10,
                        'Stator' => 10,
                    ],
                ],
                [
                    'description' => 'Electric Motor',
                    'base_yield' => 2,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        'Rotor' => 7.5,
                        'Electromagnetic Control Rod' => 3.75,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Nobelisk' => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    'Black Powder' => 20,
                    'Steel Pipe' => 20,
                ],
            ],
            'Plutonium Fuel Rod' => [
                'description' => 'Plutonium Fuel Unit',
                'base_yield' => 1,
                'base_per_min' => 0.5,
                'ingredients' => [
                    'Encased Plutonium Cell' => 10,
                    'Pressure Conversion Cube' => 0.5,
                ],
                'alt_recipe' => true,
            ],
            'Pressure Conversion Cube' => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    'Fused Modular Frame' => 1,
                    'Radio Control Unit' => 2,
                ],
            ],
            'Quickwire' => [
                'description' => 'Fused Quickwire',
                'base_yield' => 12,
                'base_per_min' => 90,
                'ingredients' => [
                    'Caterium Ingot' => 7.5,
                    'Copper Ingot' => 37.5,
                ],
                'alt_recipe' => true,
            ],
            'Reinforced Iron Plate' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        'Iron Plate' => 30,
                        'Screw' => 60,
                    ],
                ],
                [
                    'description' => 'Adhered Iron Plate',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        'Iron Plate' => 11.25,
                        'Rubber' => 3.75,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Bolted Iron Plate',
                    'base_yield' => 3,
                    'base_per_min' => 15,
                    'ingredients' => [
                        'Iron Plate' => 90,
                        'Screw' => 250,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Stitched Iron Plate',
                    'base_yield' => 3,
                    'base_per_min' => 5.6,
                    'ingredients' => [
                        'Iron Plate' => 18.75,
                        'Wire' => 37.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Rotor' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 4,
                    'ingredients' => [
                        'Iron Rod' => 20,
                        'Screw' => 100,
                    ],
                ],
                [
                    'description' => 'Copper Rotor',
                    'base_yield' => 3,
                    'base_per_min' => 11.25,
                    'ingredients' => [
                        'Copper Sheet' => 22.5,
                        'Screw' => 195,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Steel Rotor',
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        'Steel Pipe' => 10,
                        'Wire' => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Silica' => [
                'description' => 'Cheap Silica',
                'base_yield' => 7,
                'base_per_min' => 52.5,
                'ingredients' => [
                    'Raw Quartz' => 22.5,
                    'Limestone' => 37.5,
                ],
                'alt_recipe' => true,
            ],
            'Smart Plating' => [
                'base_yield' => 1,
                'base_per_min' => 2,
                'ingredients' => [
                    'Reinforced Iron Plate' => 2,
                    'Rotor' => 2,
                ],
            ],
            'Stator' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        'Steel Pipe' => 15,
                        'Wire' => 40,
                    ],
                ],
                [
                    'description' => 'Quickwire Stator',
                    'base_yield' => 2,
                    'base_per_min' => 8,
                    'ingredients' => [
                        'Steel Pipe' => 16,
                        'Quickwire' => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Supercomputer' => [
                'description' => 'OC Supercomputer',
                'base_yield' => 1,
                'base_per_min' => 3,
                'ingredients' => [
                    'Radio Control Unit' => 6,
                    'Cooling System' => 6,
                ],
                'alt_recipe' => true,
            ],
            'Versatile Framework' => [
                'base_yield' => 2,
                'base_per_min' => 5,
                'ingredients' => [
                    'Modular Frame' => 2.5,
                    'Steel Beam' => 30,
                ],
            ],
            'Wire' => [
                'description' => 'Fused Wire',
                'base_yield' => 30,
                'base_per_min' => 90,
                'ingredients' => [
                    'Copper Ingot' => 12,
                    'Caterium Ingot' => 3,
                ],
                'alt_recipe' => true,
            ],
            // UpdateSix Assembler additions
            'Gas Nobelisk' => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    'Nobelisk' => 5,
                    'Biomass' => 50,
                ],
            ],
            'Pulse Nobelisk' => [
                'base_yield' => 5,
                'base_per_min' => 5,
                'ingredients' => [
                    'Nobelisk' => 5,
                    'Crystal Oscillator' => 1,
                ],
            ],
            'Cluster Nobelisk' => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    'Nobelisk' => 7.5,
                    'Smokeless Powder' => 10,
                ],
            ],
            'Rifle Ammo' => [
                'base_yield' => 15,
                'base_per_min' => 75,
                'ingredients' => [
                    'Copper Sheet' => 15,
                    'Smokeless Powder' => 10,
                ],
            ],
            'Stun Rebar' => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    'Iron Rebar' => 10,
                    'Quickwire' => 50,
                ],
            ],
            'Shatter Rebar' => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    'Iron Rebar' => 10,
                    'Quartz Crystal' => 15,
                ],
            ],
            'Homing Rifle Ammo' => [
                'base_yield' => 10,
                'base_per_min' => 25,
                'ingredients' => [
                    'Rifle Ammo' => 50,
                    'High-Speed Connector' => 2.5,
                ],
            ],
            // UpdateOneZero Assembler additions
            'AI Limiter' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        'Copper Sheet' => 25,
                        'Quickwire' => 100,
                    ],
                ],
                [
                    'description' => 'Plastic AI Limiter',
                    'base_yield' => 2,
                    'base_per_min' => 8,
                    'ingredients' => [
                        'Quickwire' => 120,
                        'Plastic' => 28,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Magnetic Field Generator' => [
                'base_yield' => 2,
                'base_per_min' => 1,
                'ingredients' => [
                    'Versatile Framework' => 2.5,
                    'Electromagnetic Control Rod' => 1,
                ],
            ],
        ],

        // alts done
        // no byproducts
        'Foundry' => [
            'Aluminum Ingot' => [
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    'Aluminum Scrap' => 90,
                    'Silica' => 75,
                ],
            ],
            'Iron Ingot' => [
                [
                    'description' => 'Iron Alloy Ingot',
                    'base_yield' => 15,
                    'base_per_min' => 75,
                    'ingredients' => [
                        'Copper Ore' => 10,
                        'Iron Ore' => 40,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Basic Iron Ingot',
                    'base_yield' => 10,
                    'base_per_min' => 50,
                    'ingredients' => [
                        'Iron Ore' => 25,
                        'Limestone' => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Steel Ingot' => [
                [
                    'base_yield' => 3,
                    'base_per_min' => 45,
                    'ingredients' => [
                        'Iron Ore' => 45,
                        'Coal' => 45,
                    ],
                ],
                [
                    'description' => 'Coke Steel Ingot',
                    'base_yield' => 20,
                    'base_per_min' => 100,
                    'ingredients' => [
                        'Iron Ore' => 75,
                        'Petroleum Coke' => 75,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Compacted Steel Ingot',
                    'base_yield' => 4,
                    'base_per_min' => 10,
                    'ingredients' => [
                        'Iron Ore' => 5,
                        'Compacted Coal' => 2.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Solid Steel Ingot',
                    'base_yield' => 3,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Iron Ingot' => 40,
                        'Coal' => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Quartz Crystal' => [
                'description' => 'Fused Quartz Crystal',
                'base_yield' => 18,
                'base_per_min' => 54,
                'ingredients' => [
                    'Raw Quartz' => 75,
                    'Coal' => 36,
                ],
                'alt_recipe' => true,
            ],
            'Steel Beam' => [
                'description' => 'Molded Beam',
                'base_yield' => 9,
                'base_per_min' => 45,
                'ingredients' => [
                    'Steel Ingot' => 120,
                    'Concrete' => 80,
                ],
                'alt_recipe' => true,
            ],
            'Steel Pipe' => [
                'description' => 'Molded Steel Pipe',
                'base_yield' => 5,
                'base_per_min' => 50,
                'ingredients' => [
                    'Steel Ingot' => 50,
                    'Concrete' => 30,
                ],
                'alt_recipe' => true,
            ],
            'Iron Plate' => [
                'description' => 'Steel Cast Plate',
                'base_yield' => 3,
                'base_per_min' => 45,
                'ingredients' => [
                    'Iron Ingot' => 15,
                    'Steel Ingot' => 15,
                ],
                'alt_recipe' => true,
            ],
            'Caterium Ingot' => [
                'description' => 'Tempered Caterium Ingot',
                'base_yield' => 3,
                'base_per_min' => 22.5,
                'ingredients' => [
                    'Caterium Ore' => 45,
                    'Petroleum Coke' => 15,
                ],
                'alt_recipe' => true,
            ],
            'Copper Ingot' => [
                [
                    'description' => 'Copper Alloy Ingot',
                    'base_yield' => 10,
                    'base_per_min' => 100,
                    'ingredients' => [
                        'Copper Ore' => 50,
                        'Iron Ore' => 50,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Tempered Copper Ingot',
                    'base_yield' => 12,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Copper Ore' => 25,
                        'Petroleum Coke' => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
        ],

        // alts done
        // byproducts done
        'Refinery' => [
            'Alumina Solution' => [
                [
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Bauxite' => 120,
                        'Water' => 180,
                    ],
                    'byproducts' => [
                        'Silica' => 50,
                    ],
                ],
                [
                    'description' => 'Sloppy Alumina',
                    'base_yield' => 12,
                    'base_per_min' => 240,
                    'ingredients' => [
                        'Bauxite' => 200,
                        'Water' => 200,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Aluminum Scrap' => [
                [
                    'base_yield' => 6,
                    'base_per_min' => 360,
                    'ingredients' => [
                        'Alumina Solution' => 240,
                        'Coal' => 120,
                    ],
                    'byproducts' => [
                        'Water' => 120,
                    ],
                ],
                [
                    'description' => 'Electrode - Aluminum Scrap',
                    'base_yield' => 20,
                    'base_per_min' => 300,
                    'ingredients' => [
                        'Alumina Solution' => 180,
                        'Petroleum Coke' => 60,
                    ],
                    'byproducts' => [
                        'Water' => 105,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Cable' => [
                'description' => 'Coated Cable',
                'base_yield' => 9,
                'base_per_min' => 67.5,
                'ingredients' => [
                    'Heavy Oil Residue' => 15,
                    'Wire' => 37.5,
                ],
                'alt_recipe' => true,
            ],
            'Caterium Ingot' => [
                [
                    'description' => 'Pure Caterium Ingot',
                    'base_yield' => 1,
                    'base_per_min' => 12,
                    'ingredients' => [
                        'Caterium Ore' => 24,
                        'Water' => 24,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Leached Caterium Ingot',
                    'base_yield' => 6,
                    'base_per_min' => 36,
                    'ingredients' => [
                        'Caterium Ore' => 54,
                        'Sulfuric Acid' => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Concrete' => [
                'description' => 'Wet Concrete',
                'base_yield' => 4,
                'base_per_min' => 80,
                'ingredients' => [
                    'Limestone' => 120,
                    'Water' => 100,
                ],
                'alt_recipe' => true,
            ],
            'Copper Ingot' => [
                [
                    'description' => 'Pure Copper Ingot',
                    'base_yield' => 15,
                    'base_per_min' => 37.5,
                    'ingredients' => [
                        'Copper Ore' => 15,
                        'Water' => 10,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Leached Copper Ingot',
                    'base_yield' => 22,
                    'base_per_min' => 110,
                    'ingredients' => [
                        'Copper Ore' => 45,
                        'Sulfuric Acid' => 25,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Copper Sheet' => [
                'description' => 'Steamed Copper Sheet',
                'base_yield' => 3,
                'base_per_min' => 22.5,
                'ingredients' => [
                    'Copper Ingot' => 22.5,
                    'Water' => 22.5,
                ],
                'alt_recipe' => true,
            ],
            'Fabric' => [
                'description' => 'Polyester Fabric',
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    'Polymer Resin' => 80,
                    'Water' => 50,
                ],
                'alt_recipe' => true,
            ],
            'Fuel' => [
                [
                    'base_yield' => 4,
                    'base_per_min' => 40,
                    'ingredients' => [
                        'Crude Oil' => 60,
                    ],
                    'byproducts' => [
                        'Polymer Resin' => 30,
                    ],
                ],
                [
                    'description' => 'Residual Fuel',
                    'base_yield' => 4,
                    'base_per_min' => 40,
                    'ingredients' => [
                        'Heavy Oil Residue' => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Heavy Oil Residue' => [
                'base_yield' => 4,
                'base_per_min' => 40,
                'ingredients' => [
                    'Crude Oil' => 30,
                ],
                'byproducts' => [
                    'Polymer Resin' => 20,
                ],
                'alt_recipe' => true,
            ],
            'Iron Ingot' => [
                [
                    'description' => 'Pure Iron Ingot',
                    'base_yield' => 13,
                    'base_per_min' => 65,
                    'ingredients' => [
                        'Iron Ore' => 35,
                        'Water' => 20,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Leached Iron ingot',
                    'base_yield' => 10,
                    'base_per_min' => 100,
                    'ingredients' => [
                        'Iron Ore' => 50,
                        'Sulfuric Acid' => 10,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Liquid Biofuel' => [
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    'Solid Biofuel' => 90,
                    'Water' => 45,
                ],
            ],
            'Packaged Fuel' => [
                'description' => 'Diluted Packaged Fuel',
                'base_yield' => 2,
                'base_per_min' => 60,
                'ingredients' => [
                    'Heavy Oil Residue' => 30,
                    'Packaged Water' => 60,
                ],
                'alt_recipe' => true,
            ],
            'Petroleum Coke' => [
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    'Heavy Oil Residue' => 40,
                ],
            ],
            'Plastic' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Crude Oil' => 30,
                    ],
                    'byproducts' => [
                        'Heavy Oil Residue' => 10,
                    ],
                ],
                [
                    'description' => 'Recycled Plastic',
                    'base_yield' => 12,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Rubber' => 30,
                        'Fuel' => 30,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Residual Plastic',
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Polymer Resin' => 60,
                        'Water' => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Polymer Resin' => [
                'base_yield' => 13,
                'base_per_min' => 130,
                'ingredients' => [
                    'Crude Oil' => 60,
                ],
                'byproducts' => [
                    'Heavy Oil Residue' => 20,
                ],
                'alt_recipe' => true,
            ],
            'Ionized Fuel' => [
                'base_yield' => 16,
                'base_per_min' => 40,
                'ingredients' => [
                    'Rocket Fuel' => 40,
                    'Power Shard' => 2.5,
                ],
                'byproducts' => [
                    'Compacted Coal' => 5,
                ],
            ],
            'Quartz Crystal' => [
                [
                    'description' => 'Pure Quartz Crystal',
                    'base_yield' => 7,
                    'base_per_min' => 52.5,
                    'ingredients' => [
                        'Raw Quartz' => 67.5,
                        'Water' => 37.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Quartz Purification',
                    'base_yield' => 15,
                    'base_per_min' => 75,
                    'ingredients' => [
                        'Raw Quartz' => 120,
                        'Nitric Acid' => 10,
                    ],
                    'byproducts' => [
                        'Dissolved Silica' => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Rubber' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Crude Oil' => 30,
                    ],
                    'byproducts' => [
                        'Heavy Oil Residue' => 20,
                    ],
                ],
                [
                    'description' => 'Recycled Rubber',
                    'base_yield' => 12,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Plastic' => 30,
                        'Fuel' => 30,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Residual Rubber',
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Polymer Resin' => 40,
                        'Water' => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Dissolved Silica' => [
                'base_yield' => 12,
                'base_per_min' => 60,
                'ingredients' => [
                    'Raw Quartz' => 120,
                    'Nitric Acid' => 10,
                ],
                'byproducts' => [
                    'Quartz Crystal' => 75,
                ],
            ],
            'Smokeless Powder' => [
                'base_yield' => 2,
                'base_per_min' => 20,
                'ingredients' => [
                    'Black Powder' => 20,
                    'Heavy Oil Residue' => 10,
                ],
            ],
            'Sulfuric Acid' => [
                'base_yield' => 5,
                'base_per_min' => 50,
                'ingredients' => [
                    'Sulfur' => 50,
                    'Water' => 50,
                ],
            ],
            'Turbofuel' => [
                [
                    'base_yield' => 5,
                    'base_per_min' => 18.8,
                    'ingredients' => [
                        'Fuel' => 22.5,
                        'Compacted Coal' => 15,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Turbo Heavy Fuel',
                    'base_yield' => 4,
                    'base_per_min' => 30,
                    'ingredients' => [
                        'Heavy Oil Residue' => 37.5,
                        'Compacted Coal' => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
        ],

        // alts done
        // byproducts done
        'Packager' => [
            'Alumina Solution' => [
                'description' => 'Unpackage Alumina Solution',
                'base_yield' => 2,
                'base_per_min' => 120,
                'byproducts' => [
                    'Empty Canister' => 120,
                ],
                'ingredients' => [
                    'Packaged Alumina Solution' => 120,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Alumina Solution' => [
                'base_yield' => 2,
                'base_per_min' => 120,
                'ingredients' => [
                    'Alumina Solution' => 120,
                    'Empty Canister' => 120,
                ],
            ],
            'Fuel' => [
                'description' => 'Unpackage Fuel',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    'Empty Canister' => 60,
                ],
                'ingredients' => [
                    'Packaged Fuel' => 60,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Fuel' => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    'Fuel' => 40,
                    'Empty Canister' => 40,
                ],
            ],
            'Heavy Oil Residue' => [
                'description' => 'Unpackage Heavy Oil Residue',
                'base_yield' => 2,
                'base_per_min' => 20,
                'byproducts' => [
                    'Empty Canister' => 20,
                ],
                'ingredients' => [
                    'Packaged Heavy Oil Residue' => 20,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Heavy Oil Residue' => [
                'base_yield' => 2,
                'base_per_min' => 30,
                'ingredients' => [
                    'Heavy Oil Residue' => 30,
                    'Empty Canister' => 30,
                ],
            ],
            'Liquid Biofuel' => [
                'description' => 'Unpackage Liquid Biofuel',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    'Empty Canister' => 60,
                ],
                'ingredients' => [
                    'Packaged Liquid Biofuel' => 60,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Liquid Biofuel' => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    'Liquid Biofuel' => 40,
                    'Empty Canister' => 40,
                ],
            ],
            'Nitric Acid' => [
                'description' => 'Unpackage Nitric Acid',
                'base_yield' => 2,
                'base_per_min' => 20,
                'byproducts' => [
                    'Empty Canister' => 20,
                ],
                'ingredients' => [
                    'Packaged Nitric Acid' => 20,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Nitric Acid' => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Nitric Acid' => 30,
                    'Empty Canister' => 30,
                ],
            ],
            'Nitrogen Gas' => [
                'description' => 'Unpackage Nitrogen Gas',
                'base_yield' => 2,
                'base_per_min' => 240,
                'byproducts' => [
                    'Empty Fluid Tank' => 60,
                ],
                'ingredients' => [
                    'Packaged Nitrogen Gas' => 60,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Nitrogen Gas' => [
                'base_yield' => 1,
                'base_per_min' => 60,
                'ingredients' => [
                    'Nitrogen Gas' => 240,
                    'Empty Fluid Tank' => 60,
                ],
            ],
            'Crude Oil' => [
                'description' => 'Unpackage Oil',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    'Empty Canister' => 60,
                ],
                'ingredients' => [
                    'Packaged Oil' => 60,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Oil' => [
                'base_yield' => 2,
                'base_per_min' => 30,
                'ingredients' => [
                    'Crude Oil' => 30,
                    'Empty Canister' => 30,
                ],
            ],
            'Sulfuric Acid' => [
                'description' => 'Unpackage Sulfuric Acid',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    'Empty Canister' => 60,
                ],
                'ingredients' => [
                    'Packaged Sulfuric Acid' => 60,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Sulfuric Acid' => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    'Sulfuric Acid' => 40,
                    'Empty Canister' => 40,
                ],
            ],
            'Turbofuel' => [
                'description' => 'Unpackage Turbofuel',
                'base_yield' => 2,
                'base_per_min' => 20,
                'byproducts' => [
                    'Empty Canister' => 20,
                ],
                'ingredients' => [
                    'Packaged Turbofuel' => 20,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Turbofuel' => [
                'base_yield' => 2,
                'base_per_min' => 20,
                'ingredients' => [
                    'Turbofuel' => 20,
                    'Empty Canister' => 20,
                ],
            ],
            'Water' => [
                'description' => 'Unpackage Water',
                'base_yield' => 2,
                'base_per_min' => 120,
                'byproducts' => [
                    'Empty Canister' => 120,
                ],
                'ingredients' => [
                    'Packaged Water' => 120,
                ],
                'alt_recipe' => true,
            ],
            'Packaged Water' => [
                'base_yield' => 2,
                'base_per_min' => 60,
                'ingredients' => [
                    'Water' => 60,
                    'Empty Canister' => 60,
                ],
            ],
            'Packaged Ionized Fuel' => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    'Ionized Fuel' => 80,
                    'Empty Fluid Tank' => 40,
                ],
            ],
            'Packaged Rocket Fuel' => [
                'base_yield' => 1,
                'base_per_min' => 60,
                'ingredients' => [
                    'Rocket Fuel' => 120,
                    'Empty Fluid Tank' => 60,
                ],
            ],
            'Ionized Fuel' => [
                'description' => 'Unpackage Ionized Fuel',
                'base_yield' => 4,
                'base_per_min' => 80,
                'ingredients' => [
                    'Packaged Ionized Fuel' => 40,
                ],
                'byproducts' => [
                    'Empty Fluid Tank' => 40,
                ],
                'alt_recipe' => true,
            ],
            'Rocket Fuel' => [
                'description' => 'Unpackage Rocket Fuel',
                'base_yield' => 2,
                'base_per_min' => 120,
                'ingredients' => [
                    'Packaged Rocket Fuel' => 60,
                ],
                'byproducts' => [
                    'Empty Fluid Tank' => 60,
                ],
                'alt_recipe' => true,
            ],
        ],

        // alts done
        // no byproducts
        'Manufacturer' => [
            'Adaptive Control Unit' => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    'Automated Wiring' => 5,
                    'Circuit Board' => 5,
                    'Heavy Modular Frame' => 1,
                    'Computer' => 2,
                ],
            ],
            'Automated Wiring' => [
                'description' => 'Automated Speed Wiring',
                'base_yield' => 4,
                'base_per_min' => 7.5,
                'ingredients' => [
                    'Stator' => 3.75,
                    'Wire' => 75,
                    'High-Speed Connector' => 1.875,
                ],
                'alt_recipe' => true,
            ],
            'Battery' => [
                'description' => 'Classic Battery',
                'base_yield' => 4,
                'base_per_min' => 30,
                'ingredients' => [
                    'Sulfur' => 45,
                    'Alclad Aluminum Sheet' => 52.5,
                    'Plastic' => 60,
                    'Wire' => 90,
                ],
                'alt_recipe' => true,
            ],
            'Computer' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 2.5,
                    'ingredients' => [
                        'Circuit Board' => 10,
                        'Cable' => 20,
                        'Plastic' => 40,
                    ],
                ],
                [
                    'description' => 'Caterium Computer',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        'Circuit Board' => 15,
                        'Quickwire' => 52.5,
                        'Rubber' => 22.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Crystal Oscillator' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 1,
                    'ingredients' => [
                        'Quartz Crystal' => 18,
                        'Cable' => 14,
                        'Reinforced Iron Plate' => 2.5,
                    ],
                ],
                [
                    'description' => 'Insulated Crystal Oscillator',
                    'base_yield' => 1,
                    'base_per_min' => 1.875,
                    'ingredients' => [
                        'Quartz Crystal' => 18.75,
                        'Rubber' => 13.125,
                        'AI Limiter' => 1.875,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Encased Uranium Cell' => [
                'description' => 'Infused Uranium Cell',
                'base_yield' => 4,
                'base_per_min' => 20,
                'ingredients' => [
                    'Uranium' => 25,
                    'Silica' => 15,
                    'Sulfur' => 25,
                    'Quickwire' => 75,
                ],
                'alt_recipe' => true,
            ],
            'Gas Filter' => [
                'base_yield' => 1,
                'base_per_min' => 7.5,
                'ingredients' => [
                    'Fabric' => 15,
                    'Coal' => 30,
                    'Iron Plate' => 15,
                ],
            ],
            'Heavy Modular Frame' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 2,
                    'ingredients' => [
                        'Modular Frame' => 10,
                        'Steel Pipe' => 40,
                        'Encased Industrial Beam' => 10,
                        'Screw' => 240,
                    ],
                ],
                [
                    'description' => 'Heavy Flexible Frame',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        'Modular Frame' => 18.75,
                        'Encased Industrial Beam' => 11.25,
                        'Rubber' => 75,
                        'Screw' => 390,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Heavy Encased Frame',
                    'base_yield' => 3,
                    'base_per_min' => 2.8125,
                    'ingredients' => [
                        'Modular Frame' => 7.5,
                        'Encased Industrial Beam' => 9.375,
                        'Steel Pipe' => 33.75,
                        'Concrete' => 20.625,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'High-Speed Connector' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        'Quickwire' => 210,
                        'Cable' => 37.5,
                        'Circuit Board' => 3.75,
                    ],
                ],
                [
                    'description' => 'Silicon High-Speed Connector',
                    'base_yield' => 2,
                    'base_per_min' => 3,
                    'ingredients' => [
                        'Quickwire' => 90,
                        'Silica' => 37.5,
                        'Circuit Board' => 3,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Iodine Infused Filter' => [
                'base_yield' => 1,
                'base_per_min' => 3.75,
                'ingredients' => [
                    'Gas Filter' => 3.75,
                    'Quickwire' => 30,
                    'Aluminum Casing' => 3.75,
                ],
            ],
            'Modular Engine' => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    'Motor' => 2,
                    'Rubber' => 15,
                    'Smart Plating' => 2,
                ],
            ],
            'Motor' => [
                'description' => 'Rigor Motor',
                'base_yield' => 6,
                'base_per_min' => 7.5,
                'ingredients' => [
                    'Rotor' => 3.75,
                    'Stator' => 3.75,
                    'Crystal Oscillator' => 1.25,
                ],
                'alt_recipe' => true,
            ],
            'Nobelisk' => [
                'description' => 'Seismic Nobelisk',
                'base_yield' => 4,
                'base_per_min' => 6,
                'ingredients' => [
                    'Black Powder' => 12,
                    'Steel Pipe' => 12,
                    'Crystal Oscillator' => 1.5,
                ],
                'alt_recipe' => true,
            ],
            'Portable Miner' => [
                'description' => 'Automated Miner',
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    'Motor' => 1,
                    'Steel Pipe' => 4,
                    'Iron Rod' => 4,
                    'Iron Plate' => 2,
                ],
            ],
            'Plutonium Fuel Rod' => [
                'base_yield' => 1,
                'base_per_min' => 0.25,
                'ingredients' => [
                    'Encased Plutonium Cell' => 7.5,
                    'Steel Beam' => 4.5,
                    'Electromagnetic Control Rod' => 1.5,
                    'Heat Sink' => 2.5,
                ],
            ],
            'Radio Control Unit' => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 2.5,
                    'ingredients' => [
                        'Aluminum Casing' => 40,
                        'Crystal Oscillator' => 1.25,
                        'Computer' => 2.5,
                    ],
                ],
                [
                    'description' => 'Radio Connection Unit',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        'Heat Sink' => 15,
                        'High-Speed Connector' => 7.5,
                        'Quartz Crystal' => 45,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Radio Control System',
                    'base_yield' => 3,
                    'base_per_min' => 4.5,
                    'ingredients' => [
                        'Crystal Oscillator' => 1.5,
                        'Circuit Board' => 15,
                        'Aluminum Casing' => 90,
                        'Rubber' => 45,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Supercomputer' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 1.875,
                    'ingredients' => [
                        'Computer' => 7.5,
                        'AI Limiter' => 3.75,
                        'High-Speed Connector' => 5.625,
                        'Plastic' => 52.5,
                    ],
                ],
                [
                    'description' => 'Super-State Computer',
                    'base_yield' => 1,
                    'base_per_min' => 2.4,
                    'ingredients' => [
                        'Computer' => 7.2,
                        'Electromagnetic Control Rod' => 2.4,
                        'Battery' => 24,
                        'Wire' => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Smart Plating' => [
                'description' => 'Plastic Smart Plating',
                'base_yield' => 2,
                'base_per_min' => 5,
                'ingredients' => [
                    'Reinforced Iron Plate' => 2.5,
                    'Rotor' => 2.5,
                    'Plastic' => 7.5,
                ],
                'alt_recipe' => true,
            ],
            'Thermal Propulsion Rocket' => [
                'base_yield' => 2,
                'base_per_min' => 1,
                'ingredients' => [
                    'Modular Engine' => 2.5,
                    'Turbo Motor' => 1,
                    'Cooling System' => 3,
                    'Fused Modular Frame' => 1,
                ],
            ],
            'Turbo Motor' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 1.9,
                    'ingredients' => [
                        'Cooling System' => 7.5,
                        'Radio Control Unit' => 3.75,
                        'Motor' => 7.5,
                        'Rubber' => 45,
                    ],
                ],
                [
                    'description' => 'Turbo Electric Motor',
                    'base_yield' => 3,
                    'base_per_min' => 2.8,
                    'ingredients' => [
                        'Motor' => 6.5625,
                        'Radio Control Unit' => 8.4375,
                        'Electromagnetic Control Rod' => 4.6875,
                        'Rotor' => 6.5625,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Turbo Pressure Motor',
                    'base_yield' => 2,
                    'base_per_min' => 3.8,
                    'ingredients' => [
                        'Motor' => 7.5,
                        'Pressure Conversion Cube' => 1.875,
                        'Packaged Nitrogen Gas' => 45,
                        'Stator' => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Quantum Server' => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    'Supercomputer' => 10,
                    'AI Limiter' => 50,
                    'Heavy Modular Frame' => 25,
                ],
            ],
            'Uranium Fuel Rod' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 0.4,
                    'ingredients' => [
                        'Encased Uranium Cell' => 20,
                        'Encased Industrial Beam' => 1.2,
                        'Electromagnetic Control Rod' => 2,
                    ],
                ],
                [
                    'description' => 'Uranium Fuel Unit',
                    'base_yield' => 3,
                    'base_per_min' => 0.6,
                    'ingredients' => [
                        'Encased Uranium Cell' => 20,
                        'Electromagnetic Control Rod' => 2,
                        'Crystal Oscillator' => 0.6,
                        'Rotor' => 2,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Versatile Framework' => [
                'description' => 'Flexible Framework',
                'base_yield' => 2,
                'base_per_min' => 7.5,
                'ingredients' => [
                    'Modular Frame' => 3.75,
                    'Steel Beam' => 22.5,
                    'Rubber' => 30,
                ],
                'alt_recipe' => true,
            ],
            'Ballistic Warp Drive' => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    'Thermal Propulsion Rocket' => 1,
                    'Singularity Cell' => 5,
                    'Superposition Oscillator' => 2,
                    'Dark Matter Crystal' => 40,
                ],
            ],
            'SAM Fluctuator' => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    'Reanimated SAM' => 60,
                    'Wire' => 50,
                    'Steel Pipe' => 30,
                ],
            ],
            'Singularity Cell' => [
                'base_yield' => 10,
                'base_per_min' => 10,
                'ingredients' => [
                    'Nuclear Pasta' => 1,
                    'Dark Matter Crystal' => 20,
                    'Iron Plate' => 100,
                    'Concrete' => 200,
                ],
            ],
            'Cooling System' => [
                'description' => 'Cooling Device',
                'base_yield' => 2,
                'base_per_min' => 5,
                'ingredients' => [
                    'Heat Sink' => 10,
                    'Motor' => 2.5,
                    'Nitrogen Gas' => 60,
                ],
                'alt_recipe' => true,
            ],
            'Explosive Rebar' => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    'Iron Rebar' => 10,
                    'Smokeless Powder' => 10,
                    'Steel Pipe' => 10,
                ],
            ],
            'Nuke Nobelisk' => [
                'base_yield' => 1,
                'base_per_min' => 0.5,
                'ingredients' => [
                    'Encased Uranium Cell' => 10,
                    'Nobelisk' => 2.5,
                    'Smokeless Powder' => 5,
                    'AI Limiter' => 3,
                ],
            ],
            'Turbo Rifle Ammo' => [
                'base_yield' => 50,
                'base_per_min' => 250,
                'ingredients' => [
                    'Rifle Ammo' => 125,
                    'Aluminum Casing' => 15,
                    'Packaged Turbofuel' => 15,
                ],
            ],
        ],

        // alts done
        // byproducts done
        'Blender' => [
            'Aluminum Scrap' => [
                'description' => 'Instant Scrap',
                'base_yield' => 30,
                'base_per_min' => 300,
                'ingredients' => [
                    'Bauxite' => 150,
                    'Coal' => 100,
                    'Sulfuric Acid' => 50,
                    'Water' => 60,
                ],
                'byproducts' => [
                    'Water' => 50,
                ],
                'alt_recipe' => true,
            ],
            'Battery' => [
                'base_yield' => 1,
                'base_per_min' => 20,
                'ingredients' => [
                    'Sulfuric Acid' => 50,
                    'Alumina Solution' => 40,
                    'Aluminum Casing' => 20,
                ],
                'byproducts' => [
                    'Water' => 30,
                ],
            ],
            'Cooling System' => [
                [
                    'description' => 'Cooling Device',
                    'base_yield' => 2,
                    'base_per_min' => 3.8,
                    'ingredients' => [
                        'Heat Sink' => 9.375,
                        'Motor' => 1.875,
                        'Nitrogen Gas' => 45,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'base_yield' => 1,
                    'base_per_min' => 6,
                    'ingredients' => [
                        'Heat Sink' => 12,
                        'Rubber' => 12,
                        'Water' => 30,
                        'Nitrogen Gas' => 150,
                    ],
                ],
            ],
            'Encased Uranium Cell' => [
                'base_yield' => 5,
                'base_per_min' => 25,
                'ingredients' => [
                    'Uranium' => 50,
                    'Concrete' => 15,
                    'Sulfuric Acid' => 40,
                ],
                'byproducts' => [
                    'Sulfuric Acid' => 10,
                ],
            ],
            'Fuel' => [
                'description' => 'Diluted Fuel',
                'base_yield' => 10,
                'base_per_min' => 100,
                'ingredients' => [
                    'Heavy Oil Residue' => 50,
                    'Water' => 100,
                ],
                'alt_recipe' => true,
            ],
            'Fused Modular Frame' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 1.5,
                    'ingredients' => [
                        'Heavy Modular Frame' => 1.5,
                        'Aluminum Casing' => 75,
                        'Nitrogen Gas' => 37.5,
                    ],
                ],
                [
                    'description' => 'Heat-Fused Frame',
                    'base_yield' => 1,
                    'base_per_min' => 3,
                    'ingredients' => [
                        'Heavy Modular Frame' => 3,
                        'Aluminum Ingot' => 150,
                        'Nitric Acid' => 24,
                        'Fuel' => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Nitric Acid' => [
                'base_yield' => 3,
                'base_per_min' => 30,
                'ingredients' => [
                    'Nitrogen Gas' => 120,
                    'Water' => 30,
                    'Iron Plate' => 10,
                ],
            ],
            'Non-fissile Uranium' => [
                [
                    'description' => 'Fertile Uranium',
                    'base_yield' => 20,
                    'base_per_min' => 100,
                    'ingredients' => [
                        'Uranium' => 25,
                        'Uranium Waste' => 25,
                        'Nitric Acid' => 15,
                        'Sulfuric Acid' => 25,
                    ],
                    'byproducts' => [
                        'Water' => 40,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'base_yield' => 20,
                    'base_per_min' => 50,
                    'ingredients' => [
                        'Uranium Waste' => 37.5,
                        'Silica' => 25,
                        'Nitric Acid' => 15,
                        'Sulfuric Acid' => 15,
                    ],
                    'byproducts' => [
                        'Water' => 15,
                    ],
                ],
            ],
            'Turbofuel' => [
                'description' => 'Turbo Blend Fuel',
                'base_yield' => 6,
                'base_per_min' => 45,
                'ingredients' => [
                    'Fuel' => 15,
                    'Heavy Oil Residue' => 30,
                    'Sulfur' => 22.5,
                    'Petroleum Coke' => 22.5,
                ],
                'alt_recipe' => true,
            ],
            'Biochemical Sculptor' => [
                'base_yield' => 4,
                'base_per_min' => 2,
                'ingredients' => [
                    'Assembly Director System' => 0.5,
                    'Ficsite Trigon' => 40,
                    'Water' => 10,
                ],
            ],
            'Silica' => [
                'description' => 'Distilled Silica',
                'base_yield' => 27,
                'base_per_min' => 270,
                'ingredients' => [
                    'Dissolved Silica' => 120,
                    'Limestone' => 50,
                    'Water' => 100,
                ],
                'byproducts' => [
                    'Water' => 80,
                ],
                'alt_recipe' => true,
            ],
            'Rocket Fuel' => [
                [
                    'base_yield' => 10,
                    'base_per_min' => 100,
                    'ingredients' => [
                        'Turbofuel' => 60,
                        'Nitric Acid' => 10,
                    ],
                    'byproducts' => [
                        'Compacted Coal' => 10,
                    ],
                ],
                [
                    'description' => 'Nitro Rocket Fuel',
                    'base_yield' => 6,
                    'base_per_min' => 150,
                    'ingredients' => [
                        'Fuel' => 100,
                        'Nitrogen Gas' => 75,
                        'Sulfur' => 100,
                        'Coal' => 50,
                    ],
                    'byproducts' => [
                        'Compacted Coal' => 25,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Turbo Rifle Ammo' => [
                'base_yield' => 50,
                'base_per_min' => 250,
                'ingredients' => [
                    'Rifle Ammo' => 125,
                    'Aluminum Casing' => 15,
                    'Turbofuel' => 15,
                ],
            ],
        ],

        // alts done
        // no byproducts
        'Particle Accelerator' => [
            'Encased Plutonium Cell' => [
                'description' => 'Instant Plutonium Cell',
                'base_yield' => 20,
                'base_per_min' => 10,
                'ingredients' => [
                    'Non-fissile Uranium' => 75,
                    'Aluminum Casing' => 10,
                ],
                'alt_recipe' => true,
            ],
            'Plutonium Pellet' => [
                'base_yield' => 30,
                'base_per_min' => 30,
                'ingredients' => [
                    'Non-fissile Uranium' => 100,
                    'Uranium Waste' => 25,
                ],
            ],
            'Nuclear Pasta' => [
                'base_yield' => 1,
                'base_per_min' => 0.5,
                'ingredients' => [
                    'Copper Powder' => 100,
                    'Pressure Conversion Cube' => 0.5,
                ],
            ],
            'Diamonds' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        'Coal' => 600,
                    ],
                ],
                [
                    'description' => 'Cloudy Diamonds',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Coal' => 240,
                        'Limestone' => 480,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Oil-Based Diamonds',
                    'base_yield' => 2,
                    'base_per_min' => 40,
                    'ingredients' => [
                        'Crude Oil' => 200,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Petroleum Diamonds',
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        'Petroleum Coke' => 720,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Turbo Diamonds',
                    'base_yield' => 3,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Coal' => 600,
                        'Packaged Turbofuel' => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Dark Matter Crystal' => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        'Diamonds' => 30,
                        'Dark Matter Residue' => 150,
                    ],
                ],
                [
                    'description' => 'Dark Matter Crystallization',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        'Dark Matter Residue' => 200,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Dark Matter Trap',
                    'base_yield' => 2,
                    'base_per_min' => 60,
                    'ingredients' => [
                        'Time Crystal' => 30,
                        'Dark Matter Residue' => 150,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Ficsonium' => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    'Plutonium Waste' => 10,
                    'Singularity Cell' => 10,
                    'Dark Matter Residue' => 200,
                ],
            ],
        ],

        // no alts
        'Nuclear Power Plant' => [
            'Uranium Waste' => [
                'base_yield' => 50,
                'base_per_min' => 10,
                'ingredients' => [
                    'Uranium Fuel Rod' => 0.2,
                    'Water' => 300,
                ],
            ],
            'Plutonium Waste' => [
                'base_yield' => 10,
                'base_per_min' => 1,
                'ingredients' => [
                    'Plutonium Fuel Rod' => 0.1,
                    'Water' => 300,
                ],
            ],
        ],

        'Quantum Encoder' => [
            'AI Expansion Server' => [
                'base_yield' => 1,
                'base_per_min' => 4,
                'ingredients' => [
                    'Magnetic Field Generator' => 4,
                    'Neural-Quantum Processor' => 4,
                    'Superposition Oscillator' => 4,
                    'Excited Photonic Matter' => 100,
                ],
                'byproducts' => [
                    'Dark Matter Residue' => 100,
                ],
            ],
            'Alien Power Matrix' => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    'SAM Fluctuator' => 12.5,
                    'Power Shard' => 7.5,
                    'Superposition Oscillator' => 7.5,
                    'Excited Photonic Matter' => 60,
                ],
                'byproducts' => [
                    'Dark Matter Residue' => 60,
                ],
            ],
            'Ficsonium Fuel Rod' => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    'Ficsonium' => 5,
                    'Electromagnetic Control Rod' => 5,
                    'Ficsite Trigon' => 100,
                    'Excited Photonic Matter' => 50,
                ],
                'byproducts' => [
                    'Dark Matter Residue' => 50,
                ],
            ],
            'Neural-Quantum Processor' => [
                'base_yield' => 1,
                'base_per_min' => 3,
                'ingredients' => [
                    'Time Crystal' => 15,
                    'Supercomputer' => 3,
                    'Ficsite Trigon' => 45,
                    'Excited Photonic Matter' => 75,
                ],
                'byproducts' => [
                    'Dark Matter Residue' => 75,
                ],
            ],
            'Superposition Oscillator' => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    'Dark Matter Crystal' => 30,
                    'Crystal Oscillator' => 5,
                    'Alclad Aluminum Sheet' => 45,
                    'Excited Photonic Matter' => 125,
                ],
                'byproducts' => [
                    'Dark Matter Residue' => 125,
                ],
            ],
            'Power Shard' => [
                'description' => 'Synthetic Power Shard',
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    'Time Crystal' => 10,
                    'Dark Matter Crystal' => 10,
                    'Quartz Crystal' => 60,
                    'Excited Photonic Matter' => 60,
                ],
                'byproducts' => [
                    'Dark Matter Residue' => 60,
                ],
                'alt_recipe' => true,
            ],
        ],

        'Converter' => [
            'Bauxite' => [
                [
                    'description' => 'Bauxite (Caterium)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Caterium Ore' => 150,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Bauxite (Copper)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Copper Ore' => 180,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Caterium Ore' => [
                [
                    'description' => 'Caterium Ore (Copper)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Copper Ore' => 150,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Caterium Ore (Quartz)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Raw Quartz' => 120,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Coal' => [
                [
                    'description' => 'Coal (Iron)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Iron Ore' => 180,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Coal (Limestone)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Limestone' => 360,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Copper Ore' => [
                [
                    'description' => 'Copper Ore (Quartz)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Raw Quartz' => 100,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Copper Ore (Sulfur)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Sulfur' => 120,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Dark Matter Residue' => [
                'base_yield' => 10,
                'base_per_min' => 100,
                'ingredients' => [
                    'Reanimated SAM' => 50,
                ],
            ],
            'Ionized Fuel' => [
                'description' => 'Dark-Ion Fuel',
                'base_yield' => 10,
                'base_per_min' => 200,
                'ingredients' => [
                    'Packaged Rocket Fuel' => 240,
                    'Dark Matter Crystal' => 80,
                ],
                'byproducts' => [
                    'Compacted Coal' => 40,
                ],
                'alt_recipe' => true,
            ],
            'Excited Photonic Matter' => [
                'base_yield' => 10,
                'base_per_min' => 200,
                'ingredients' => [],
            ],
            'Ficsite Ingot' => [
                [
                    'description' => 'Ficsite Ingot (Aluminum)',
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        'Reanimated SAM' => 60,
                        'Aluminum Ingot' => 120,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Ficsite Ingot (Caterium)',
                    'base_yield' => 1,
                    'base_per_min' => 15,
                    'ingredients' => [
                        'Reanimated SAM' => 45,
                        'Caterium Ingot' => 60,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Ficsite Ingot (Iron)',
                    'base_yield' => 1,
                    'base_per_min' => 10,
                    'ingredients' => [
                        'Reanimated SAM' => 40,
                        'Iron Ingot' => 240,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Iron Ore' => [
                'description' => 'Iron Ore (Limestone)',
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    'Reanimated SAM' => 10,
                    'Limestone' => 240,
                ],
                'alt_recipe' => true,
            ],
            'Limestone' => [
                'description' => 'Limestone (Sulfur)',
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    'Reanimated SAM' => 10,
                    'Sulfur' => 20,
                ],
                'alt_recipe' => true,
            ],
            'Nitrogen Gas' => [
                [
                    'description' => 'Nitrogen Gas (Bauxite)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Bauxite' => 100,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Nitrogen Gas (Caterium)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Caterium Ore' => 120,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Diamonds' => [
                'description' => 'Pink Diamonds',
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    'Coal' => 120,
                    'Quartz Crystal' => 45,
                ],
                'alt_recipe' => true,
            ],
            'Raw Quartz' => [
                [
                    'description' => 'Raw Quartz (Bauxite)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Bauxite' => 100,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Raw Quartz (Coal)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Coal' => 240,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Sulfur' => [
                [
                    'description' => 'Sulfur (Coal)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Coal' => 200,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Sulfur (Iron)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        'Reanimated SAM' => 10,
                        'Iron Ore' => 300,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            'Time Crystal' => [
                'base_yield' => 1,
                'base_per_min' => 6,
                'ingredients' => [
                    'Diamonds' => 12,
                ],
            ],
            'Uranium' => [
                'description' => 'Uranium Ore (Bauxite)',
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    'Reanimated SAM' => 10,
                    'Bauxite' => 480,
                ],
                'alt_recipe' => true,
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

                    if (isset($recipe['byproducts'])) {
                        collect($recipe['byproducts'])->each(function ($qty, $name) use ($recipe_model) {

                            $ingredient = Ingredient::ofName($name);

                            if (get_class($ingredient) !== Ingredient::class) {
                                throw new InvalidArgumentException("Could not find ingredient {$name}");
                            }

                            $recipe_model->addByproduct($ingredient, $qty);
                        });
                    }

                }
            });
        });
    }
}
