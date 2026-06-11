<?php

namespace Database\Seeders;

use App\Enums\Building as BuildingEnum;
use App\Enums\Ingredient as IngredientEnum;
use App\Models\Building;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    protected $buildings = [

        // Smelter
        [
            'name' => BuildingEnum::SMELTER->value,
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
                        ['ingredient' => IngredientEnum::IRON_ROD->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::WIRE->value, 'qty' => 8],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 6,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::REINFORCED_IRON_PLATE->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::WIRE->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::CIRCUIT_BOARD->value, 'qty' => 5],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 8,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::COMPUTER->value, 'qty' => 3],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 10,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::WIRE->value, 'qty' => 50],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 2],
                    ],
                ],
            ],

        ],

        // Constructor
        [
            'name' => BuildingEnum::CONSTRUCTOR->value,
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
                        ['ingredient' => IngredientEnum::REINFORCED_IRON_PLATE->value, 'qty' => 2],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 8],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 6,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::REINFORCED_IRON_PLATE->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 5],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 8,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 50],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 16,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 50],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 2],
                    ],
                ],
            ],
        ],

        // Assembler
        [
            'name' => BuildingEnum::ASSEMBLER->value,
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
                        ['ingredient' => IngredientEnum::REINFORCED_IRON_PLATE->value, 'qty' => 8],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::ROTOR->value, 'qty' => 4],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 22.5,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::REINFORCED_IRON_PLATE->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::CIRCUIT_BOARD->value, 'qty' => 5],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 30,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::COMPUTER->value, 'qty' => 5],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 60,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 5],
                    ],
                ],
            ],
        ],

        // Foundry
        [
            'name' => BuildingEnum::FOUNDRY->value,
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
                        ['ingredient' => IngredientEnum::MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::ROTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::CONCRETE->value, 'qty' => 20],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 24,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::IRON_ROD->value, 'qty' => 20],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 32,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::STEEL_PIPE->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 10],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 64,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::STEEL_PIPE->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 2],
                    ],
                ],
            ],
        ],

        // Packager
        [
            'name' => BuildingEnum::PACKAGER->value,
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
                        ['ingredient' => IngredientEnum::STEEL_BEAM->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::RUBBER->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::PLASTIC->value, 'qty' => 10],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 20,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::STEEL_PIPE->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::POLYMER_RESIN->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::PETROLEUM_COKE->value, 'qty' => 10],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 30,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::PACKAGED_OIL->value, 'qty' => 30],
                        ['ingredient' => IngredientEnum::PACKAGED_HEAVY_OIL_RESIDUE->value, 'qty' => 10],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 40,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::ALCLAD_ALUMINUM_SHEET->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::ALUMINUM_SCRAP->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::FABRIC->value, 'qty' => 10],
                    ],
                ],
            ],
        ],

        // Refinery
        [
            'name' => BuildingEnum::REFINERY->value,
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
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::STEEL_PIPE->value, 'qty' => 30],
                        ['ingredient' => IngredientEnum::COPPER_SHEET->value, 'qty' => 20],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 45,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::COMPUTER->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::STEEL_BEAM->value, 'qty' => 30],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 60,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 20],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 120,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 5],
                    ],
                ],
            ],
        ],

        // Manufacturer
        [
            'name' => BuildingEnum::MANUFACTURER->value,
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
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 50],
                        ['ingredient' => IngredientEnum::PLASTIC->value, 'qty' => 50],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 82.5,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::COMPUTER->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::WIRE->value, 'qty' => 50],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 110,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::BATTERY->value, 'qty' => 10],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 220,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::BATTERY->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 10],
                    ],
                ],
            ],
        ],

        // Blender
        [
            'name' => BuildingEnum::BLENDER->value,
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
                        ['ingredient' => IngredientEnum::COMPUTER->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::MOTOR->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::ALUMINUM_CASING->value, 'qty' => 50],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 75 * 1.5,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::ALUMINUM_CASING->value, 'qty' => 75],
                        ['ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 10],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 150,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::FUSED_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::ALUMINUM_CASING->value, 'qty' => 100],
                        ['ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 15],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 300,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::FUSED_MODULAR_FRAME->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::ALUMINUM_CASING->value, 'qty' => 150],
                        ['ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 5],
                    ],
                ],
            ],
        ],

        // Particle Accelerator
        [
            'name' => BuildingEnum::PARTICLE_ACCELERATOR->value,
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
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::FUSED_MODULAR_FRAME->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::COOLING_SYSTEM->value, 'qty' => 50],
                        ['ingredient' => IngredientEnum::QUICKWIRE->value, 'qty' => 500],
                    ],
                ],
                'mk2' => [
                    'multiplier' => 1.5,
                    'base_power' => 375,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 5],
                        ['ingredient' => IngredientEnum::ALUMINUM_CASING->value, 'qty' => 75],
                        ['ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 10],
                    ],
                ],
                'mk3' => [
                    'multiplier' => 2,
                    'base_power' => 500,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::FUSED_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::ALUMINUM_CASING->value, 'qty' => 100],
                        ['ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 15],
                    ],
                ],
                'mk4' => [
                    'multiplier' => 5,
                    'base_power' => 1000,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::FUSED_MODULAR_FRAME->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::ALUMINUM_CASING->value, 'qty' => 150],
                        ['ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 15],
                        ['ingredient' => IngredientEnum::QUANTUM_SERVER->value, 'qty' => 5],
                    ],
                ],
            ],
        ],

        // Converter
        [
            'name' => BuildingEnum::CONVERTER->value,
            'inputs' => 2,
            'outputs' => 2,
            'width' => 24,
            'length' => 24,
            'height' => 32,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 500,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::FUSED_MODULAR_FRAME->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::COOLING_SYSTEM->value, 'qty' => 25],
                        ['ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 50],
                        ['ingredient' => IngredientEnum::SAM_FLUCTUATOR->value, 'qty' => 100],
                    ],
                ],
            ],
        ],

        // Quantum Encoder
        [
            'name' => BuildingEnum::QUANTUM_ENCODER->value,
            'inputs' => 4,
            'outputs' => 2,
            'width' => 24,
            'length' => 38,
            'height' => 32,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 500,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::TURBO_MOTOR->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 20],
                        ['ingredient' => IngredientEnum::TIME_CRYSTAL->value, 'qty' => 50],
                        ['ingredient' => IngredientEnum::COOLING_SYSTEM->value, 'qty' => 50],
                        ['ingredient' => IngredientEnum::FICSITE_TRIGON->value, 'qty' => 100],
                    ],
                ],
            ],
        ],

        // Nuclear Power Plant
        [
            'name' => BuildingEnum::NUCLEAR_POWER_PLANT->value,
            'inputs' => 2,
            'outputs' => 1,
            'width' => 36,
            'length' => 43,
            'height' => 49,
            'variants' => [
                'mk1' => [
                    'multiplier' => 1,
                    'base_power' => 2500,
                    'is_generator' => true,
                    'recipe' => [
                        ['ingredient' => IngredientEnum::SUPERCOMPUTER->value, 'qty' => 10],
                        ['ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME->value, 'qty' => 25],
                        ['ingredient' => IngredientEnum::ALCLAD_ALUMINUM_SHEET->value, 'qty' => 100],
                        ['ingredient' => IngredientEnum::CABLE->value, 'qty' => 200],
                        ['ingredient' => IngredientEnum::CONCRETE->value, 'qty' => 250],
                    ],
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
        collect($this->buildings)
            ->each(function ($atts) {
                $atts = (object) $atts;

                $building = Building::create([
                    'name' => $atts->name,
                    'inputs' => $atts->inputs,
                    'outputs' => $atts->outputs,
                    'width' => $atts->width,
                    'length' => $atts->length,
                    'height' => $atts->height,
                ]);

                foreach ($atts->variants as $variant_name => $variant_atts) {
                    $variant = $building->variants()->create([
                        'name' => $variant_name,
                        'multiplier' => $variant_atts['multiplier'],
                        'base_power' => $variant_atts['base_power'],
                        'is_generator' => $variant_atts['is_generator'] ?? false,
                    ]);

                    $variant->setRecipe($variant_atts['recipe']);
                }
            });
    }
}
