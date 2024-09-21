<?php

namespace App\Helpers;

use App\Enums\Ingredient as IngredientEnum;
use App\Enums\Recipe as RecipeEnum;
use App\Enums\Building as BuildingEnum;
use App\Models\BuildingVariant;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Building;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class UpdateOneZero
{
    public static function update(): void
    {
        echo "<pre>";
        // cleanup deprecated stuff
        static::cleanup();
        echo "Finished cleaning deprecated stuff \n";

        // seed the ingredients
        static::ingredients();
        echo "Created new ingredients \n";

        // seed the buildings
        static::buildings();
        echo "Finished the buildings \n";

        // seed the recipes
        static::recipes();
        echo "Created new recipes. \n Done with update.";

        echo "</pre>";
    }

    protected static function cleanup(): void
    {
        // rename ingredients
        i('SAM Ore')?->update(['name' => IngredientEnum::SAM->value]);
        i('Green Power Slug')?->update(['name' => IngredientEnum::BLUE_POWER_SLUG->value]);
    }

    public static function buildings(): void
    {
        // soft-delete mod variants for now
        BuildingVariant::where('name','<>','MK1')->delete();

        $buildings = [
            // Assembler
            [
                'name' => BuildingEnum::ASSEMBLER,
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
                            [ 'ingredient' => IngredientEnum::REINFORCED_IRON_PLATE, 'qty' => 8 ],
                            [ 'ingredient' => IngredientEnum::CABLE, 'qty' => 10 ],
                            [ 'ingredient' => IngredientEnum::ROTOR, 'qty' => 4 ],
                        ],
                    ],
                ]
            ],

            // Manufacturer
            [
                'name' => BuildingEnum::MANUFACTURER,
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
                            [ 'ingredient' => IngredientEnum::MOTOR, 'qty' => 10 ],
                            [ 'ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME, 'qty' => 20 ],
                            [ 'ingredient' => IngredientEnum::CABLE, 'qty' => 50 ],
                            [ 'ingredient' => IngredientEnum::PLASTIC, 'qty' => 50 ],
                        ],
                    ],
                ]
            ],

            // Blender
            [
                'name' => BuildingEnum::BLENDER,
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
                            [ 'ingredient' => IngredientEnum::COMPUTER, 'qty' => 10 ],
                            [ 'ingredient' => IngredientEnum::HEAVY_MODULAR_FRAME, 'qty' => 10 ],
                            [ 'ingredient' => IngredientEnum::MOTOR, 'qty' => 20 ],
                            [ 'ingredient' => IngredientEnum::ALUMINUM_CASING, 'qty' => 50 ],
                        ],
                    ],
                ]
            ],

            // Particle Accelerator
            [
                'name' => BuildingEnum::PARTICLE_ACCELERATOR,
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
                            [ 'ingredient' => IngredientEnum::TURBO_MOTOR, 'qty' => 10 ],
                            [ 'ingredient' => IngredientEnum::SUPERCOMPUTER, 'qty' => 10 ],
                            [ 'ingredient' => IngredientEnum::FUSED_MODULAR_FRAME, 'qty' => 20 ],
                            [ 'ingredient' => IngredientEnum::COOLING_SYSTEM, 'qty' => 50 ],
                            [ 'ingredient' => IngredientEnum::QUICKWIRE, 'qty' => 500 ],
                        ],
                    ],
                ]
            ],

            // Quantum Encoder
            [
                'name' => BuildingEnum::QUANTUM_ENCODER,
                'inputs' => 4,
                'outputs' => 2,
                'width' => 24, // TODO: get actual size
                'length' => 38,
                'height' => 32,
                'variants' => [
                    'mk1' => [
                        'multiplier' => 1,
                        'base_power' => 500,
                        'recipe' => [
                            [ 'ingredient' => IngredientEnum::TURBO_MOTOR, 'qty' => 20 ],
                            [ 'ingredient' => IngredientEnum::SUPERCOMPUTER, 'qty' => 20 ],
                            [ 'ingredient' => IngredientEnum::TIME_CRYSTAL, 'qty' => 50 ],
                            [ 'ingredient' => IngredientEnum::COOLING_SYSTEM, 'qty' => 50 ],
                            [ 'ingredient' => IngredientEnum::FICSITE_TRIGON, 'qty' => 100 ],
                        ],
                    ],
                ]
            ],

            // Converter
            [
                'name' => BuildingEnum::CONVERTER,
                'inputs' => 2,
                'outputs' => 2,
                'width' => 24, // TODO: get actual size
                'length' => 24,
                'height' => 32,
                'variants' => [
                    'mk1' => [
                        'multiplier' => 1,
                        'base_power' => 500,
                        'recipe' => [
                            [ 'ingredient' => IngredientEnum::FUSED_MODULAR_FRAME->value, 'qty' => 10 ],
                            [ 'ingredient' => IngredientEnum::COOLING_SYSTEM->value, 'qty' => 25 ],
                            [ 'ingredient' => IngredientEnum::RADIO_CONTROL_UNIT->value, 'qty' => 50 ],
                            [ 'ingredient' => IngredientEnum::SAM_FLUCTUATOR->value, 'qty' => 100 ],
                        ],
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
                ]
            ],
        ];

        collect($buildings)
            ->each(function(array $atts) {
                $atts = (object) $atts;

                $building = Building::updateOrCreate([
                    'name' => $atts->name->value,
                    ],
                    [
                        'inputs' => $atts->inputs,
                        'outputs' => $atts->outputs,
                        'width' => $atts->width,
                        'length' => $atts->length,
                        'height' => $atts->height,
                    ]
                );


                // clear out the variants
                $building->variants()->withTrashed()->get()->each(function(BuildingVariant $buildingVariant) {
                    $buildingVariant->forceDelete();
                });
                $building->refresh();

                foreach($atts->variants as $variant_name => $variant_atts ) {
                    $variant = $building->variants()->create([
                        'name' => $variant_name,
                        'multiplier' => $variant_atts['multiplier'],
                        'base_power' => $variant_atts['base_power'],
                    ]);

                    echo "Variant {$variant_name} created for {$building->name}\n";

                    $variant->setRecipe($variant_atts['recipe']);
                }
            });
    }

    protected static function ingredients()
    {
        $tier1 = [
            IngredientEnum::SAM, // i
        ];

        $tier2 = [
            IngredientEnum::ALIEN_DNA_CAPSULE,
            IngredientEnum::DIAMONDS,
            IngredientEnum::EXCITED_PHOTONIC_MATTER,
            IngredientEnum::REANIMATED_SAM,
        ];

        $tier3 = [
            IngredientEnum::TIME_CRYSTAL
        ];

        $tier4 = [
            IngredientEnum::SAM_FLUCTUATOR,
        ];

        $tier5 = [
            IngredientEnum::DISSOLVED_SILICA,
            IngredientEnum::FICSITE_INGOT,
            IngredientEnum::ROCKET_FUEL,
            IngredientEnum::PACKAGED_ROCKET_FUEL,
        ];

        $tier6 = [
            IngredientEnum::FICSITE_TRIGON,
            IngredientEnum::IONIZED_FUEL,
            IngredientEnum::PACKAGED_IONIZED_FUEL,
        ];

        $tier7 = [
            IngredientEnum::NEURAL_QUANTUM_PROCESSOR,
        ];

        $tier8 = [];

        $tier9 = [];

        $tier10 = [
            IngredientEnum::BIOCHEMICAL_SCULPTOR,
        ];

        $tier11 = [];
        $tier12 = [];

        $tier13 = [
            IngredientEnum::FICSONIUM,
        ];

        $tier14 = [
            IngredientEnum::FICSONIUM_FUEL_ROD,
        ];

        $tier15 = [
            IngredientEnum::DARK_MATTER_RESIDUE,
        ];

        $tier16 = [
            IngredientEnum::DARK_MATTER_RESIDUE,
            IngredientEnum::DARK_MATTER_CRYSTAL,
        ];

        $tier17 = [
            IngredientEnum::SUPERPOSITION_OSCILLATOR,
            IngredientEnum::SINGULARITY_CELL,
        ];

        $tier18 = [
            IngredientEnum::ALIEN_POWER_MATRIX,
            IngredientEnum::AI_EXPANSION_SERVER,
            IngredientEnum::BALLISTIC_WARP_DRIVE,
        ];

        // seed the ingredients
        collect($tier1)->each(function ($name) {
            $name = $name->value;
            if (! Ingredient::whereName($name)?->exists()) {
                echo "Creating Ingredient $name \n";
                Ingredient::forceCreate(['name' => $name, 'raw' => true, 'tier' => 1]);
            }
        });

        $tiers = compact('tier1', 'tier2', 'tier3', 'tier4',
            'tier5', 'tier6', 'tier7', 'tier8',
            'tier9', 'tier10', 'tier11', 'tier12',
            'tier13', 'tier14', 'tier15', 'tier16',
            'tier17', 'tier18'
        );

        collect($tiers)->each(function ($row, $tier) {

            collect($row)->each(function ($name) use ($tier) {
                $name = $name->value;
                if (! Ingredient::whereName($name)?->exists()) {
                    echo "Creating Ingredient $name \n";
                    Ingredient::forceCreate(['name' => $name, 'raw' => false, 'tier' => intval(str_replace("tier","",$tier))]);
                }
            });
        });
    }

    protected static function recipes(): void
    {
        $recipes = [
            "Constructor" => [
                IngredientEnum::ALIEN_DNA_CAPSULE->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            IngredientEnum::ALIEN_PROTEIN->value => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::STEEL_BEAM->value => [
                    [
                        "description" => RecipeEnum::ALUMINUM_BEAM->value,
                        "base_yield" => 3,
                        "base_per_min" => 22.5,
                        "ingredients" => [
                            IngredientEnum::ALUMINUM_INGOT->value => 22.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::IRON_ROD->value => [
                    [
                        "description" => RecipeEnum::ALUMINUM_ROD->value,
                        "base_yield" => 7,
                        "base_per_min" => 52.5,
                        "ingredients" => [
                            IngredientEnum::ALUMINUM_INGOT->value => 7.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::FICSITE_TRIGON->value => [
                    [
                        "base_yield" => 3,
                        "base_per_min" => 30,
                        "ingredients" => [
                            IngredientEnum::FICSITE_INGOT->value => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::STEEL_PIPE->value => [
                    [
                        "description" => RecipeEnum::IRON_PIPE->value,
                        "base_yield" => 5,
                        "base_per_min" => 25,
                        "ingredients" => [
                            IngredientEnum::IRON_INGOT->value => 100,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::POWER_SHARD->value => [
                    [
                        "description" => RecipeEnum::POWER_SHARD_1->value,
                        "base_yield" => 1,
                        "base_per_min" => 7.5,
                        "ingredients" => [
                            IngredientEnum::BLUE_POWER_SLUG->value => 7.5,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::POWER_SHARD_2->value,
                        "base_yield" => 2,
                        "base_per_min" => 10,
                        "ingredients" => [
                            IngredientEnum::YELLOW_POWER_SLUG->value => 5,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::POWER_SHARD_5->value,
                        "base_yield" => 5,
                        "base_per_min" => 12.5,
                        "ingredients" => [
                            IngredientEnum::PURPLE_POWER_SLUG->value => 2.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::REANIMATED_SAM->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 30,
                        "ingredients" => [
                            IngredientEnum::SAM->value => 120,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
            ],
            "Assembler" => [
                // update
                IngredientEnum::CIRCUIT_BOARD->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 7.5,
                        "ingredients" => [
                            IngredientEnum::COPPER_SHEET->value => 15,
                            IngredientEnum::PLASTIC->value => 30,
                        ],
                    ],
                    [
                        "description" => RecipeEnum::CATERIUM_CIRCUIT_BOARD->value,
                        "base_yield" => 7,
                        "base_per_min" => 8.75,
                        "ingredients" => [
                            IngredientEnum::QUICKWIRE->value => 37.5,
                            IngredientEnum::PLASTIC->value => 12.5,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::ELECTRODE_CIRCUIT_BOARD->value,
                        "base_yield" => 1,
                        "base_per_min" => 5,
                        "ingredients" => [
                            IngredientEnum::RUBBER->value => 20,
                            IngredientEnum::PETROLEUM_COKE->value => 40,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::SILICON_CIRCUIT_BOARD->value,
                        "base_yield" => 5,
                        "base_per_min" => 12.5,
                        "ingredients" => [
                            IngredientEnum::COPPER_SHEET->value => 27.5,
                            IngredientEnum::SILICA->value => 27.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],

                IngredientEnum::AI_LIMITER->value => [
                    [
                        "description" => RecipeEnum::PLASTIC_AI_LIMITER->value,
                        "base_yield" => 2,
                        "base_per_min" => 8,
                        "ingredients" => [
                            IngredientEnum::QUICKWIRE->value => 120,
                            IngredientEnum::PLASTIC->value => 28,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                // update
                IngredientEnum::COMPUTER->value => [
                    [
                        "description" => RecipeEnum::CRYSTAL_COMPUTER->value,
                        "base_yield" => 2,
                        "base_per_min" => 3.3333,
                        "ingredients" => [
                            IngredientEnum::CIRCUIT_BOARD->value => 5,
                            IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.6667,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 6,
                        "ingredients" => [
                            IngredientEnum::STEEL_BEAM->value => 18,
                            IngredientEnum::CONCRETE->value => 36,
                        ],
                    ],
                    [
                        "description" => RecipeEnum::ENCASED_INDUSTRIAL_PIPE->value,
                        "base_yield" => 1,
                        "base_per_min" => 4,
                        "ingredients" => [
                            IngredientEnum::STEEL_PIPE->value => 24,
                            IngredientEnum::CONCRETE->value => 20,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::MAGNETIC_FIELD_GENERATOR->value => [[
                    "base_yield" => 2,
                    "base_per_min" => 1,
                    "ingredients" => [
                        IngredientEnum::VERSATILE_FRAMEWORK->value => 2.5,
                        IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 1,
                    ],
                ]],
            ],
            "Foundry" => [
                IngredientEnum::IRON_INGOT->value => [
                    [
                        "description" => RecipeEnum::BASIC_IRON_INGOT->value,
                        "base_yield" => 10,
                        "base_per_min" => 50,
                        "ingredients" => [
                            IngredientEnum::IRON_ORE->value => 25,
                            IngredientEnum::LIMESTONE->value => 40,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::QUARTZ_CRYSTAL->value => [
                    [
                        "description" => RecipeEnum::FUSED_QUARTZ_CRYSTAL->value,
                        "base_yield" => 18,
                        "base_per_min" => 54,
                        "ingredients" => [
                            IngredientEnum::RAW_QUARTZ->value => 75,
                            IngredientEnum::COAL->value => 36,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::STEEL_BEAM->value => [
                    [
                        "description" => RecipeEnum::MOLDED_BEAM->value,
                        "base_yield" => 9,
                        "base_per_min" => 45,
                        "ingredients" => [
                            IngredientEnum::STEEL_INGOT->value => 120,
                            IngredientEnum::CONCRETE->value => 80,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::STEEL_PIPE->value => [
                    [
                        "description" => RecipeEnum::MOLDED_STEEL_PIPE->value,
                        "base_yield" => 5,
                        "base_per_min" => 50,
                        "ingredients" => [
                            IngredientEnum::STEEL_INGOT->value => 50,
                            IngredientEnum::CONCRETE->value => 30,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::IRON_PLATE->value => [
                    [
                        "description" => RecipeEnum::STEEL_CAST_PLATE->value,
                        "base_yield" => 3,
                        "base_per_min" => 45,
                        "ingredients" => [
                            IngredientEnum::IRON_INGOT->value => 15,
                            IngredientEnum::STEEL_INGOT->value => 15,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::CATERIUM_INGOT->value => [
                    [
                        "description" => RecipeEnum::TEMPERED_CATERIUM_INGOT->value,
                        "base_yield" => 3,
                        "base_per_min" => 22.5,
                        "ingredients" => [
                            IngredientEnum::CATERIUM_ORE->value => 45,
                            IngredientEnum::PETROLEUM_COKE->value => 15,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::COPPER_INGOT->value => [
                    [
                        "description" => RecipeEnum::TEMPERED_COPPER_INGOT->value,
                        "base_yield" => 12,
                        "base_per_min" => 60,
                        "ingredients" => [
                            IngredientEnum::COPPER_ORE->value => 25,
                            IngredientEnum::PETROLEUM_COKE->value => 40,
                            IngredientEnum::PETROLEUM_COKE->value => 40,
                            IngredientEnum::PETROLEUM_COKE->value => 40,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Refinery" => [
                IngredientEnum::ALUMINUM_SCRAP->value => [
                    [
                        "description" => RecipeEnum::ELECTRODE_ALUMINUM_SCRAP->value,
                        "base_yield" => 20,
                        "base_per_min" => 300,
                        "ingredients" => [
                            IngredientEnum::ALUMINA_SOLUTION->value => 180,
                            IngredientEnum::PETROLEUM_COKE->value => 60,
                        ],
                        "byproducts" => [
                            IngredientEnum::WATER->value => 105,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::IONIZED_FUEL->value => [
                    [
                        "base_yield" => 16,
                        "base_per_min" => 40,
                        "ingredients" => [
                            IngredientEnum::ROCKET_FUEL->value => 40,
                            IngredientEnum::POWER_SHARD->value => 2.5,
                        ],
                        "byproducts" => [
                            IngredientEnum::COMPACTED_COAL->value => 5,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::CATERIUM_INGOT->value => [
                    [
                        "description" => RecipeEnum::LEACHED_CATERIUM_INGOT->value,
                        "base_yield" => 6,
                        "base_per_min" => 36,
                        "ingredients" => [
                            IngredientEnum::CATERIUM_ORE->value => 54,
                            IngredientEnum::SULFURIC_ACID->value => 30,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::COPPER_INGOT->value => [
                    [
                        "description" => RecipeEnum::LEACHED_COPPER_INGOT->value,
                        "base_yield" => 22,
                        "base_per_min" => 110,
                        "ingredients" => [
                            IngredientEnum::COPPER_ORE->value => 45,
                            IngredientEnum::SULFURIC_ACID->value => 25,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::IRON_INGOT->value => [
                    [
                        "description" => RecipeEnum::LEACHED_IRON_INGOT->value,
                        "base_yield" => 10,
                        "base_per_min" => 100,
                        "ingredients" => [
                            IngredientEnum::IRON_ORE->value => 50,
                            IngredientEnum::SULFURIC_ACID->value => 10,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::QUARTZ_CRYSTAL->value => [
                    [
                        "description" => RecipeEnum::QUARTZ_PURIFICATION->value,
                        "base_yield" => 15,
                        "base_per_min" => 75,
                        "ingredients" => [
                            IngredientEnum::RAW_QUARTZ->value => 120,
                            IngredientEnum::NITRIC_ACID->value => 10,
                        ],
                        "byproducts" => [
                            IngredientEnum::DISSOLVED_SILICA->value => 60,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Manufacturer" => [
                IngredientEnum::AUTOMATED_WIRING->value => [
                    [
                        "description" => RecipeEnum::AUTOMATED_SPEED_WIRING->value,
                        "base_yield" => 4,
                        "base_per_min" => 7.5,
                        "ingredients" => [
                            IngredientEnum::STATOR->value => 3.75,
                            IngredientEnum::WIRE->value => 75,
                            IngredientEnum::HIGH_SPEED_CONNECTOR->value => 1.875,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::BALLISTIC_WARP_DRIVE->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 1,
                        "ingredients" => [
                            IngredientEnum::THERMAL_PROPULSION_ROCKET->value => 1,
                            IngredientEnum::SINGULARITY_CELL->value => 5,
                            IngredientEnum::SUPERPOSITION_OSCILLATOR->value => 2,
                            IngredientEnum::DARK_MATTER_CRYSTAL->value => 40,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::IODINE_INFUSED_FILTER->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 3.75,
                        "ingredients" => [
                            IngredientEnum::GAS_FILTER->value => 3.75,
                            IngredientEnum::QUICKWIRE->value => 30,
                            IngredientEnum::ALUMINUM_CASING->value => 3.75,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::MOTOR->value => [
                    [
                        "description" => RecipeEnum::RIGOR_MOTOR->value,
                        "base_yield" => 6,
                        "base_per_min" => 7.5,
                        "ingredients" => [
                            IngredientEnum::ROTOR->value => 3.75,
                            IngredientEnum::STATOR->value => 3.75,
                            IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.25,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::SAM_FLUCTUATOR->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 60,
                            IngredientEnum::WIRE->value => 50,
                            IngredientEnum::STEEL_PIPE->value => 30,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::SINGULARITY_CELL->value => [
                    [
                        "base_yield" => 10,
                        "base_per_min" => 10,
                        "ingredients" => [
                            IngredientEnum::NUCLEAR_PASTA->value => 1,
                            IngredientEnum::DARK_MATTER_CRYSTAL->value => 20,
                            IngredientEnum::IRON_PLATE->value => 100,
                            IngredientEnum::CONCRETE->value => 200,
                        ],
                        "alt_recipe" => false,
                    ],
                ],

                // updates
                IngredientEnum::COMPUTER->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 2.5,
                        "ingredients" => [
                            IngredientEnum::CIRCUIT_BOARD->value => 10,
                            IngredientEnum::CABLE->value => 20,
                            IngredientEnum::PLASTIC->value => 40,
                        ],
                        "alt_recipe" => false,
                    ],
                    [
                        "description" => RecipeEnum::CATERIUM_COMPUTER->value,
                        "base_yield" => 1,
                        "base_per_min" => 3.75,
                        "ingredients" => [
                            IngredientEnum::CIRCUIT_BOARD->value => 15,
                            IngredientEnum::QUICKWIRE->value => 52.5,
                            IngredientEnum::RUBBER->value => 22.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::HEAVY_MODULAR_FRAME->value => [
                [
                    "base_yield" => 1,
                    "base_per_min" => 2,
                    "ingredients" => [
                        IngredientEnum::MODULAR_FRAME->value => 10,
                        IngredientEnum::STEEL_PIPE->value => 40,
                        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => 10,
                        IngredientEnum::SCREW->value => 240,
                    ],
                ],
                [
                    "description" => RecipeEnum::HEAVY_FLEXIBLE_FRAME->value,
                    "base_yield" => 1,
                    "base_per_min" => 3.75,
                    "ingredients" => [
                        IngredientEnum::MODULAR_FRAME->value => 18.75,
                        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => 11.25,
                        IngredientEnum::RUBBER->value => 75,
                        IngredientEnum::SCREW->value => 390,
                    ],
                    "alt_recipe" => true,
                ],
                [
                    "description" => RecipeEnum::HEAVY_ENCASED_FRAME->value,
                    "base_yield" => 3,
                    "base_per_min" => 2.8125,
                    "ingredients" => [
                        IngredientEnum::MODULAR_FRAME->value => 7.5,
                        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value => 9.375,
                        IngredientEnum::STEEL_PIPE->value => 33.75,
                        IngredientEnum::CONCRETE->value => 20.625,
                    ],
                    "alt_recipe" => true,
                ],
            ],
            ],
            "Blender" => [
                IngredientEnum::BIOCHEMICAL_SCULPTOR->value => [
                    [
                        "base_yield" => 4,
                        "base_per_min" => 2,
                        "ingredients" => [
                            IngredientEnum::ASSEMBLY_DIRECTOR_SYSTEM->value => 0.5,
                            IngredientEnum::FICSITE_TRIGON->value => 40,
                            IngredientEnum::WATER->value => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::SILICA->value => [
                    [
                        "description" => RecipeEnum::DISTILLED_SILICA->value,
                        "base_yield" => 27,
                        "base_per_min" => 270,
                        "ingredients" => [
                            IngredientEnum::DISSOLVED_SILICA->value => 120,
                            IngredientEnum::LIMESTONE->value => 50,
                            IngredientEnum::WATER->value => 100,
                        ],
                        "byproducts" => [
                            IngredientEnum::WATER->value => 80,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::ROCKET_FUEL->value => [
                    [
                        "description" => RecipeEnum::NITRO_ROCKET_FUEL->value,
                        "base_yield" => 6,
                        "base_per_min" => 150,
                        "ingredients" => [
                            IngredientEnum::FUEL->value => 100,
                            IngredientEnum::NITROGEN_GAS->value => 75,
                            IngredientEnum::SULFUR->value => 100,
                            IngredientEnum::COAL->value => 50,
                        ],
                        "byproducts" => [
                            IngredientEnum::COMPACTED_COAL->value => 25,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::ROCKET_FUEL->value => [
                    [
                        "base_yield" => 10,
                        "base_per_min" => 100,
                        "ingredients" => [
                            IngredientEnum::TURBOFUEL->value => 60,
                            IngredientEnum::NITRIC_ACID->value => 10,
                        ],
                        "byproducts" => [
                            IngredientEnum::COMPACTED_COAL->value => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
            ],
            "Packager" => [
                IngredientEnum::PACKAGED_IONIZED_FUEL->value => [
                    [
                        "base_yield" => 2,
                        "base_per_min" => 40,
                        "ingredients" => [
                            IngredientEnum::IONIZED_FUEL->value => 80,
                            IngredientEnum::EMPTY_FLUID_TANK->value => 40,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::PACKAGED_ROCKET_FUEL->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 60,
                        "ingredients" => [
                            IngredientEnum::ROCKET_FUEL->value => 120,
                            IngredientEnum::EMPTY_FLUID_TANK->value => 60,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::IONIZED_FUEL->value => [
                    [
                        "description" => RecipeEnum::UNPACKAGE_IONIZED_FUEL->value,
                        "base_yield" => 4,
                        "base_per_min" => 80,
                        "ingredients" => [
                            IngredientEnum::PACKAGED_IONIZED_FUEL->value => 40,
                        ],
                        "byproducts" => [
                            IngredientEnum::EMPTY_FLUID_TANK->value => 40,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::ROCKET_FUEL->value => [
                    [
                        "description" => RecipeEnum::UNPACKAGE_ROCKET_FUEL->value,
                        "base_yield" => 2,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::PACKAGED_ROCKET_FUEL->value => 60,
                        ],
                        "byproducts" => [
                            IngredientEnum::EMPTY_FLUID_TANK->value => 60,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Particle Accelerator" => [
                IngredientEnum::DIAMONDS->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 30,
                        "ingredients" => [
                            IngredientEnum::COAL->value => 600,
                        ],
                        "alt_recipe" => false,
                    ],
                    [
                        "description" => RecipeEnum::CLOUDY_DIAMONDS->value,
                        "base_yield" => 1,
                        "base_per_min" => 20,
                        "ingredients" => [
                            IngredientEnum::COAL->value => 240,
                            IngredientEnum::LIMESTONE->value => 480,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::OIL_BASED_DIAMONDS->value,
                        "base_yield" => 2,
                        "base_per_min" => 40,
                        "ingredients" => [
                            IngredientEnum::CRUDE_OIL->value => 200,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::PETROLEUM_DIAMONDS->value,
                        "base_yield" => 1,
                        "base_per_min" => 30,
                        "ingredients" => [
                            IngredientEnum::PETROLEUM_COKE->value => 720,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::TURBO_DIAMONDS->value,
                        "base_yield" => 3,
                        "base_per_min" => 60,
                        "ingredients" => [
                            IngredientEnum::COAL->value => 600,
                            IngredientEnum::PACKAGED_TURBOFUEL->value => 40,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::DARK_MATTER_CRYSTAL->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 30,
                        "ingredients" => [
                            IngredientEnum::DIAMONDS->value => 30,
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 150,
                        ],
                        "alt_recipe" => false,
                    ],
                    [
                        "description" => RecipeEnum::DARK_MATTER_CRYSTALLIZATION->value,
                        "base_yield" => 1,
                        "base_per_min" => 20,
                        "ingredients" => [
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 200,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::DARK_MATTER_TRAP->value,
                        "base_yield" => 2,
                        "base_per_min" => 60,
                        "ingredients" => [
                            IngredientEnum::TIME_CRYSTAL->value => 30,
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 150,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::FICSONIUM->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            IngredientEnum::PLUTONIUM_WASTE->value => 10,
                            IngredientEnum::SINGULARITY_CELL->value => 10,
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 200,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
            ],
            "Quantum Encoder" => [
                IngredientEnum::AI_EXPANSION_SERVER->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 4,
                        "ingredients" => [
                            IngredientEnum::MAGNETIC_FIELD_GENERATOR->value => 4,
                            IngredientEnum::NEURAL_QUANTUM_PROCESSOR->value => 4,
                            IngredientEnum::SUPERPOSITION_OSCILLATOR->value => 4,
                            IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 100,
                        ],
                        "byproducts" => [
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 100,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::ALIEN_POWER_MATRIX->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 2.5,
                        "ingredients" => [
                            IngredientEnum::SAM_FLUCTUATOR->value => 12.5,
                            IngredientEnum::POWER_SHARD->value => 7.5,
                            IngredientEnum::SUPERPOSITION_OSCILLATOR->value => 7.5,
                            IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 60,
                        ],
                        "byproducts" => [
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 60,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::FICSONIUM_FUEL_ROD->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 2.5,
                        "ingredients" => [
                            IngredientEnum::FICSONIUM->value => 5,
                            IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value => 5,
                            IngredientEnum::FICSITE_TRIGON->value => 100,
                            IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 50,
                        ],
                        "byproducts" => [
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 50,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::NEURAL_QUANTUM_PROCESSOR->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 3,
                        "ingredients" => [
                            IngredientEnum::TIME_CRYSTAL->value => 15,
                            IngredientEnum::SUPERCOMPUTER->value => 3,
                            IngredientEnum::FICSITE_TRIGON->value => 45,
                            IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 75,
                        ],
                        "byproducts" => [
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 75,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::SUPERPOSITION_OSCILLATOR->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 5,
                        "ingredients" => [
                            IngredientEnum::DARK_MATTER_CRYSTAL->value => 30,
                            IngredientEnum::CRYSTAL_OSCILLATOR->value => 5,
                            IngredientEnum::ALCLAD_ALUMINUM_SHEET->value => 45,
                            IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 125,
                        ],
                        "byproducts" => [
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 125,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::POWER_SHARD->value => [
                    [
                        "description" => RecipeEnum::SYNTHETIC_POWER_SHARD->value,
                        "base_yield" => 1,
                        "base_per_min" => 5,
                        "ingredients" => [
                            IngredientEnum::TIME_CRYSTAL->value => 10,
                            IngredientEnum::DARK_MATTER_CRYSTAL->value => 10,
                            IngredientEnum::QUARTZ_CRYSTAL->value => 60,
                            IngredientEnum::EXCITED_PHOTONIC_MATTER->value => 60,
                        ],
                        "byproducts" => [
                            IngredientEnum::DARK_MATTER_RESIDUE->value => 60,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Converter" => [
                IngredientEnum::BAUXITE->value => [
                    [
                        "description" => RecipeEnum::BAUXITE_CATERIUM->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::CATERIUM_ORE->value => 150,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::BAUXITE_COPPER->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::COPPER_ORE->value => 180,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::CATERIUM_ORE->value => [
                    [
                        "description" => RecipeEnum::CATERIUM_ORE_COPPER->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::COPPER_ORE->value => 150,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::CATERIUM_ORE_QUARTZ->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::RAW_QUARTZ->value => 120,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::COAL->value => [
                    [
                        "description" => RecipeEnum::COAL_IRON->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::IRON_ORE->value => 180,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::COAL_LIMESTONE->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::LIMESTONE->value => 360,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::COPPER_ORE->value => [
                    [
                        "description" => RecipeEnum::COPPER_ORE_QUARTZ->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::RAW_QUARTZ->value => 100,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::COPPER_ORE_SULFUR->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::SULFUR->value => 120,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::DARK_MATTER_RESIDUE->value => [
                    [
                        "base_yield" => 10,
                        "base_per_min" => 100,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 5,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::IONIZED_FUEL->value => [
                    [
                        "description" => RecipeEnum::DARK_ION_FUEL->value,
                        "base_yield" => 10,
                        "base_per_min" => 200,
                        "ingredients" => [
                            IngredientEnum::PACKAGED_ROCKET_FUEL->value => 240,
                            IngredientEnum::DARK_MATTER_CRYSTAL->value => 80,
                        ],
                        "byproducts" => [
                            IngredientEnum::COMPACTED_COAL->value => 40
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::EXCITED_PHOTONIC_MATTER->value => [
                    [
                        "base_yield" => 10,
                        "base_per_min" => 200,
                        "ingredients" => [

                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::FICSITE_INGOT->value => [
                    [
                        "description" => RecipeEnum::FICSITE_INGOT_ALUMINUM->value,
                        "base_yield" => 1,
                        "base_per_min" => 30,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 60,
                            IngredientEnum::ALUMINUM_INGOT->value => 120,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::FICSITE_INGOT_CATERIUM->value,
                        "base_yield" => 1,
                        "base_per_min" => 15,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 45,
                            IngredientEnum::CATERIUM_INGOT->value => 60,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::FICSITE_INGOT_IRON->value,
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 40,
                            IngredientEnum::IRON_INGOT->value => 240,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::IRON_ORE->value => [
                    [
                        "description" => RecipeEnum::IRON_ORE_LIMESTONE->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::LIMESTONE->value => 240,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::LIMESTONE->value => [
                    [
                        "description" => RecipeEnum::LIMESTONE_SULFUR->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::SULFUR->value => 20,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::NITROGEN_GAS->value => [
                    [
                        "description" => RecipeEnum::NITROGEN_GAS_BAUXITE->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::BAUXITE->value => 100,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::NITROGEN_GAS_CATERIUM->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::CATERIUM_ORE->value => 120,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::DIAMONDS->value => [
                    [
                        "description" => RecipeEnum::PINK_DIAMONDS->value,
                        "base_yield" => 1,
                        "base_per_min" => 15,
                        "ingredients" => [
                            IngredientEnum::COAL->value => 120,
                            IngredientEnum::QUARTZ_CRYSTAL->value => 45,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::RAW_QUARTZ->value => [
                    [
                        "description" => RecipeEnum::RAW_QUARTZ_BAUXITE->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::BAUXITE->value => 100,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::RAW_QUARTZ_COAL->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::COAL->value => 240,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::SULFUR->value => [
                    [
                        "description" => RecipeEnum::SULFUR_COAL->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::COAL->value => 200,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::SULFUR_IRON->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::IRON_ORE->value => 300,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::TIME_CRYSTAL->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 6,
                        "ingredients" => [
                            IngredientEnum::DIAMONDS->value => 12,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                IngredientEnum::URANIUM->value => [
                    [
                        "description" => RecipeEnum::URANIUM_ORE_BAUXITE->value,
                        "base_yield" => 12,
                        "base_per_min" => 120,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 10,
                            IngredientEnum::BAUXITE->value => 480,
                        ],
                        "alt_recipe" => true,
                    ],
                ]
            ],
        ];

        collect($recipes)->each(function ($products, $building_name) {
            $building = Building::ofName($building_name);
            echo "Processing Recipes for {$building_name} \n";

            collect($products)->each(function ($recipes, $product_name) use ($building) {
                echo "Processing Ingredient: {$product_name} \n";

                $product = Ingredient::ofName($product_name);

                // wrap the recipes if needed
                $recipes = Arr::wrap($recipes);

                foreach ($recipes as $recipe) {
                    $description = $recipe["description"] ?? $product_name;
                    echo "Processing Recipe: $description \n";

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

                    if ($recipe_model = Recipe::ofName($description)) {
                        $recipe_model->update($atts);
                        $recipe_model->ingredients()->detach();
                        $recipe_model->byproducts()->detach();
                    } else {
                        $recipe_model = Recipe::create($atts);
                    }

                    collect($recipe['ingredients'])->each(function ($qty, $name) use ($recipe_model) {

                        $ingredient = Ingredient::ofName($name);

                        if (!$ingredient) {
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
