<?php

namespace Database\Seeders;

use App\Enums\Building as BuildingEnum;
use App\Enums\Ingredient as IngredientEnum;
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
        BuildingEnum::SMELTER->value => [
            IngredientEnum::IRON_INGOT->value => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::IRON_ORE->value => 30,
                ],
            ],

            IngredientEnum::COPPER_INGOT->value => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::COPPER_ORE->value => 30,
                ],
            ],

            IngredientEnum::CATERIUM_INGOT->value => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    IngredientEnum::CATERIUM_ORE->value => 45,
                ],
            ],

            IngredientEnum::ALUMINUM_INGOT->value => [
                'description' => 'Pure Aluminum Ingot',
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::ALUMINUM_SCRAP->value => 60,
                ],
                'alt_recipe' => true,
            ],

        ],

        // alts done
        // no byproducts
        BuildingEnum::CONSTRUCTOR->value => [
            IngredientEnum::ALUMINUM_CASING->value => [
                'base_yield' => 2,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::ALUMINUM_INGOT->value => 90,
                ],
            ],

            IngredientEnum::BIOMASS->value => [
                [
                    'description' => 'Biomass (Alien Organs)',
                    'base_yield' => 200,
                    'base_per_min' => 1500,
                    'ingredients' => [
                        IngredientEnum::ALIEN_ORGANS->value => 7.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Leaves)',
                    'base_yield' => 5,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::LEAVES->value => 120,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Mycelia)',
                    'base_yield' => 10,
                    'base_per_min' => 150,
                    'ingredients' => [
                        IngredientEnum::MYCELIA->value => 150,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Wood)',
                    'base_yield' => 20,
                    'base_per_min' => 300,
                    'ingredients' => [
                        IngredientEnum::WOOD->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Biomass (Alien Protein)',
                    'base_yield' => 100,
                    'base_per_min' => 1500,
                    'ingredients' => [
                        IngredientEnum::ALIEN_PROTEIN->value => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CABLE->value => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::WIRE->value => 60,
                ],
            ],
            IngredientEnum::COAL->value => [
                [
                    'description' => 'Biocoal',
                    'base_yield' => 6,
                    'base_per_min' => 45,
                    'ingredients' => [
                        IngredientEnum::BIOMASS->value => 37.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Charcoal',
                    'base_yield' => 10,
                    'base_per_min' => 150,
                    'ingredients' => [
                        IngredientEnum::WOOD->value => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CONCRETE->value => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    IngredientEnum::LIMESTONE->value => 45,
                ],
            ],
            IngredientEnum::COPPER_POWDER->value => [
                'base_yield' => 5,
                'base_per_min' => 50,
                'ingredients' => [
                    IngredientEnum::COPPER_INGOT->value => 300,
                ],
            ],
            IngredientEnum::COPPER_SHEET->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::COPPER_INGOT->value => 20,
                ],
            ],
            IngredientEnum::EMPTY_CANISTER->value => [
                [
                    'base_yield' => 4,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::PLASTIC->value => 30,
                    ],
                ],
                [
                    'description' => 'Steel Canister',
                    'base_yield' => 2,
                    'base_per_min' => 40,
                    'ingredients' => [
                        IngredientEnum::STEEL_INGOT->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::EMPTY_FLUID_TANK->value => [
                'base_yield' => 1,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::ALUMINUM_INGOT->value => 60,
                ],
            ],
            IngredientEnum::IRON_PLATE->value => [
                'base_yield' => 2,
                'base_per_min' => 20,
                'ingredients' => [
                    IngredientEnum::IRON_INGOT->value => 30,
                ],
            ],
            IngredientEnum::IRON_ROD->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 15,
                    'ingredients' => [
                        IngredientEnum::IRON_INGOT->value => 15,
                    ],
                ],
                [
                    'description' => 'Steel Rod',
                    'base_yield' => 4,
                    'base_per_min' => 48,
                    'ingredients' => [
                        IngredientEnum::STEEL_INGOT->value => 12,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Aluminum Rod',
                    'base_yield' => 7,
                    'base_per_min' => 52.5,
                    'ingredients' => [
                        IngredientEnum::ALUMINUM_INGOT->value => 7.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::POWER_SHARD->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        IngredientEnum::BLUE_POWER_SLUG->value => 7.5,
                    ],
                ],
                [
                    'description' => 'Power Shard (1)',
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        IngredientEnum::BLUE_POWER_SLUG->value => 7.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Power Shard (2)',
                    'base_yield' => 2,
                    'base_per_min' => 10,
                    'ingredients' => [
                        IngredientEnum::YELLOW_POWER_SLUG->value => 5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Power Shard (5)',
                    'base_yield' => 5,
                    'base_per_min' => 12.5,
                    'ingredients' => [
                        IngredientEnum::PURPLE_POWER_SLUG->value => 2.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::QUARTZ_CRYSTAL->value => [
                'base_yield' => 3,
                'base_per_min' => 22.5,
                'ingredients' => [
                    IngredientEnum::RAW_QUARTZ->value => 37.5,
                ],
            ],
            IngredientEnum::QUICKWIRE->value => [
                'base_yield' => 5,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::CATERIUM_INGOT->value => 12,
                ],
            ],
            IngredientEnum::SCREW->value => [
                [
                    'base_yield' => 4,
                    'base_per_min' => 40,
                    'ingredients' => [
                        IngredientEnum::IRON_ROD->value => 10,
                    ],
                ],
                [
                    'description' => 'Cast Screw',
                    'base_yield' => 20,
                    'base_per_min' => 50,
                    'ingredients' => [
                        IngredientEnum::IRON_INGOT->value => 12.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Steel Screw',
                    'base_yield' => 52,
                    'base_per_min' => 260,
                    'ingredients' => [
                        IngredientEnum::STEEL_BEAM->value => 5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::SILICA->value => [
                'base_yield' => 5,
                'base_per_min' => 37.5,
                'ingredients' => [
                    IngredientEnum::RAW_QUARTZ->value => 22.5,
                ],
            ],
            IngredientEnum::SOLID_BIOFUEL->value => [
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::BIOMASS->value => 120,
                ],
            ],
            IngredientEnum::STEEL_BEAM->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 15,
                    'ingredients' => [
                        IngredientEnum::STEEL_INGOT->value => 60,
                    ],
                ],
                [
                    'description' => 'Aluminum Beam',
                    'base_yield' => 3,
                    'base_per_min' => 22.5,
                    'ingredients' => [
                        IngredientEnum::ALUMINUM_INGOT->value => 22.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::STEEL_PIPE->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::STEEL_INGOT->value => 30,
                    ],
                ],
                [
                    'description' => 'Iron Pipe',
                    'base_yield' => 5,
                    'base_per_min' => 25,
                    'ingredients' => [
                        IngredientEnum::IRON_INGOT->value => 100,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::WIRE->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 30,
                    'ingredients' => [
                        IngredientEnum::COPPER_INGOT->value => 15,
                    ],
                ],
                [
                    'description' => 'Caterium Wire',
                    'base_yield' => 8,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::CATERIUM_INGOT->value => 15,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Iron Wire',
                    'base_yield' => 9,
                    'base_per_min' => 22.5,
                    'ingredients' => [
                        IngredientEnum::IRON_INGOT->value => 12.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            // UpdateSix combat items
            IngredientEnum::ALIEN_PROTEIN->value => [
                [
                    'description' => 'Hog Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::HOG_REMAINS->value => 20,
                    ],
                    'alt_recipe' => false,
                ],
                [
                    'description' => 'Spitter Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::PLASMA_SPITTER_REMAINS->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Hatcher Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::HATCHER_REMAINS->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Stinger Protein',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::STINGER_REMAINS->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::ORGANIC_DATA_CAPSULE->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::ALIEN_PROTEIN->value => 10,
                ],
            ],
            IngredientEnum::IRON_REBAR->value => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    IngredientEnum::IRON_ROD->value => 15,
                ],
            ],
            // UpdateOneZero new Constructor items
            IngredientEnum::ALIEN_DNA_CAPSULE->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::ALIEN_PROTEIN->value => 10,
                ],
            ],
            IngredientEnum::FICSITE_TRIGON->value => [
                'base_yield' => 3,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::FICSITE_INGOT->value => 10,
                ],
            ],
            IngredientEnum::REANIMATED_SAM->value => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::SAM->value => 120,
                ],
            ],
        ],

        // alts done
        // no byproducts
        BuildingEnum::ASSEMBLER->value => [
            IngredientEnum::ALCLAD_ALUMINUM_SHEET->value => [
                'base_yield' => 3,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::ALUMINUM_INGOT->value => 30,
                    IngredientEnum::COPPER_INGOT->value => 10,
                ],
            ],
            IngredientEnum::ALUMINUM_CASING->value => [
                'description' => 'Alclad Casing',
                'base_yield' => 15,
                'base_per_min' => 112.5,
                'ingredients' => [
                    IngredientEnum::ALUMINUM_INGOT->value => 150,
                    IngredientEnum::COPPER_INGOT->value => 75,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::ASSEMBLY_DIRECTOR_SYSTEM->value => [
                'base_yield' => 1,
                'base_per_min' => 0.75,
                'ingredients' => [
                    IngredientEnum::ADAPTIVE_CONTROL_UNIT->value => 1.5,
                    IngredientEnum::SUPERCOMPUTER->value => 0.75,
                ],
            ],
            IngredientEnum::AUTOMATED_WIRING->value => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    IngredientEnum::STATOR->value => 2.5,
                    IngredientEnum::CABLE->value => 50,
                ],
            ],
            IngredientEnum::BLACK_POWDER->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        IngredientEnum::COAL->value => 15,
                        IngredientEnum::SULFUR->value => 15,
                    ],
                ],
                [
                    'description' => 'Fine Black Powder',
                    'base_yield' => 6,
                    'base_per_min' => 45,
                    'ingredients' => [
                        IngredientEnum::SULFUR->value => 7.5,
                        IngredientEnum::COMPACTED_COAL->value => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CABLE->value => [
                [
                    'description' => 'Insulated Cable',
                    'base_yield' => 20,
                    'base_per_min' => 100,
                    'ingredients' => [
                        IngredientEnum::WIRE->value => 45,
                        IngredientEnum::RUBBER->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Quickwire Cable',
                    'base_yield' => 11,
                    'base_per_min' => 27.5,
                    'ingredients' => [
                        IngredientEnum::QUICKWIRE->value => 7.5,
                        IngredientEnum::RUBBER->value => 5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CIRCUIT_BOARD->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        IngredientEnum::COPPER_SHEET->value => 15,
                        IngredientEnum::PLASTIC->value => 30,
                    ],
                ],
                [
                    'description' => 'Caterium Circuit Board',
                    'base_yield' => 7,
                    'base_per_min' => 8.75,
                    'ingredients' => [
                        IngredientEnum::QUICKWIRE->value => 37.5,
                        IngredientEnum::PLASTIC->value => 12.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Electrode Circuit Board',
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        IngredientEnum::RUBBER->value => 30,
                        IngredientEnum::PETROLEUM_COKE->value => 45,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Silicon Circuit Board',
                    'base_yield' => 5,
                    'base_per_min' => 12.5,
                    'ingredients' => [
                        IngredientEnum::COPPER_SHEET->value => 27.5,
                        IngredientEnum::SILICA->value => 27.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::COMPACTED_COAL->value => [
                [
                    'base_yield' => 5,
                    'base_per_min' => 25,
                    'ingredients' => [
                        IngredientEnum::COAL->value => 25,
                        IngredientEnum::SULFUR->value => 25,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::COMPUTER->value => [
                [
                    'description' => 'Crystal Computer',
                    'base_yield' => 2,
                    'base_per_min' => 3.3333,
                    'ingredients' => [
                        IngredientEnum::CIRCUIT_BOARD->value => 5,
                        IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.6667,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CONCRETE->value => [
                [
                    'description' => 'Fine Concrete',
                    'base_yield' => 10,
                    'base_per_min' => 50,
                    'ingredients' => [
                        IngredientEnum::SILICA->value => 15,
                        IngredientEnum::LIMESTONE->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Rubber Concrete',
                    'base_yield' => 9,
                    'base_per_min' => 90,
                    'ingredients' => [
                        IngredientEnum::LIMESTONE->value => 100,
                        IngredientEnum::RUBBER->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 4,
                    'ingredients' => [
                        IngredientEnum::STATOR->value => 6,
                        IngredientEnum::AI_LIMITER->value => 4,
                    ],
                ],
                [
                    'description' => 'Electromagnetic Connection Rod',
                    'base_yield' => 2,
                    'base_per_min' => 8,
                    'ingredients' => [
                        IngredientEnum::STATOR->value => 8,
                        IngredientEnum::HIGH_SPEED_CONNECTOR->value => 4,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::EMPTY_CANISTER->value => [
                'description' => 'Coated Iron Canister',
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::IRON_PLATE->value => 30,
                    IngredientEnum::COPPER_SHEET->value => 15,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 6,
                    'ingredients' => [
                        IngredientEnum::STEEL_BEAM->value => 18,
                        IngredientEnum::CONCRETE->value => 36,
                    ],
                ],
                [
                    'description' => 'Encased Industrial Pipe',
                    'base_yield' => 1,
                    'base_per_min' => 4,
                    'ingredients' => [
                        IngredientEnum::STEEL_PIPE->value => 24,
                        IngredientEnum::CONCRETE->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::ENCASED_PLUTONIUM_CELL->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::PLUTONIUM_PELLET->value => 10,
                    IngredientEnum::CONCRETE->value => 20,
                ],
            ],
            IngredientEnum::FABRIC->value => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    IngredientEnum::MYCELIA->value => 15,
                    IngredientEnum::BIOMASS->value => 75,
                ],
            ],
            IngredientEnum::HEAT_SINK->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        IngredientEnum::ALCLAD_ALUMINUM_SHEET->value => 37.5,
                        IngredientEnum::COPPER_SHEET->value => 22.5,
                    ],
                ],
                [
                    'description' => 'Heat Exchanger',
                    'base_yield' => 1,
                    'base_per_min' => 10,
                    'ingredients' => [
                        IngredientEnum::ALUMINUM_CASING->value => 30,
                        IngredientEnum::RUBBER->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::IRON_PLATE->value => [
                [
                    'description' => 'Coated Iron Plate',
                    'base_yield' => 15,
                    'base_per_min' => 75,
                    'ingredients' => [
                        IngredientEnum::IRON_INGOT->value => 50,
                        IngredientEnum::PLASTIC->value => 10,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::MODULAR_FRAME->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 2,
                    'ingredients' => [
                        IngredientEnum::REINFORCED_IRON_PLATE->value => 3,
                        IngredientEnum::IRON_ROD->value => 12,
                    ],
                ],
                [
                    'description' => 'Bolted Frame',
                    'base_yield' => 2,
                    'base_per_min' => 5,
                    'ingredients' => [
                        IngredientEnum::REINFORCED_IRON_PLATE->value => 7.5,
                        IngredientEnum::SCREW->value => 140,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Steeled Frame',
                    'base_yield' => 3,
                    'base_per_min' => 3,
                    'ingredients' => [
                        IngredientEnum::REINFORCED_IRON_PLATE->value => 2,
                        IngredientEnum::STEEL_PIPE->value => 10,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::MOTOR->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        IngredientEnum::ROTOR->value => 10,
                        IngredientEnum::STATOR->value => 10,
                    ],
                ],
                [
                    'description' => 'Electric Motor',
                    'base_yield' => 2,
                    'base_per_min' => 7.5,
                    'ingredients' => [
                        IngredientEnum::ROTOR->value => 7.5,
                        IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 3.75,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::NOBELISK->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::BLACK_POWDER->value => 20,
                    IngredientEnum::STEEL_PIPE->value => 20,
                ],
            ],
            IngredientEnum::PLUTONIUM_FUEL_ROD->value => [
                'description' => 'Plutonium Fuel Unit',
                'base_yield' => 1,
                'base_per_min' => 0.5,
                'ingredients' => [
                    IngredientEnum::ENCASED_PLUTONIUM_CELL->value => 10,
                    IngredientEnum::PRESSURE_CONVERSION_CUBE->value => 0.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PRESSURE_CONVERSION_CUBE->value => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::FUSED_MODULAR_FRAME->value => 1,
                    IngredientEnum::RADIO_CONTROL_UNIT->value => 2,
                ],
            ],
            IngredientEnum::QUICKWIRE->value => [
                'description' => 'Fused Quickwire',
                'base_yield' => 12,
                'base_per_min' => 90,
                'ingredients' => [
                    IngredientEnum::CATERIUM_INGOT->value => 7.5,
                    IngredientEnum::COPPER_INGOT->value => 37.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::REINFORCED_IRON_PLATE->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        IngredientEnum::IRON_PLATE->value => 30,
                        IngredientEnum::SCREW->value => 60,
                    ],
                ],
                [
                    'description' => 'Adhered Iron Plate',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        IngredientEnum::IRON_PLATE->value => 11.25,
                        IngredientEnum::RUBBER->value => 3.75,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Bolted Iron Plate',
                    'base_yield' => 3,
                    'base_per_min' => 15,
                    'ingredients' => [
                        IngredientEnum::IRON_PLATE->value => 90,
                        IngredientEnum::SCREW->value => 250,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Stitched Iron Plate',
                    'base_yield' => 3,
                    'base_per_min' => 5.6,
                    'ingredients' => [
                        IngredientEnum::IRON_PLATE->value => 18.75,
                        IngredientEnum::WIRE->value => 37.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::ROTOR->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 4,
                    'ingredients' => [
                        IngredientEnum::IRON_ROD->value => 20,
                        IngredientEnum::SCREW->value => 100,
                    ],
                ],
                [
                    'description' => 'Copper Rotor',
                    'base_yield' => 3,
                    'base_per_min' => 11.25,
                    'ingredients' => [
                        IngredientEnum::COPPER_SHEET->value => 22.5,
                        IngredientEnum::SCREW->value => 195,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Steel Rotor',
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        IngredientEnum::STEEL_PIPE->value => 10,
                        IngredientEnum::WIRE->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::SILICA->value => [
                'description' => 'Cheap Silica',
                'base_yield' => 7,
                'base_per_min' => 52.5,
                'ingredients' => [
                    IngredientEnum::RAW_QUARTZ->value => 22.5,
                    IngredientEnum::LIMESTONE->value => 37.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::SMART_PLATING->value => [
                'base_yield' => 1,
                'base_per_min' => 2,
                'ingredients' => [
                    IngredientEnum::REINFORCED_IRON_PLATE->value => 2,
                    IngredientEnum::ROTOR->value => 2,
                ],
            ],
            IngredientEnum::STATOR->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        IngredientEnum::STEEL_PIPE->value => 15,
                        IngredientEnum::WIRE->value => 40,
                    ],
                ],
                [
                    'description' => 'Quickwire Stator',
                    'base_yield' => 2,
                    'base_per_min' => 8,
                    'ingredients' => [
                        IngredientEnum::STEEL_PIPE->value => 16,
                        IngredientEnum::QUICKWIRE->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::SUPERCOMPUTER->value => [
                'description' => 'OC Supercomputer',
                'base_yield' => 1,
                'base_per_min' => 3,
                'ingredients' => [
                    IngredientEnum::RADIO_CONTROL_UNIT->value => 6,
                    IngredientEnum::COOLING_SYSTEM->value => 6,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::VERSATILE_FRAMEWORK->value => [
                'base_yield' => 2,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::MODULAR_FRAME->value => 2.5,
                    IngredientEnum::STEEL_BEAM->value => 30,
                ],
            ],
            IngredientEnum::WIRE->value => [
                'description' => 'Fused Wire',
                'base_yield' => 30,
                'base_per_min' => 90,
                'ingredients' => [
                    IngredientEnum::COPPER_INGOT->value => 12,
                    IngredientEnum::CATERIUM_INGOT->value => 3,
                ],
                'alt_recipe' => true,
            ],
            // UpdateSix Assembler additions
            IngredientEnum::GAS_NOBELISK->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::NOBELISK->value => 5,
                    IngredientEnum::BIOMASS->value => 50,
                ],
            ],
            IngredientEnum::PULSE_NOBELISK->value => [
                'base_yield' => 5,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::NOBELISK->value => 5,
                    IngredientEnum::CRYSTAL_OSCILLATOR->value => 1,
                ],
            ],
            IngredientEnum::CLUSTER_NOBELISK->value => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    IngredientEnum::NOBELISK->value => 7.5,
                    IngredientEnum::SMOKELESS_POWDER->value => 10,
                ],
            ],
            IngredientEnum::RIFLE_AMMO->value => [
                'base_yield' => 15,
                'base_per_min' => 75,
                'ingredients' => [
                    IngredientEnum::COPPER_SHEET->value => 15,
                    IngredientEnum::SMOKELESS_POWDER->value => 10,
                ],
            ],
            IngredientEnum::STUN_REBAR->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::IRON_REBAR->value => 10,
                    IngredientEnum::QUICKWIRE->value => 50,
                ],
            ],
            IngredientEnum::SHATTER_REBAR->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::IRON_REBAR->value => 10,
                    IngredientEnum::QUARTZ_CRYSTAL->value => 15,
                ],
            ],
            IngredientEnum::HOMING_RIFLE_AMMO->value => [
                'base_yield' => 10,
                'base_per_min' => 25,
                'ingredients' => [
                    IngredientEnum::RIFLE_AMMO->value => 50,
                    IngredientEnum::HIGH_SPEED_CONNECTOR->value => 2.5,
                ],
            ],
            // UpdateOneZero Assembler additions
            IngredientEnum::AI_LIMITER->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 5,
                    'ingredients' => [
                        IngredientEnum::COPPER_SHEET->value => 25,
                        IngredientEnum::QUICKWIRE->value => 100,
                    ],
                ],
                [
                    'description' => 'Plastic AI Limiter',
                    'base_yield' => 2,
                    'base_per_min' => 8,
                    'ingredients' => [
                        IngredientEnum::QUICKWIRE->value => 120,
                        IngredientEnum::PLASTIC->value => 28,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::MAGNETIC_FIELD_GENERATOR->value => [
                'base_yield' => 2,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::VERSATILE_FRAMEWORK->value => 2.5,
                    IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 1,
                ],
            ],
        ],

        // alts done
        // no byproducts
        BuildingEnum::FOUNDRY->value => [
            IngredientEnum::ALUMINUM_INGOT->value => [
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::ALUMINUM_SCRAP->value => 90,
                    IngredientEnum::SILICA->value => 75,
                ],
            ],
            IngredientEnum::IRON_INGOT->value => [
                [
                    'description' => 'Iron Alloy Ingot',
                    'base_yield' => 15,
                    'base_per_min' => 75,
                    'ingredients' => [
                        IngredientEnum::COPPER_ORE->value => 10,
                        IngredientEnum::IRON_ORE->value => 40,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Basic Iron Ingot',
                    'base_yield' => 10,
                    'base_per_min' => 50,
                    'ingredients' => [
                        IngredientEnum::IRON_ORE->value => 25,
                        IngredientEnum::LIMESTONE->value => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::STEEL_INGOT->value => [
                [
                    'base_yield' => 3,
                    'base_per_min' => 45,
                    'ingredients' => [
                        IngredientEnum::IRON_ORE->value => 45,
                        IngredientEnum::COAL->value => 45,
                    ],
                ],
                [
                    'description' => 'Coke Steel Ingot',
                    'base_yield' => 20,
                    'base_per_min' => 100,
                    'ingredients' => [
                        IngredientEnum::IRON_ORE->value => 75,
                        IngredientEnum::PETROLEUM_COKE->value => 75,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Compacted Steel Ingot',
                    'base_yield' => 4,
                    'base_per_min' => 10,
                    'ingredients' => [
                        IngredientEnum::IRON_ORE->value => 5,
                        IngredientEnum::COMPACTED_COAL->value => 2.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Solid Steel Ingot',
                    'base_yield' => 3,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::IRON_INGOT->value => 40,
                        IngredientEnum::COAL->value => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::QUARTZ_CRYSTAL->value => [
                'description' => 'Fused Quartz Crystal',
                'base_yield' => 18,
                'base_per_min' => 54,
                'ingredients' => [
                    IngredientEnum::RAW_QUARTZ->value => 75,
                    IngredientEnum::COAL->value => 36,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::STEEL_BEAM->value => [
                'description' => 'Molded Beam',
                'base_yield' => 9,
                'base_per_min' => 45,
                'ingredients' => [
                    IngredientEnum::STEEL_INGOT->value => 120,
                    IngredientEnum::CONCRETE->value => 80,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::STEEL_PIPE->value => [
                'description' => 'Molded Steel Pipe',
                'base_yield' => 5,
                'base_per_min' => 50,
                'ingredients' => [
                    IngredientEnum::STEEL_INGOT->value => 50,
                    IngredientEnum::CONCRETE->value => 30,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::IRON_PLATE->value => [
                'description' => 'Steel Cast Plate',
                'base_yield' => 3,
                'base_per_min' => 45,
                'ingredients' => [
                    IngredientEnum::IRON_INGOT->value => 15,
                    IngredientEnum::STEEL_INGOT->value => 15,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::CATERIUM_INGOT->value => [
                'description' => 'Tempered Caterium Ingot',
                'base_yield' => 3,
                'base_per_min' => 22.5,
                'ingredients' => [
                    IngredientEnum::CATERIUM_ORE->value => 45,
                    IngredientEnum::PETROLEUM_COKE->value => 15,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::COPPER_INGOT->value => [
                [
                    'description' => 'Copper Alloy Ingot',
                    'base_yield' => 10,
                    'base_per_min' => 100,
                    'ingredients' => [
                        IngredientEnum::COPPER_ORE->value => 50,
                        IngredientEnum::IRON_ORE->value => 50,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Tempered Copper Ingot',
                    'base_yield' => 12,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::COPPER_ORE->value => 25,
                        IngredientEnum::PETROLEUM_COKE->value => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
        ],

        // alts done
        // byproducts done
        BuildingEnum::REFINERY->value => [
            IngredientEnum::ALUMINA_SOLUTION->value => [
                [
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::BAUXITE->value => 120,
                        IngredientEnum::WATER->value => 180,
                    ],
                    'byproducts' => [
                        IngredientEnum::SILICA->value => 50,
                    ],
                ],
                [
                    'description' => 'Sloppy Alumina',
                    'base_yield' => 12,
                    'base_per_min' => 240,
                    'ingredients' => [
                        IngredientEnum::BAUXITE->value => 200,
                        IngredientEnum::WATER->value => 200,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::ALUMINUM_SCRAP->value => [
                [
                    'base_yield' => 6,
                    'base_per_min' => 360,
                    'ingredients' => [
                        IngredientEnum::ALUMINA_SOLUTION->value => 240,
                        IngredientEnum::COAL->value => 120,
                    ],
                    'byproducts' => [
                        IngredientEnum::WATER->value => 120,
                    ],
                ],
                [
                    'description' => 'Electrode - Aluminum Scrap',
                    'base_yield' => 20,
                    'base_per_min' => 300,
                    'ingredients' => [
                        IngredientEnum::ALUMINA_SOLUTION->value => 180,
                        IngredientEnum::PETROLEUM_COKE->value => 60,
                    ],
                    'byproducts' => [
                        IngredientEnum::WATER->value => 105,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CABLE->value => [
                'description' => 'Coated Cable',
                'base_yield' => 9,
                'base_per_min' => 67.5,
                'ingredients' => [
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 15,
                    IngredientEnum::WIRE->value => 37.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::CATERIUM_INGOT->value => [
                [
                    'description' => 'Pure Caterium Ingot',
                    'base_yield' => 1,
                    'base_per_min' => 12,
                    'ingredients' => [
                        IngredientEnum::CATERIUM_ORE->value => 24,
                        IngredientEnum::WATER->value => 24,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Leached Caterium Ingot',
                    'base_yield' => 6,
                    'base_per_min' => 36,
                    'ingredients' => [
                        IngredientEnum::CATERIUM_ORE->value => 54,
                        IngredientEnum::SULFURIC_ACID->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CONCRETE->value => [
                'description' => 'Wet Concrete',
                'base_yield' => 4,
                'base_per_min' => 80,
                'ingredients' => [
                    IngredientEnum::LIMESTONE->value => 120,
                    IngredientEnum::WATER->value => 100,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::COPPER_INGOT->value => [
                [
                    'description' => 'Pure Copper Ingot',
                    'base_yield' => 15,
                    'base_per_min' => 37.5,
                    'ingredients' => [
                        IngredientEnum::COPPER_ORE->value => 15,
                        IngredientEnum::WATER->value => 10,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Leached Copper Ingot',
                    'base_yield' => 22,
                    'base_per_min' => 110,
                    'ingredients' => [
                        IngredientEnum::COPPER_ORE->value => 45,
                        IngredientEnum::SULFURIC_ACID->value => 25,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::COPPER_SHEET->value => [
                'description' => 'Steamed Copper Sheet',
                'base_yield' => 3,
                'base_per_min' => 22.5,
                'ingredients' => [
                    IngredientEnum::COPPER_INGOT->value => 22.5,
                    IngredientEnum::WATER->value => 22.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::FABRIC->value => [
                'description' => 'Polyester Fabric',
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::POLYMER_RESIN->value => 80,
                    IngredientEnum::WATER->value => 50,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::FUEL->value => [
                [
                    'base_yield' => 4,
                    'base_per_min' => 40,
                    'ingredients' => [
                        IngredientEnum::CRUDE_OIL->value => 60,
                    ],
                    'byproducts' => [
                        IngredientEnum::POLYMER_RESIN->value => 30,
                    ],
                ],
                [
                    'description' => 'Residual Fuel',
                    'base_yield' => 4,
                    'base_per_min' => 40,
                    'ingredients' => [
                        IngredientEnum::HEAVY_OIL_RESIDUE->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::HEAVY_OIL_RESIDUE->value => [
                'base_yield' => 4,
                'base_per_min' => 40,
                'ingredients' => [
                    IngredientEnum::CRUDE_OIL->value => 30,
                ],
                'byproducts' => [
                    IngredientEnum::POLYMER_RESIN->value => 20,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::IRON_INGOT->value => [
                [
                    'description' => 'Pure Iron Ingot',
                    'base_yield' => 13,
                    'base_per_min' => 65,
                    'ingredients' => [
                        IngredientEnum::IRON_ORE->value => 35,
                        IngredientEnum::WATER->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Leached Iron ingot',
                    'base_yield' => 10,
                    'base_per_min' => 100,
                    'ingredients' => [
                        IngredientEnum::IRON_ORE->value => 50,
                        IngredientEnum::SULFURIC_ACID->value => 10,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::LIQUID_BIOFUEL->value => [
                'base_yield' => 4,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::SOLID_BIOFUEL->value => 90,
                    IngredientEnum::WATER->value => 45,
                ],
            ],
            IngredientEnum::PACKAGED_FUEL->value => [
                'description' => 'Diluted Packaged Fuel',
                'base_yield' => 2,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 30,
                    IngredientEnum::PACKAGED_WATER->value => 60,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PETROLEUM_COKE->value => [
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 40,
                ],
            ],
            IngredientEnum::PLASTIC->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::CRUDE_OIL->value => 30,
                    ],
                    'byproducts' => [
                        IngredientEnum::HEAVY_OIL_RESIDUE->value => 10,
                    ],
                ],
                [
                    'description' => 'Recycled Plastic',
                    'base_yield' => 12,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::RUBBER->value => 30,
                        IngredientEnum::FUEL->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Residual Plastic',
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::POLYMER_RESIN->value => 60,
                        IngredientEnum::WATER->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::POLYMER_RESIN->value => [
                'base_yield' => 13,
                'base_per_min' => 130,
                'ingredients' => [
                    IngredientEnum::CRUDE_OIL->value => 60,
                ],
                'byproducts' => [
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 20,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::IONIZED_FUEL->value => [
                'base_yield' => 16,
                'base_per_min' => 40,
                'ingredients' => [
                    IngredientEnum::ROCKET_FUEL->value => 40,
                    IngredientEnum::POWER_SHARD->value => 2.5,
                ],
                'byproducts' => [
                    IngredientEnum::COMPACTED_COAL->value => 5,
                ],
            ],
            IngredientEnum::QUARTZ_CRYSTAL->value => [
                [
                    'description' => 'Pure Quartz Crystal',
                    'base_yield' => 7,
                    'base_per_min' => 52.5,
                    'ingredients' => [
                        IngredientEnum::RAW_QUARTZ->value => 67.5,
                        IngredientEnum::WATER->value => 37.5,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Quartz Purification',
                    'base_yield' => 15,
                    'base_per_min' => 75,
                    'ingredients' => [
                        IngredientEnum::RAW_QUARTZ->value => 120,
                        IngredientEnum::NITRIC_ACID->value => 10,
                    ],
                    'byproducts' => [
                        IngredientEnum::DISSOLVED_SILICA->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::RUBBER->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::CRUDE_OIL->value => 30,
                    ],
                    'byproducts' => [
                        IngredientEnum::HEAVY_OIL_RESIDUE->value => 20,
                    ],
                ],
                [
                    'description' => 'Recycled Rubber',
                    'base_yield' => 12,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::PLASTIC->value => 30,
                        IngredientEnum::FUEL->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Residual Rubber',
                    'base_yield' => 2,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::POLYMER_RESIN->value => 40,
                        IngredientEnum::WATER->value => 20,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::DISSOLVED_SILICA->value => [
                'base_yield' => 12,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::RAW_QUARTZ->value => 120,
                    IngredientEnum::NITRIC_ACID->value => 10,
                ],
                'byproducts' => [
                    IngredientEnum::QUARTZ_CRYSTAL->value => 75,
                ],
            ],
            IngredientEnum::SMOKELESS_POWDER->value => [
                'base_yield' => 2,
                'base_per_min' => 20,
                'ingredients' => [
                    IngredientEnum::BLACK_POWDER->value => 20,
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 10,
                ],
            ],
            IngredientEnum::SULFURIC_ACID->value => [
                'base_yield' => 5,
                'base_per_min' => 50,
                'ingredients' => [
                    IngredientEnum::SULFUR->value => 50,
                    IngredientEnum::WATER->value => 50,
                ],
            ],
            IngredientEnum::TURBOFUEL->value => [
                [
                    'base_yield' => 5,
                    'base_per_min' => 18.8,
                    'ingredients' => [
                        IngredientEnum::FUEL->value => 22.5,
                        IngredientEnum::COMPACTED_COAL->value => 15,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Turbo Heavy Fuel',
                    'base_yield' => 4,
                    'base_per_min' => 30,
                    'ingredients' => [
                        IngredientEnum::HEAVY_OIL_RESIDUE->value => 37.5,
                        IngredientEnum::COMPACTED_COAL->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
        ],

        // alts done
        // byproducts done
        BuildingEnum::PACKAGER->value => [
            IngredientEnum::ALUMINA_SOLUTION->value => [
                'description' => 'Unpackage Alumina Solution',
                'base_yield' => 2,
                'base_per_min' => 120,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 120,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_ALUMINA_SOLUTION->value => 120,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_ALUMINA_SOLUTION->value => [
                'base_yield' => 2,
                'base_per_min' => 120,
                'ingredients' => [
                    IngredientEnum::ALUMINA_SOLUTION->value => 120,
                    IngredientEnum::EMPTY_CANISTER->value => 120,
                ],
            ],
            IngredientEnum::FUEL->value => [
                'description' => 'Unpackage Fuel',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 60,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_FUEL->value => 60,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_FUEL->value => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    IngredientEnum::FUEL->value => 40,
                    IngredientEnum::EMPTY_CANISTER->value => 40,
                ],
            ],
            IngredientEnum::HEAVY_OIL_RESIDUE->value => [
                'description' => 'Unpackage Heavy Oil Residue',
                'base_yield' => 2,
                'base_per_min' => 20,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 20,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_HEAVY_OIL_RESIDUE->value => 20,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_HEAVY_OIL_RESIDUE->value => [
                'base_yield' => 2,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 30,
                    IngredientEnum::EMPTY_CANISTER->value => 30,
                ],
            ],
            IngredientEnum::LIQUID_BIOFUEL->value => [
                'description' => 'Unpackage Liquid Biofuel',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 60,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_LIQUID_BIOFUEL->value => 60,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_LIQUID_BIOFUEL->value => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    IngredientEnum::LIQUID_BIOFUEL->value => 40,
                    IngredientEnum::EMPTY_CANISTER->value => 40,
                ],
            ],
            IngredientEnum::NITRIC_ACID->value => [
                'description' => 'Unpackage Nitric Acid',
                'base_yield' => 2,
                'base_per_min' => 20,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 20,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_NITRIC_ACID->value => 20,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_NITRIC_ACID->value => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::NITRIC_ACID->value => 30,
                    IngredientEnum::EMPTY_CANISTER->value => 30,
                ],
            ],
            IngredientEnum::NITROGEN_GAS->value => [
                'description' => 'Unpackage Nitrogen Gas',
                'base_yield' => 2,
                'base_per_min' => 240,
                'byproducts' => [
                    IngredientEnum::EMPTY_FLUID_TANK->value => 60,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_NITROGEN_GAS->value => 60,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_NITROGEN_GAS->value => [
                'base_yield' => 1,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::NITROGEN_GAS->value => 240,
                    IngredientEnum::EMPTY_FLUID_TANK->value => 60,
                ],
            ],
            IngredientEnum::CRUDE_OIL->value => [
                'description' => 'Unpackage Oil',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 60,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_OIL->value => 60,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_OIL->value => [
                'base_yield' => 2,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::CRUDE_OIL->value => 30,
                    IngredientEnum::EMPTY_CANISTER->value => 30,
                ],
            ],
            IngredientEnum::SULFURIC_ACID->value => [
                'description' => 'Unpackage Sulfuric Acid',
                'base_yield' => 2,
                'base_per_min' => 60,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 60,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_SULFURIC_ACID->value => 60,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_SULFURIC_ACID->value => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    IngredientEnum::SULFURIC_ACID->value => 40,
                    IngredientEnum::EMPTY_CANISTER->value => 40,
                ],
            ],
            IngredientEnum::TURBOFUEL->value => [
                'description' => 'Unpackage Turbofuel',
                'base_yield' => 2,
                'base_per_min' => 20,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 20,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_TURBOFUEL->value => 20,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_TURBOFUEL->value => [
                'base_yield' => 2,
                'base_per_min' => 20,
                'ingredients' => [
                    IngredientEnum::TURBOFUEL->value => 20,
                    IngredientEnum::EMPTY_CANISTER->value => 20,
                ],
            ],
            IngredientEnum::WATER->value => [
                'description' => 'Unpackage Water',
                'base_yield' => 2,
                'base_per_min' => 120,
                'byproducts' => [
                    IngredientEnum::EMPTY_CANISTER->value => 120,
                ],
                'ingredients' => [
                    IngredientEnum::PACKAGED_WATER->value => 120,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PACKAGED_WATER->value => [
                'base_yield' => 2,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::WATER->value => 60,
                    IngredientEnum::EMPTY_CANISTER->value => 60,
                ],
            ],
            IngredientEnum::PACKAGED_IONIZED_FUEL->value => [
                'base_yield' => 2,
                'base_per_min' => 40,
                'ingredients' => [
                    IngredientEnum::IONIZED_FUEL->value => 80,
                    IngredientEnum::EMPTY_FLUID_TANK->value => 40,
                ],
            ],
            IngredientEnum::PACKAGED_ROCKET_FUEL->value => [
                'base_yield' => 1,
                'base_per_min' => 60,
                'ingredients' => [
                    IngredientEnum::ROCKET_FUEL->value => 120,
                    IngredientEnum::EMPTY_FLUID_TANK->value => 60,
                ],
            ],
            IngredientEnum::IONIZED_FUEL->value => [
                'description' => 'Unpackage Ionized Fuel',
                'base_yield' => 4,
                'base_per_min' => 80,
                'ingredients' => [
                    IngredientEnum::PACKAGED_IONIZED_FUEL->value => 40,
                ],
                'byproducts' => [
                    IngredientEnum::EMPTY_FLUID_TANK->value => 40,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::ROCKET_FUEL->value => [
                'description' => 'Unpackage Rocket Fuel',
                'base_yield' => 2,
                'base_per_min' => 120,
                'ingredients' => [
                    IngredientEnum::PACKAGED_ROCKET_FUEL->value => 60,
                ],
                'byproducts' => [
                    IngredientEnum::EMPTY_FLUID_TANK->value => 60,
                ],
                'alt_recipe' => true,
            ],
        ],

        // alts done
        // no byproducts
        BuildingEnum::MANUFACTURER->value => [
            IngredientEnum::ADAPTIVE_CONTROL_UNIT->value => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::AUTOMATED_WIRING->value => 5,
                    IngredientEnum::CIRCUIT_BOARD->value => 5,
                    IngredientEnum::HEAVY_MODULAR_FRAME->value => 1,
                    IngredientEnum::COMPUTER->value => 2,
                ],
            ],
            IngredientEnum::AUTOMATED_WIRING->value => [
                'description' => 'Automated Speed Wiring',
                'base_yield' => 4,
                'base_per_min' => 7.5,
                'ingredients' => [
                    IngredientEnum::STATOR->value => 3.75,
                    IngredientEnum::WIRE->value => 75,
                    IngredientEnum::HIGH_SPEED_CONNECTOR->value => 1.875,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::BATTERY->value => [
                'description' => 'Classic Battery',
                'base_yield' => 4,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::SULFUR->value => 45,
                    IngredientEnum::ALCLAD_ALUMINUM_SHEET->value => 52.5,
                    IngredientEnum::PLASTIC->value => 60,
                    IngredientEnum::WIRE->value => 90,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::COMPUTER->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 2.5,
                    'ingredients' => [
                        IngredientEnum::CIRCUIT_BOARD->value => 10,
                        IngredientEnum::CABLE->value => 20,
                        IngredientEnum::PLASTIC->value => 40,
                    ],
                ],
                [
                    'description' => 'Caterium Computer',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        IngredientEnum::CIRCUIT_BOARD->value => 15,
                        IngredientEnum::QUICKWIRE->value => 52.5,
                        IngredientEnum::RUBBER->value => 22.5,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CRYSTAL_OSCILLATOR->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 1,
                    'ingredients' => [
                        IngredientEnum::QUARTZ_CRYSTAL->value => 18,
                        IngredientEnum::CABLE->value => 14,
                        IngredientEnum::REINFORCED_IRON_PLATE->value => 2.5,
                    ],
                ],
                [
                    'description' => 'Insulated Crystal Oscillator',
                    'base_yield' => 1,
                    'base_per_min' => 1.875,
                    'ingredients' => [
                        IngredientEnum::QUARTZ_CRYSTAL->value => 18.75,
                        IngredientEnum::RUBBER->value => 13.125,
                        IngredientEnum::AI_LIMITER->value => 1.875,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::ENCASED_URANIUM_CELL->value => [
                'description' => 'Infused Uranium Cell',
                'base_yield' => 4,
                'base_per_min' => 20,
                'ingredients' => [
                    IngredientEnum::URANIUM->value => 25,
                    IngredientEnum::SILICA->value => 15,
                    IngredientEnum::SULFUR->value => 25,
                    IngredientEnum::QUICKWIRE->value => 75,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::GAS_FILTER->value => [
                'base_yield' => 1,
                'base_per_min' => 7.5,
                'ingredients' => [
                    IngredientEnum::FABRIC->value => 15,
                    IngredientEnum::COAL->value => 30,
                    IngredientEnum::IRON_PLATE->value => 15,
                ],
            ],
            IngredientEnum::HEAVY_MODULAR_FRAME->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 2,
                    'ingredients' => [
                        IngredientEnum::MODULAR_FRAME->value => 10,
                        IngredientEnum::STEEL_PIPE->value => 40,
                        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => 10,
                        IngredientEnum::SCREW->value => 240,
                    ],
                ],
                [
                    'description' => 'Heavy Flexible Frame',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        IngredientEnum::MODULAR_FRAME->value => 18.75,
                        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => 11.25,
                        IngredientEnum::RUBBER->value => 75,
                        IngredientEnum::SCREW->value => 390,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Heavy Encased Frame',
                    'base_yield' => 3,
                    'base_per_min' => 2.8125,
                    'ingredients' => [
                        IngredientEnum::MODULAR_FRAME->value => 7.5,
                        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => 9.375,
                        IngredientEnum::STEEL_PIPE->value => 33.75,
                        IngredientEnum::CONCRETE->value => 20.625,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::HIGH_SPEED_CONNECTOR->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        IngredientEnum::QUICKWIRE->value => 210,
                        IngredientEnum::CABLE->value => 37.5,
                        IngredientEnum::CIRCUIT_BOARD->value => 3.75,
                    ],
                ],
                [
                    'description' => 'Silicon High-Speed Connector',
                    'base_yield' => 2,
                    'base_per_min' => 3,
                    'ingredients' => [
                        IngredientEnum::QUICKWIRE->value => 90,
                        IngredientEnum::SILICA->value => 37.5,
                        IngredientEnum::CIRCUIT_BOARD->value => 3,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::IODINE_INFUSED_FILTER->value => [
                'base_yield' => 1,
                'base_per_min' => 3.75,
                'ingredients' => [
                    IngredientEnum::GAS_FILTER->value => 3.75,
                    IngredientEnum::QUICKWIRE->value => 30,
                    IngredientEnum::ALUMINUM_CASING->value => 3.75,
                ],
            ],
            IngredientEnum::MODULAR_ENGINE->value => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::MOTOR->value => 2,
                    IngredientEnum::RUBBER->value => 15,
                    IngredientEnum::SMART_PLATING->value => 2,
                ],
            ],
            IngredientEnum::MOTOR->value => [
                'description' => 'Rigor Motor',
                'base_yield' => 6,
                'base_per_min' => 7.5,
                'ingredients' => [
                    IngredientEnum::ROTOR->value => 3.75,
                    IngredientEnum::STATOR->value => 3.75,
                    IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.25,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::NOBELISK->value => [
                'description' => 'Seismic Nobelisk',
                'base_yield' => 4,
                'base_per_min' => 6,
                'ingredients' => [
                    IngredientEnum::BLACK_POWDER->value => 12,
                    IngredientEnum::STEEL_PIPE->value => 12,
                    IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PORTABLE_MINER->value => [
                'description' => 'Automated Miner',
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::MOTOR->value => 1,
                    IngredientEnum::STEEL_PIPE->value => 4,
                    IngredientEnum::IRON_ROD->value => 4,
                    IngredientEnum::IRON_PLATE->value => 2,
                ],
            ],
            IngredientEnum::PLUTONIUM_FUEL_ROD->value => [
                'base_yield' => 1,
                'base_per_min' => 0.25,
                'ingredients' => [
                    IngredientEnum::ENCASED_PLUTONIUM_CELL->value => 7.5,
                    IngredientEnum::STEEL_BEAM->value => 4.5,
                    IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 1.5,
                    IngredientEnum::HEAT_SINK->value => 2.5,
                ],
            ],
            IngredientEnum::RADIO_CONTROL_UNIT->value => [
                [
                    'base_yield' => 2,
                    'base_per_min' => 2.5,
                    'ingredients' => [
                        IngredientEnum::ALUMINUM_CASING->value => 40,
                        IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.25,
                        IngredientEnum::COMPUTER->value => 2.5,
                    ],
                ],
                [
                    'description' => 'Radio Connection Unit',
                    'base_yield' => 1,
                    'base_per_min' => 3.75,
                    'ingredients' => [
                        IngredientEnum::HEAT_SINK->value => 15,
                        IngredientEnum::HIGH_SPEED_CONNECTOR->value => 7.5,
                        IngredientEnum::QUARTZ_CRYSTAL->value => 45,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Radio Control System',
                    'base_yield' => 3,
                    'base_per_min' => 4.5,
                    'ingredients' => [
                        IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.5,
                        IngredientEnum::CIRCUIT_BOARD->value => 15,
                        IngredientEnum::ALUMINUM_CASING->value => 90,
                        IngredientEnum::RUBBER->value => 45,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::SUPERCOMPUTER->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 1.875,
                    'ingredients' => [
                        IngredientEnum::COMPUTER->value => 7.5,
                        IngredientEnum::AI_LIMITER->value => 3.75,
                        IngredientEnum::HIGH_SPEED_CONNECTOR->value => 5.625,
                        IngredientEnum::PLASTIC->value => 52.5,
                    ],
                ],
                [
                    'description' => 'Super-State Computer',
                    'base_yield' => 1,
                    'base_per_min' => 2.4,
                    'ingredients' => [
                        IngredientEnum::COMPUTER->value => 7.2,
                        IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 2.4,
                        IngredientEnum::BATTERY->value => 24,
                        IngredientEnum::WIRE->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::SMART_PLATING->value => [
                'description' => 'Plastic Smart Plating',
                'base_yield' => 2,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::REINFORCED_IRON_PLATE->value => 2.5,
                    IngredientEnum::ROTOR->value => 2.5,
                    IngredientEnum::PLASTIC->value => 7.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::THERMAL_PROPULSION_ROCKET->value => [
                'base_yield' => 2,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::MODULAR_ENGINE->value => 2.5,
                    IngredientEnum::TURBO_MOTOR->value => 1,
                    IngredientEnum::COOLING_SYSTEM->value => 3,
                    IngredientEnum::FUSED_MODULAR_FRAME->value => 1,
                ],
            ],
            IngredientEnum::TURBO_MOTOR->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 1.9,
                    'ingredients' => [
                        IngredientEnum::COOLING_SYSTEM->value => 7.5,
                        IngredientEnum::RADIO_CONTROL_UNIT->value => 3.75,
                        IngredientEnum::MOTOR->value => 7.5,
                        IngredientEnum::RUBBER->value => 45,
                    ],
                ],
                [
                    'description' => 'Turbo Electric Motor',
                    'base_yield' => 3,
                    'base_per_min' => 2.8,
                    'ingredients' => [
                        IngredientEnum::MOTOR->value => 6.5625,
                        IngredientEnum::RADIO_CONTROL_UNIT->value => 8.4375,
                        IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 4.6875,
                        IngredientEnum::ROTOR->value => 6.5625,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Turbo Pressure Motor',
                    'base_yield' => 2,
                    'base_per_min' => 3.8,
                    'ingredients' => [
                        IngredientEnum::MOTOR->value => 7.5,
                        IngredientEnum::PRESSURE_CONVERSION_CUBE->value => 1.875,
                        IngredientEnum::PACKAGED_NITROGEN_GAS->value => 45,
                        IngredientEnum::STATOR->value => 15,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::QUANTUM_SERVER->value => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::SUPERCOMPUTER->value => 10,
                    IngredientEnum::AI_LIMITER->value => 50,
                    IngredientEnum::HEAVY_MODULAR_FRAME->value => 25,
                ],
            ],
            IngredientEnum::URANIUM_FUEL_ROD->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 0.4,
                    'ingredients' => [
                        IngredientEnum::ENCASED_URANIUM_CELL->value => 20,
                        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => 1.2,
                        IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 2,
                    ],
                ],
                [
                    'description' => 'Uranium Fuel Unit',
                    'base_yield' => 3,
                    'base_per_min' => 0.6,
                    'ingredients' => [
                        IngredientEnum::ENCASED_URANIUM_CELL->value => 20,
                        IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 2,
                        IngredientEnum::CRYSTAL_OSCILLATOR->value => 0.6,
                        IngredientEnum::ROTOR->value => 2,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::VERSATILE_FRAMEWORK->value => [
                'description' => 'Flexible Framework',
                'base_yield' => 2,
                'base_per_min' => 7.5,
                'ingredients' => [
                    IngredientEnum::MODULAR_FRAME->value => 3.75,
                    IngredientEnum::STEEL_BEAM->value => 22.5,
                    IngredientEnum::RUBBER->value => 30,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::BALLISTIC_WARP_DRIVE->value => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::THERMAL_PROPULSION_ROCKET->value => 1,
                    IngredientEnum::SINGULARITY_CELL->value => 5,
                    IngredientEnum::SUPERPOSITION_OSCILLATOR->value => 2,
                    IngredientEnum::DARK_MATTER_CRYSTAL->value => 40,
                ],
            ],
            IngredientEnum::SAM_FLUCTUATOR->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::REANIMATED_SAM->value => 60,
                    IngredientEnum::WIRE->value => 50,
                    IngredientEnum::STEEL_PIPE->value => 30,
                ],
            ],
            IngredientEnum::SINGULARITY_CELL->value => [
                'base_yield' => 10,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::NUCLEAR_PASTA->value => 1,
                    IngredientEnum::DARK_MATTER_CRYSTAL->value => 20,
                    IngredientEnum::IRON_PLATE->value => 100,
                    IngredientEnum::CONCRETE->value => 200,
                ],
            ],
            IngredientEnum::COOLING_SYSTEM->value => [
                'description' => 'Cooling Device',
                'base_yield' => 2,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::HEAT_SINK->value => 10,
                    IngredientEnum::MOTOR->value => 2.5,
                    IngredientEnum::NITROGEN_GAS->value => 60,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::EXPLOSIVE_REBAR->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::IRON_REBAR->value => 10,
                    IngredientEnum::SMOKELESS_POWDER->value => 10,
                    IngredientEnum::STEEL_PIPE->value => 10,
                ],
            ],
            IngredientEnum::NUKE_NOBELISK->value => [
                'base_yield' => 1,
                'base_per_min' => 0.5,
                'ingredients' => [
                    IngredientEnum::ENCASED_URANIUM_CELL->value => 10,
                    IngredientEnum::NOBELISK->value => 2.5,
                    IngredientEnum::SMOKELESS_POWDER->value => 5,
                    IngredientEnum::AI_LIMITER->value => 3,
                ],
            ],
            IngredientEnum::TURBO_RIFLE_AMMO->value => [
                'base_yield' => 50,
                'base_per_min' => 250,
                'ingredients' => [
                    IngredientEnum::RIFLE_AMMO->value => 125,
                    IngredientEnum::ALUMINUM_CASING->value => 15,
                    IngredientEnum::PACKAGED_TURBOFUEL->value => 15,
                ],
            ],
        ],

        // alts done
        // byproducts done
        BuildingEnum::BLENDER->value => [
            IngredientEnum::ALUMINUM_SCRAP->value => [
                'description' => 'Instant Scrap',
                'base_yield' => 30,
                'base_per_min' => 300,
                'ingredients' => [
                    IngredientEnum::BAUXITE->value => 150,
                    IngredientEnum::COAL->value => 100,
                    IngredientEnum::SULFURIC_ACID->value => 50,
                    IngredientEnum::WATER->value => 60,
                ],
                'byproducts' => [
                    IngredientEnum::WATER->value => 50,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::BATTERY->value => [
                'base_yield' => 1,
                'base_per_min' => 20,
                'ingredients' => [
                    IngredientEnum::SULFURIC_ACID->value => 50,
                    IngredientEnum::ALUMINA_SOLUTION->value => 40,
                    IngredientEnum::ALUMINUM_CASING->value => 20,
                ],
                'byproducts' => [
                    IngredientEnum::WATER->value => 30,
                ],
            ],
            IngredientEnum::COOLING_SYSTEM->value => [
                [
                    'description' => 'Cooling Device',
                    'base_yield' => 2,
                    'base_per_min' => 3.8,
                    'ingredients' => [
                        IngredientEnum::HEAT_SINK->value => 9.375,
                        IngredientEnum::MOTOR->value => 1.875,
                        IngredientEnum::NITROGEN_GAS->value => 45,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'base_yield' => 1,
                    'base_per_min' => 6,
                    'ingredients' => [
                        IngredientEnum::HEAT_SINK->value => 12,
                        IngredientEnum::RUBBER->value => 12,
                        IngredientEnum::WATER->value => 30,
                        IngredientEnum::NITROGEN_GAS->value => 150,
                    ],
                ],
            ],
            IngredientEnum::ENCASED_URANIUM_CELL->value => [
                'base_yield' => 5,
                'base_per_min' => 25,
                'ingredients' => [
                    IngredientEnum::URANIUM->value => 50,
                    IngredientEnum::CONCRETE->value => 15,
                    IngredientEnum::SULFURIC_ACID->value => 40,
                ],
                'byproducts' => [
                    IngredientEnum::SULFURIC_ACID->value => 10,
                ],
            ],
            IngredientEnum::FUEL->value => [
                'description' => 'Diluted Fuel',
                'base_yield' => 10,
                'base_per_min' => 100,
                'ingredients' => [
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 50,
                    IngredientEnum::WATER->value => 100,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::FUSED_MODULAR_FRAME->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 1.5,
                    'ingredients' => [
                        IngredientEnum::HEAVY_MODULAR_FRAME->value => 1.5,
                        IngredientEnum::ALUMINUM_CASING->value => 75,
                        IngredientEnum::NITROGEN_GAS->value => 37.5,
                    ],
                ],
                [
                    'description' => 'Heat-Fused Frame',
                    'base_yield' => 1,
                    'base_per_min' => 3,
                    'ingredients' => [
                        IngredientEnum::HEAVY_MODULAR_FRAME->value => 3,
                        IngredientEnum::ALUMINUM_INGOT->value => 150,
                        IngredientEnum::NITRIC_ACID->value => 24,
                        IngredientEnum::FUEL->value => 30,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::NITRIC_ACID->value => [
                'base_yield' => 3,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::NITROGEN_GAS->value => 120,
                    IngredientEnum::WATER->value => 30,
                    IngredientEnum::IRON_PLATE->value => 10,
                ],
            ],
            IngredientEnum::NON_FISSILE_URANIUM->value => [
                [
                    'description' => 'Fertile Uranium',
                    'base_yield' => 20,
                    'base_per_min' => 100,
                    'ingredients' => [
                        IngredientEnum::URANIUM->value => 25,
                        IngredientEnum::URANIUM_WASTE->value => 25,
                        IngredientEnum::NITRIC_ACID->value => 15,
                        IngredientEnum::SULFURIC_ACID->value => 25,
                    ],
                    'byproducts' => [
                        IngredientEnum::WATER->value => 40,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'base_yield' => 20,
                    'base_per_min' => 50,
                    'ingredients' => [
                        IngredientEnum::URANIUM_WASTE->value => 37.5,
                        IngredientEnum::SILICA->value => 25,
                        IngredientEnum::NITRIC_ACID->value => 15,
                        IngredientEnum::SULFURIC_ACID->value => 15,
                    ],
                    'byproducts' => [
                        IngredientEnum::WATER->value => 15,
                    ],
                ],
            ],
            IngredientEnum::TURBOFUEL->value => [
                'description' => 'Turbo Blend Fuel',
                'base_yield' => 6,
                'base_per_min' => 45,
                'ingredients' => [
                    IngredientEnum::FUEL->value => 15,
                    IngredientEnum::HEAVY_OIL_RESIDUE->value => 30,
                    IngredientEnum::SULFUR->value => 22.5,
                    IngredientEnum::PETROLEUM_COKE->value => 22.5,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::BIOCHEMICAL_SCULPTOR->value => [
                'base_yield' => 4,
                'base_per_min' => 2,
                'ingredients' => [
                    IngredientEnum::ASSEMBLY_DIRECTOR_SYSTEM->value => 0.5,
                    IngredientEnum::FICSITE_TRIGON->value => 40,
                    IngredientEnum::WATER->value => 10,
                ],
            ],
            IngredientEnum::SILICA->value => [
                'description' => 'Distilled Silica',
                'base_yield' => 27,
                'base_per_min' => 270,
                'ingredients' => [
                    IngredientEnum::DISSOLVED_SILICA->value => 120,
                    IngredientEnum::LIMESTONE->value => 50,
                    IngredientEnum::WATER->value => 100,
                ],
                'byproducts' => [
                    IngredientEnum::WATER->value => 80,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::ROCKET_FUEL->value => [
                [
                    'base_yield' => 10,
                    'base_per_min' => 100,
                    'ingredients' => [
                        IngredientEnum::TURBOFUEL->value => 60,
                        IngredientEnum::NITRIC_ACID->value => 10,
                    ],
                    'byproducts' => [
                        IngredientEnum::COMPACTED_COAL->value => 10,
                    ],
                ],
                [
                    'description' => 'Nitro Rocket Fuel',
                    'base_yield' => 6,
                    'base_per_min' => 150,
                    'ingredients' => [
                        IngredientEnum::FUEL->value => 100,
                        IngredientEnum::NITROGEN_GAS->value => 75,
                        IngredientEnum::SULFUR->value => 100,
                        IngredientEnum::COAL->value => 50,
                    ],
                    'byproducts' => [
                        IngredientEnum::COMPACTED_COAL->value => 25,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::TURBO_RIFLE_AMMO->value => [
                'base_yield' => 50,
                'base_per_min' => 250,
                'ingredients' => [
                    IngredientEnum::RIFLE_AMMO->value => 125,
                    IngredientEnum::ALUMINUM_CASING->value => 15,
                    IngredientEnum::TURBOFUEL->value => 15,
                ],
            ],
        ],

        // alts done
        // no byproducts
        BuildingEnum::PARTICLE_ACCELERATOR->value => [
            IngredientEnum::ENCASED_PLUTONIUM_CELL->value => [
                'description' => 'Instant Plutonium Cell',
                'base_yield' => 20,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::NON_FISSILE_URANIUM->value => 75,
                    IngredientEnum::ALUMINUM_CASING->value => 10,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::PLUTONIUM_PELLET->value => [
                'base_yield' => 30,
                'base_per_min' => 30,
                'ingredients' => [
                    IngredientEnum::NON_FISSILE_URANIUM->value => 100,
                    IngredientEnum::URANIUM_WASTE->value => 25,
                ],
            ],
            IngredientEnum::NUCLEAR_PASTA->value => [
                'base_yield' => 1,
                'base_per_min' => 0.5,
                'ingredients' => [
                    IngredientEnum::COPPER_POWDER->value => 100,
                    IngredientEnum::PRESSURE_CONVERSION_CUBE->value => 0.5,
                ],
            ],
            IngredientEnum::DIAMONDS->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        IngredientEnum::COAL->value => 600,
                    ],
                ],
                [
                    'description' => 'Cloudy Diamonds',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::COAL->value => 240,
                        IngredientEnum::LIMESTONE->value => 480,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Oil-Based Diamonds',
                    'base_yield' => 2,
                    'base_per_min' => 40,
                    'ingredients' => [
                        IngredientEnum::CRUDE_OIL->value => 200,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Petroleum Diamonds',
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        IngredientEnum::PETROLEUM_COKE->value => 720,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Turbo Diamonds',
                    'base_yield' => 3,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::COAL->value => 600,
                        IngredientEnum::PACKAGED_TURBOFUEL->value => 40,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::DARK_MATTER_CRYSTAL->value => [
                [
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        IngredientEnum::DIAMONDS->value => 30,
                        IngredientEnum::DARK_MATTER_RESIDUE->value => 150,
                    ],
                ],
                [
                    'description' => 'Dark Matter Crystallization',
                    'base_yield' => 1,
                    'base_per_min' => 20,
                    'ingredients' => [
                        IngredientEnum::DARK_MATTER_RESIDUE->value => 200,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Dark Matter Trap',
                    'base_yield' => 2,
                    'base_per_min' => 60,
                    'ingredients' => [
                        IngredientEnum::TIME_CRYSTAL->value => 30,
                        IngredientEnum::DARK_MATTER_RESIDUE->value => 150,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::FICSONIUM->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::PLUTONIUM_WASTE->value => 10,
                    IngredientEnum::SINGULARITY_CELL->value => 10,
                    IngredientEnum::DARK_MATTER_RESIDUE->value => 200,
                ],
            ],
        ],

        // no alts
        BuildingEnum::NUCLEAR_POWER_PLANT->value => [
            IngredientEnum::URANIUM_WASTE->value => [
                'base_yield' => 50,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::URANIUM_FUEL_ROD->value => 0.2,
                    IngredientEnum::WATER->value => 300,
                ],
            ],
            IngredientEnum::PLUTONIUM_WASTE->value => [
                'base_yield' => 10,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::PLUTONIUM_FUEL_ROD->value => 0.1,
                    IngredientEnum::WATER->value => 300,
                ],
            ],
        ],

        BuildingEnum::QUANTUM_ENCODER->value => [
            IngredientEnum::AI_EXPANSION_SERVER->value => [
                'base_yield' => 1,
                'base_per_min' => 4,
                'ingredients' => [
                    IngredientEnum::MAGNETIC_FIELD_GENERATOR->value => 4,
                    IngredientEnum::NEURAL_QUANTUM_PROCESSOR->value => 4,
                    IngredientEnum::SUPERPOSITION_OSCILLATOR->value => 4,
                    IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 100,
                ],
                'byproducts' => [
                    IngredientEnum::DARK_MATTER_RESIDUE->value => 100,
                ],
            ],
            IngredientEnum::ALIEN_POWER_MATRIX->value => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    IngredientEnum::SAM_FLUCTUATOR->value => 12.5,
                    IngredientEnum::POWER_SHARD->value => 7.5,
                    IngredientEnum::SUPERPOSITION_OSCILLATOR->value => 7.5,
                    IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 60,
                ],
                'byproducts' => [
                    IngredientEnum::DARK_MATTER_RESIDUE->value => 60,
                ],
            ],
            IngredientEnum::FICSONIUM_FUEL_ROD->value => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    IngredientEnum::FICSONIUM->value => 5,
                    IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 5,
                    IngredientEnum::FICSITE_TRIGON->value => 100,
                    IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 50,
                ],
                'byproducts' => [
                    IngredientEnum::DARK_MATTER_RESIDUE->value => 50,
                ],
            ],
            IngredientEnum::NEURAL_QUANTUM_PROCESSOR->value => [
                'base_yield' => 1,
                'base_per_min' => 3,
                'ingredients' => [
                    IngredientEnum::TIME_CRYSTAL->value => 15,
                    IngredientEnum::SUPERCOMPUTER->value => 3,
                    IngredientEnum::FICSITE_TRIGON->value => 45,
                    IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 75,
                ],
                'byproducts' => [
                    IngredientEnum::DARK_MATTER_RESIDUE->value => 75,
                ],
            ],
            IngredientEnum::SUPERPOSITION_OSCILLATOR->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::DARK_MATTER_CRYSTAL->value => 30,
                    IngredientEnum::CRYSTAL_OSCILLATOR->value => 5,
                    IngredientEnum::ALCLAD_ALUMINUM_SHEET->value => 45,
                    IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 125,
                ],
                'byproducts' => [
                    IngredientEnum::DARK_MATTER_RESIDUE->value => 125,
                ],
            ],
            IngredientEnum::POWER_SHARD->value => [
                'description' => 'Synthetic Power Shard',
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::TIME_CRYSTAL->value => 10,
                    IngredientEnum::DARK_MATTER_CRYSTAL->value => 10,
                    IngredientEnum::QUARTZ_CRYSTAL->value => 60,
                    IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 60,
                ],
                'byproducts' => [
                    IngredientEnum::DARK_MATTER_RESIDUE->value => 60,
                ],
                'alt_recipe' => true,
            ],
        ],

        BuildingEnum::CONVERTER->value => [
            IngredientEnum::BAUXITE->value => [
                [
                    'description' => 'Bauxite (Caterium)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::CATERIUM_ORE->value => 150,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Bauxite (Copper)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::COPPER_ORE->value => 180,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::CATERIUM_ORE->value => [
                [
                    'description' => 'Caterium Ore (Copper)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::COPPER_ORE->value => 150,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Caterium Ore (Quartz)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::RAW_QUARTZ->value => 120,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::COAL->value => [
                [
                    'description' => 'Coal (Iron)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::IRON_ORE->value => 180,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Coal (Limestone)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::LIMESTONE->value => 360,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::COPPER_ORE->value => [
                [
                    'description' => 'Copper Ore (Quartz)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::RAW_QUARTZ->value => 100,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Copper Ore (Sulfur)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::SULFUR->value => 120,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::DARK_MATTER_RESIDUE->value => [
                'base_yield' => 10,
                'base_per_min' => 100,
                'ingredients' => [
                    IngredientEnum::REANIMATED_SAM->value => 50,
                ],
            ],
            IngredientEnum::IONIZED_FUEL->value => [
                'description' => 'Dark-Ion Fuel',
                'base_yield' => 10,
                'base_per_min' => 200,
                'ingredients' => [
                    IngredientEnum::PACKAGED_ROCKET_FUEL->value => 240,
                    IngredientEnum::DARK_MATTER_CRYSTAL->value => 80,
                ],
                'byproducts' => [
                    IngredientEnum::COMPACTED_COAL->value => 40,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::EXCITED_PHOTONIC_MATTER->value => [
                'base_yield' => 10,
                'base_per_min' => 200,
                'ingredients' => [],
            ],
            IngredientEnum::FICSITE_INGOT->value => [
                [
                    'description' => 'Ficsite Ingot (Aluminum)',
                    'base_yield' => 1,
                    'base_per_min' => 30,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 60,
                        IngredientEnum::ALUMINUM_INGOT->value => 120,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Ficsite Ingot (Caterium)',
                    'base_yield' => 1,
                    'base_per_min' => 15,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 45,
                        IngredientEnum::CATERIUM_INGOT->value => 60,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Ficsite Ingot (Iron)',
                    'base_yield' => 1,
                    'base_per_min' => 10,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 40,
                        IngredientEnum::IRON_INGOT->value => 240,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::IRON_ORE->value => [
                'description' => 'Iron Ore (Limestone)',
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    IngredientEnum::REANIMATED_SAM->value => 10,
                    IngredientEnum::LIMESTONE->value => 240,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::LIMESTONE->value => [
                'description' => 'Limestone (Sulfur)',
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    IngredientEnum::REANIMATED_SAM->value => 10,
                    IngredientEnum::SULFUR->value => 20,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::NITROGEN_GAS->value => [
                [
                    'description' => 'Nitrogen Gas (Bauxite)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::BAUXITE->value => 100,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Nitrogen Gas (Caterium)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::CATERIUM_ORE->value => 120,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::DIAMONDS->value => [
                'description' => 'Pink Diamonds',
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    IngredientEnum::COAL->value => 120,
                    IngredientEnum::QUARTZ_CRYSTAL->value => 45,
                ],
                'alt_recipe' => true,
            ],
            IngredientEnum::RAW_QUARTZ->value => [
                [
                    'description' => 'Raw Quartz (Bauxite)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::BAUXITE->value => 100,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Raw Quartz (Coal)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::COAL->value => 240,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::SULFUR->value => [
                [
                    'description' => 'Sulfur (Coal)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::COAL->value => 200,
                    ],
                    'alt_recipe' => true,
                ],
                [
                    'description' => 'Sulfur (Iron)',
                    'base_yield' => 12,
                    'base_per_min' => 120,
                    'ingredients' => [
                        IngredientEnum::REANIMATED_SAM->value => 10,
                        IngredientEnum::IRON_ORE->value => 300,
                    ],
                    'alt_recipe' => true,
                ],
            ],
            IngredientEnum::TIME_CRYSTAL->value => [
                'base_yield' => 1,
                'base_per_min' => 6,
                'ingredients' => [
                    IngredientEnum::DIAMONDS->value => 12,
                ],
            ],
            IngredientEnum::URANIUM->value => [
                'description' => 'Uranium Ore (Bauxite)',
                'base_yield' => 12,
                'base_per_min' => 120,
                'ingredients' => [
                    IngredientEnum::REANIMATED_SAM->value => 10,
                    IngredientEnum::BAUXITE->value => 480,
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
