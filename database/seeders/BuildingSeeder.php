<?php

namespace Database\Seeders;

use App\Enums\Ingredient as IngredientEnum;
use App\Enums\Building as BuildingEnum;
use App\Models\Building;
use App\Models\BuildingVariant;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    protected $buildings = [

        // Smelter
        [
            'name' => 'Smelter',
            'inputs' => 1,
            'outputs' => 1,
            'width' => 6,
            'length' => 9,
            'height' => 9,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 4,
                    'recipe' => [
                        [ 'ingredient' => 'Iron Rod', 'qty' => 5 ],
                        [ 'ingredient' => 'Wire', 'qty' => 8 ],
                    ]
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 6,
                    'recipe' => [
                        [ 'ingredient' => 'Reinforced Iron Plate', 'qty' => 5 ],
                        [ 'ingredient' => 'Wire', 'qty' => 20 ],
                        [ 'ingredient' => 'Circuit Board', 'qty' => 5 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 8,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Computer', 'qty' => 3 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 10,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Wire', 'qty' => 50 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 2 ],
                    ]
                ],
            ],

        ],

        // Constructor
        [
            'name' => 'Constructor',
            'inputs' => 1,
            'outputs' => 1,
            'width' => 8,
            'length' => 10,
            'height' => 8,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 4,
                    'recipe' => [
                        [ 'ingredient' => 'Reinforced Iron Plate', 'qty' => 2 ],
                        [ 'ingredient' => 'Cable', 'qty' => 8 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 6,
                    'recipe' => [
                        [ 'ingredient' => 'Reinforced Iron Plate', 'qty' => 5 ],
                        [ 'ingredient' => 'Cable', 'qty' => 20 ],
                        [ 'ingredient' => 'Motor', 'qty' => 5 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 8,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Cable', 'qty' => 50 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 16,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Cable', 'qty' => 50 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 2 ],
                    ]
                ],
            ]
        ],

        // Assembler
        [
            'name' => 'Assembler',
            'inputs' => 2,
            'outputs' => 1,
            'width' => 10,
            'length' => 15,
            'height' => 11,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 15,
                    'recipe' => [
                        [ 'ingredient' => 'Reinforced Iron Plate', 'qty' => 8 ],
                        [ 'ingredient' => 'Cable', 'qty' => 10 ],
                        [ 'ingredient' => 'Rotor', 'qty' => 4 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 22.5,
                    'recipe' => [
                        [ 'ingredient' => 'Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Reinforced Iron Plate', 'qty' => 10 ],
                        [ 'ingredient' => 'Circuit Board', 'qty' => 5 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 30,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 5 ],
                        [ 'ingredient' => 'Computer', 'qty' => 5 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 60,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 5 ],
                        [ 'ingredient' => 'Supercomputer', 'qty' => 5 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 5 ],
                    ]
                ],
            ]
        ],

        // Foundry
        [
            'name' => 'Foundry',
            'inputs' => 2,
            'outputs' => 1,
            'width' => 10,
            'length' => 9,
            'height' => 9,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 16,
                    'recipe' => [
                        [ 'ingredient' => 'Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Rotor', 'qty' => 10 ],
                        [ 'ingredient' => 'Concrete', 'qty' => 20 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 24,
                    'recipe' => [
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Cable', 'qty' => 10 ],
                        [ 'ingredient' => 'Iron Rod', 'qty' => 20 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 32,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 15 ],
                        [ 'ingredient' => 'Steel Pipe', 'qty' => 20 ],
                        [ 'ingredient' => 'Supercomputer', 'qty' => 10 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 64,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 15 ],
                        [ 'ingredient' => 'Steel Pipe', 'qty' => 20 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 2 ],
                    ]
                ],
            ]
        ],

        // Packager
        [
            'name' => 'Packager',
            'inputs' => 2,
            'outputs' => 2,
            'width' => 8,
            'length' => 8,
            'height' => 12,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 10,
                    'recipe' => [
                        [ 'ingredient' => 'Steel Beam', 'qty' => 20 ],
                        [ 'ingredient' => 'Rubber', 'qty' => 10 ],
                        [ 'ingredient' => 'Plastic', 'qty' => 10 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 20,
                    'recipe' => [
                        [ 'ingredient' => 'Steel Pipe', 'qty' => 20 ],
                        [ 'ingredient' => 'Polymer Resin', 'qty' => 10 ],
                        [ 'ingredient' => 'Petroleum Coke', 'qty' => 10 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 30,
                    'recipe' => [
                        [ 'ingredient' => 'Encased Industrial Beam', 'qty' => 20 ],
                        [ 'ingredient' => 'Packaged Oil', 'qty' => 30 ],
                        [ 'ingredient' => 'Packaged Heavy Oil Residue', 'qty' => 10 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 40,
                    'recipe' => [
                        [ 'ingredient' => 'Alclad Aluminum Sheet', 'qty' => 20 ],
                        [ 'ingredient' => 'Aluminum Scrap', 'qty' => 10 ],
                        [ 'ingredient' => 'Fabric', 'qty' => 10 ],
                    ]
                ],
            ]
        ],

        // Refinery
        [
            'name' => 'Refinery',
            'inputs' => 2,
            'outputs' => 2,
            'width' => 10,
            'length' => 20,
            'height' => 31,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 30,
                    'recipe' => [
                        [ 'ingredient' => 'Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Encased Industrial Beam', 'qty' => 10 ],
                        [ 'ingredient' => 'Steel Pipe', 'qty' => 30 ],
                        [ 'ingredient' => 'Copper Sheet', 'qty' => 20 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 45,
                    'recipe' => [
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 20 ],
                        [ 'ingredient' => 'Motor', 'qty' => 15 ],
                        [ 'ingredient' => 'Computer', 'qty' => 5 ],
                        [ 'ingredient' => 'Steel Beam', 'qty' => 30 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 60,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Supercomputer', 'qty' => 10 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 20 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 120,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 20 ],
                        [ 'ingredient' => 'Supercomputer', 'qty' => 10 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 5],
                    ]
                ],
            ]
        ],

        // Manufacturer
        [
            'name' => 'Manufacturer',
            'inputs' => 4,
            'outputs' => 1,
            'width' => 18,
            'length' => 20,
            'height' => 12,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 55,
                    'recipe' => [
                        [ 'ingredient' => 'Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 20 ],
                        [ 'ingredient' => 'Cable', 'qty' => 50 ],
                        [ 'ingredient' => 'Plastic', 'qty' => 50 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 82.5,
                    'recipe' => [
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Computer', 'qty' => 5 ],
                        [ 'ingredient' => 'Wire', 'qty' => 50 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 110,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Supercomputer', 'qty' => 15 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 20 ],
                        [ 'ingredient' => 'Battery', 'qty' => 10 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 220,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Battery', 'qty' => 10 ],
                        [ 'ingredient' => 'Supercomputer', 'qty' => 15 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 10],
                    ]
                ],
            ]
        ],

        // Blender
        [
            'name' => 'Blender',
            'inputs' => 4,
            'outputs' => 2,
            'width' => 18,
            'length' => 16,
            'height' => 15,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 75,
                    'recipe' => [
                        [ 'ingredient' => 'Computer', 'qty' => 10 ],
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Motor', 'qty' => 20 ],
                        [ 'ingredient' => 'Aluminum Casing', 'qty' => 50 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 75*1.5,
                    'recipe' => [
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 15 ],
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Aluminum Casing', 'qty' => 75 ],
                        [ 'ingredient' => 'Radio Control Unit', 'qty' => 10 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 150,
                    'recipe' => [
                        [ 'ingredient' => 'Fused Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Aluminum Casing', 'qty' => 100 ],
                        [ 'ingredient' => 'Radio Control Unit', 'qty' => 15 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 300,
                    'recipe' => [
                        [ 'ingredient' => 'Fused Modular Frame', 'qty' => 20 ],
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 15 ],
                        [ 'ingredient' => 'Aluminum Casing', 'qty' => 150 ],
                        [ 'ingredient' => 'Radio Control Unit', 'qty' => 15 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 5 ],
                    ]
                ],
            ]
        ],

        // Particle Accelerator
        [
            'name' => 'Particle Accelerator',
            'inputs' => 3,
            'outputs' => 1,
            'width' => 24,
            'length' => 38,
            'height' => 32,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 250,
                    'recipe' => [
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Supercomputer', 'qty' => 10 ],
                        [ 'ingredient' => 'Fused Modular Frame', 'qty' => 20 ],
                        [ 'ingredient' => 'Cooling System', 'qty' => 50 ],
                        [ 'ingredient' => 'Quickwire', 'qty' => 500 ],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 375,
                    'recipe' => [
                        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 15 ],
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                        [ 'ingredient' => 'Aluminum Casing', 'qty' => 75 ],
                        [ 'ingredient' => 'Radio Control Unit', 'qty' => 10 ],
                    ]
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 500,
                    'recipe' => [
                        [ 'ingredient' => 'Fused Modular Frame', 'qty' => 10 ],
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                        [ 'ingredient' => 'Aluminum Casing', 'qty' => 100 ],
                        [ 'ingredient' => 'Radio Control Unit', 'qty' => 15 ],
                    ]
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 1000,
                    'recipe' => [
                        [ 'ingredient' => 'Fused Modular Frame', 'qty' => 20 ],
                        [ 'ingredient' => 'Turbo Motor', 'qty' => 15 ],
                        [ 'ingredient' => 'Aluminum Casing', 'qty' => 150 ],
                        [ 'ingredient' => 'Radio Control Unit', 'qty' => 15 ],
                        [ 'ingredient' => 'Quantum Server', 'qty' => 5 ],
                    ]
                ],
            ]
        ],

        // Nuclear Power Plant
        [
            'name' => BuildingEnum::NUCLEAR_POWER_PLANT,
            'inputs' => 2,
            'outputs' => 1,
            'width' => 36,
            'length' => 43,
            'height' => 49,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 2500,
                    'recipe' => [
                        [ 'ingredient' => IngredientEnum::SUPERCOMPUTER, 'qty' => 10 ],
                        [ 'ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME, 'qty' => 25 ],
                        [ 'ingredient' => IngredientEnum::ALCLAD_ALUMINUM_SHEET, 'qty' => 100 ],
                        [ 'ingredient' => IngredientEnum::CABLE, 'qty' => 200 ],
                        [ 'ingredient' => IngredientEnum::CONCRETE, 'qty' => 250 ],
                    ],
                ],
                //'mk2' => [
                //    'multiplier' => 1,
                //    'base_power' => 2500,
                //    'recipe' => [
                //        [ 'ingredient' => 'Concrete', 'qty' => 250 ],
                //        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 25 ],
                //        [ 'ingredient' => 'Supercomputer', 'qty' => 5 ],
                //        [ 'ingredient' => 'Cable', 'qty' => 100 ],
                //        [ 'ingredient' => 'Alclad Aluminum Sheet', 'qty' => 100 ],
                //    ],
                //],
                //'mk3' => [
                //    'multiplier' => 1,
                //    'base_power' => 2500,
                //    'recipe' => [
                //        [ 'ingredient' => 'Concrete', 'qty' => 250 ],
                //        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 25 ],
                //        [ 'ingredient' => 'Supercomputer', 'qty' => 5 ],
                //        [ 'ingredient' => 'Cable', 'qty' => 100 ],
                //        [ 'ingredient' => 'Alclad Aluminum Sheet', 'qty' => 100 ],
                //    ],
                //],
                //'mk4' => [
                //    'multiplier' => 1,
                //    'base_power' => 2500,
                //    'recipe' => [
                //        [ 'ingredient' => 'Concrete', 'qty' => 250 ],
                //        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 25 ],
                //        [ 'ingredient' => 'Supercomputer', 'qty' => 5 ],
                //        [ 'ingredient' => 'Cable', 'qty' => 100 ],
                //        [ 'ingredient' => 'Alclad Aluminum Sheet', 'qty' => 100 ],
                //    ],
                //],
                //'mk2' => [
                //    'multiplier' => 1.5,
                //    'base_power' => 375,
                //    'recipe' => [
                //        [ 'ingredient' => 'Heavy Modular Frame', 'qty' => 15 ],
                //        [ 'ingredient' => 'Turbo Motor', 'qty' => 5 ],
                //        [ 'ingredient' => 'Aluminum Casing', 'qty' => 75 ],
                //        [ 'ingredient' => 'Radio Control Unit', 'qty' => 10 ],
                //    ]
                //],
                //'mk3' => [
                //    'multiplier' => 2,
                //    'base_power' => 500,
                //    'recipe' => [
                //        [ 'ingredient' => 'Fused Modular Frame', 'qty' => 10 ],
                //        [ 'ingredient' => 'Turbo Motor', 'qty' => 10 ],
                //        [ 'ingredient' => 'Aluminum Casing', 'qty' => 100 ],
                //        [ 'ingredient' => 'Radio Control Unit', 'qty' => 15 ],
                //    ]
                //],
                //'mk4' => [
                //    'multiplier' => 5,
                //    'base_power' => 1000,
                //    'recipe' => [
                //        [ 'ingredient' => 'Fused Modular Frame', 'qty' => 20 ],
                //        [ 'ingredient' => 'Turbo Motor', 'qty' => 15 ],
                //        [ 'ingredient' => 'Aluminum Casing', 'qty' => 150 ],
                //        [ 'ingredient' => 'Radio Control Unit', 'qty' => 15 ],
                //        [ 'ingredient' => 'Quantum Server', 'qty' => 5 ],
                //    ]
                //],
            ]
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->buildings)
            ->each(function($atts) {
                $atts = (object) $atts;

                $building = Building::create([
                    'name' => $atts->name,
                    'inputs' => $atts->inputs,
                    'outputs' => $atts->outputs,
                    'width' => $atts->width,
                    'length' => $atts->length,
                    'height' => $atts->height,
                ]);

                foreach($atts->variants as $variant_name => $variant_atts ) {
                    $variant = $building->variants()->create([
                        'name' => $variant_name,
                        'multiplier' => $variant_atts['multiplier'],
                        'base_power' => $variant_atts['base_power'],
                    ]);

                    $variant->setRecipe($variant_atts['recipe']);
                }
            });
    }
}
