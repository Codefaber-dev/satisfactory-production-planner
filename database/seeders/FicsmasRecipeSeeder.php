<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class FicsmasRecipeSeeder extends Seeder
{
    protected $recipes = [
        // alts done
        // no byproducts
        "Smelter" => [
            "Blue FICSMAS Ornament" => [
                "base_yield" => 2,
                "base_per_min" => 10,
                "ingredients" => [
                    "FICSMAS Gift" => 5,
                ],
            ],

            "Red FICSMAS Ornament" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "FICSMAS Gift" => 5,
                ],
            ],



        ],

        // alts done
        // no byproducts
        "Constructor" => [
            "Actual Snow" => [
                "base_yield" => 2,
                "base_per_min" => 10,
                "ingredients" => [
                    "FICSMAS Gift" => 25,
                ],
            ],
            "Candy Cane" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "FICSMAS Gift" => 15,
                ],
            ],
            "FICSMAS Bow" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "FICSMAS Gift" => 10,
                ],
            ],
            "FICSMAS Tree Branch" => [
                "base_yield" => 1,
                "base_per_min" => 10,
                "ingredients" => [
                    "FICSMAS Gift" => 10,
                ],
            ],
            "Snowball" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Actual Snow" => 15,
                ],
            ],
        ],

        // alts done
        // no byproducts
        "Assembler" => [
            "Fancy Fireworks" => [
                "base_yield" => 1,
                "base_per_min" => 2.5,
                "ingredients" => [
                    "FICSMAS Tree Branch" => 10,
                    "FICSMAS Bow" => 7.5,
                ],
            ],
            "Sparkly Fireworks" => [
                "base_yield" => 1,
                "base_per_min" => 2.5,
                "ingredients" => [
                    "FICSMAS Tree Branch" => 7.5,
                    "Actual Snow" => 5,
                ],
            ],
            "Sweet Fireworks" => [
                "base_yield" => 1,
                "base_per_min" => 2.5,
                "ingredients" => [
                    "FICSMAS Tree Branch" => 15,
                    "Candy Cane" => 7.5,
                ],
            ],
            "FICSMAS Decoration" => [
                "base_yield" => 2,
                "base_per_min" => 2,
                "ingredients" => [
                    "FICSMAS Tree Branch" => 15,
                    "FICSMAS Ornament Bundle" => 6,
                ],
            ],
            "FICSMAS Ornament Bundle" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Copper FICSMAS Ornament" => 5,
                    "Iron FICSMAS Ornament" => 5,
                ],
            ],
            "FICSMAS Wonder Star" => [
                "base_yield" => 1,
                "base_per_min" => 1,
                "ingredients" => [
                    "FICSMAS Decoration" => 5,
                    "Candy Cane" => 20,
                ],
            ],

        ],

        // alts done
        // no byproducts
        "Foundry" => [
            "Copper FICSMAS Ornament" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Red FICSMAS Ornament" => 10,
                    "Copper Ingot" => 10,
                ],
            ],
            "Iron FICSMAS Ornament" => [
                "base_yield" => 1,
                "base_per_min" => 5,
                "ingredients" => [
                    "Blue FICSMAS Ornament" => 15,
                    "Iron Ingot" => 15,
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
