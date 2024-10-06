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

class UpdateOneZero2
{
    public static function update(): void
    {
        echo "<pre>";
        // cleanup deprecated stuff
        static::cleanup();
        echo "Finished cleaning deprecated stuff \n";

        // seed the recipes
        static::recipes();
        echo "Created new recipes. \n Done with update.";

        echo "</pre>";
    }

    protected static function cleanup(): void
    {
        // remove deprecated recipes
        r('Beacon')?->delete();
        r('Color Cartridge')?->delete();
        r('Steel Coated Plate')?->delete();

        // remove deprecated ingredients
        i('Beacon')?->delete();
        i('Color Cartridge')?->delete();
    }

    protected static function recipes(): void
    {
        $recipes = [
            "Constructor" => [
                IngredientEnum::EMPTY_CANISTER->value => [
                    [
                        "description" => RecipeEnum::STEEL_CANISTER->value,
                        "base_yield" => 4,
                        "base_per_min" => 40,
                        "ingredients" => [
                            IngredientEnum::STEEL_INGOT->value => 40,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Assembler" => [
                IngredientEnum::BLACK_POWDER->value => [
                    [
                        "description" => RecipeEnum::FINE_BLACK_POWDER->value,
                        "base_yield" => 6,
                        "base_per_min" => 45,
                        "ingredients" => [
                            IngredientEnum::SULFUR->value => 7.5,
                            IngredientEnum::COMPACTED_COAL->value => 15,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::CONCRETE->value => [
                    [
                        "description" => RecipeEnum::FINE_CONCRETE->value,
                        "base_yield" => 10,
                        "base_per_min" => 50,
                        "ingredients" => [
                            IngredientEnum::SILICA->value => 15,
                            IngredientEnum::LIMESTONE->value => 60,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => RecipeEnum::RUBBER_CONCRETE->value,
                        "base_yield" => 9,
                        "base_per_min" => 90,
                        "ingredients" => [
                            IngredientEnum::LIMESTONE->value => 100,
                            IngredientEnum::RUBBER->value => 20,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::IRON_PLATE->value => [
                    [
                        "description" => RecipeEnum::COATED_IRON_PLATE->value,
                        "base_yield" => 10,
                        "base_per_min" => 75,
                        "ingredients" => [
                            IngredientEnum::IRON_INGOT->value => 37.5,
                            IngredientEnum::PLASTIC->value => 7.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::PORTABLE_MINER->value => [
                    [
                        "description" => RecipeEnum::AUTOMATED_MINER->value,
                        "base_yield" => 1,
                        "base_per_min" => 1,
                        "ingredients" => [
                            IngredientEnum::STEEL_PIPE->value => 4,
                            IngredientEnum::IRON_PLATE->value => 4,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::REINFORCED_IRON_PLATE->value => [
                    [
                        "description" => RecipeEnum::ADHERED_IRON_PLATE->value,
                        "base_yield" => 1,
                        "base_per_min" => 3.75,
                        "ingredients" => [
                            IngredientEnum::IRON_PLATE->value => 11.25,
                            IngredientEnum::RUBBER->value => 3.75,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::ROTOR->value => [
                    [
                        "description" => RecipeEnum::COPPER_ROTOR->value,
                        "base_yield" => 3,
                        "base_per_min" => 11.25,
                        "ingredients" => [
                            IngredientEnum::COPPER_SHEET->value => 22.5,
                            IngredientEnum::SCREW->value => 195,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::SHATTER_REBAR->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 5,
                        "ingredients" => [
                            IngredientEnum::IRON_REBAR->value => 10,
                            IngredientEnum::QUARTZ_CRYSTAL->value => 15,
                        ],
                    ],
                ],
                IngredientEnum::SILICA->value => [
                    [
                        "description" => RecipeEnum::CHEAP_SILICA->value,
                        "base_yield" => 7,
                        "base_per_min" => 52.5,
                        "ingredients" => [
                            IngredientEnum::RAW_QUARTZ->value => 22.5,
                            IngredientEnum::LIMESTONE->value => 37.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Foundry" => [
                IngredientEnum::COPPER_INGOT->value => [
                    [
                        "description" => RecipeEnum::COPPER_ALLOY_INGOT->value,
                        "base_yield" => 10,
                        "base_per_min" => 100,
                        "ingredients" => [
                            IngredientEnum::COPPER_ORE->value => 50,
                            IngredientEnum::IRON_ORE->value => 50,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::IRON_INGOT->value => [
                    [
                        "description" => RecipeEnum::IRON_ALLOY_INGOT->value,
                        "base_yield" => 15,
                        "base_per_min" => 75,
                        "ingredients" => [
                            IngredientEnum::COPPER_ORE->value => 10,
                            IngredientEnum::IRON_ORE->value => 40,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::STEEL_INGOT->value => [
                    [
                        "description" => RecipeEnum::COMPACTED_STEEL_INGOT->value,
                        "base_yield" => 4,
                        "base_per_min" => 10,
                        "ingredients" => [
                            IngredientEnum::IRON_ORE->value => 5,
                            IngredientEnum::COMPACTED_COAL->value => 2.5,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Refinery" => [
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

            ],
            "Manufacturer" => [
                // update
                IngredientEnum::ADAPTIVE_CONTROL_UNIT->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 1,
                        "ingredients" => [
                            IngredientEnum::AUTOMATED_WIRING->value => 5,
                            IngredientEnum::CIRCUIT_BOARD->value => 5,
                            IngredientEnum::HEAVY_MODULAR_FRAME->value => 1,
                            IngredientEnum::COMPUTER->value => 2,
                        ],
                    ],
                ],
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
                IngredientEnum::COOLING_SYSTEM->value => [
                    [
                        "description" => RecipeEnum::COOLING_DEVICE->value,
                        "base_yield" => 2,
                        "base_per_min" => 5,
                        "ingredients" => [
                            IngredientEnum::HEAT_SINK->value => 10,
                            IngredientEnum::MOTOR->value => 2.5,
                            IngredientEnum::NITROGEN_GAS->value => 60,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                IngredientEnum::EXPLOSIVE_REBAR->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 5,
                        "ingredients" => [
                            IngredientEnum::IRON_REBAR->value => 10,
                            IngredientEnum::SMOKELESS_POWDER->value => 10,
                            IngredientEnum::STEEL_PIPE->value => 10,
                        ],
                    ],
                ],
                IngredientEnum::GAS_FILTER->value => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 7.5,
                        "ingredients" => [
                            IngredientEnum::FABRIC->value => 15,
                            IngredientEnum::COAL->value => 30,
                            IngredientEnum::IRON_PLATE->value => 15,
                        ],
                    ],
                ],
                IngredientEnum::RADIO_CONTROL_UNIT->value => [
                    [
                        "base_yield" => 2,
                        "base_per_min" => 2.5,
                        "ingredients" => [
                            IngredientEnum::ALUMINUM_CASING->value => 40,
                            IngredientEnum::CRYSTAL_OSCILLATOR->value => 1.25,
                            IngredientEnum::COMPUTER->value => 2.5,
                        ],
                    ],
                    [
                        "description" => RecipeEnum::RADIO_CONNECTION_UNIT->value,
                        "base_yield" => 1,
                        "base_per_min" => 3.75,
                        "ingredients" => [
                            IngredientEnum::HEAT_SINK->value => 15,
                            IngredientEnum::HIGH_SPEED_CONNECTOR->value => 7.5,
                            IngredientEnum::QUARTZ_CRYSTAL->value => 45,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
            ],
            "Blender" => [

            ],
            "Packager" => [

            ],
            "Particle Accelerator" => [

            ],
            "Quantum Encoder" => [

            ],
            "Converter" => [
                IngredientEnum::DARK_MATTER_RESIDUE->value => [
                    [
                        "base_yield" => 10,
                        "base_per_min" => 100,
                        "ingredients" => [
                            IngredientEnum::REANIMATED_SAM->value => 50,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
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
                    }
                    else {
                        $recipe_model = Recipe::create($atts);
                    }

                    collect($recipe['ingredients'])->each(function ($qty, $name) use ($recipe_model) {

                        $ingredient = Ingredient::ofName($name);

                        if (! $ingredient) {
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
