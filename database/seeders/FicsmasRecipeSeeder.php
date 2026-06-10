<?php

namespace Database\Seeders;

use App\Enums\Building as BuildingEnum;
use App\Enums\Ingredient as IngredientEnum;
use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class FicsmasRecipeSeeder extends Seeder
{
    protected $recipes = [
        // alts done
        // no byproducts
        BuildingEnum::SMELTER->value => [
            IngredientEnum::BLUE_FICSMAS_ORNAMENT->value => [
                'base_yield' => 2,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::FICSMAS_GIFT->value => 5,
                ],
            ],

            IngredientEnum::RED_FICSMAS_ORNAMENT->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::FICSMAS_GIFT->value => 5,
                ],
            ],

        ],

        // alts done
        // no byproducts
        BuildingEnum::CONSTRUCTOR->value => [
            IngredientEnum::ACTUAL_SNOW->value => [
                'base_yield' => 2,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::FICSMAS_GIFT->value => 25,
                ],
            ],
            IngredientEnum::CANDY_CANE->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::FICSMAS_GIFT->value => 15,
                ],
            ],
            IngredientEnum::FICSMAS_BOW->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::FICSMAS_GIFT->value => 10,
                ],
            ],
            IngredientEnum::FICSMAS_TREE_BRANCH->value => [
                'base_yield' => 1,
                'base_per_min' => 10,
                'ingredients' => [
                    IngredientEnum::FICSMAS_GIFT->value => 10,
                ],
            ],
            IngredientEnum::SNOWBALL->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::ACTUAL_SNOW->value => 15,
                ],
            ],
        ],

        // alts done
        // no byproducts
        BuildingEnum::ASSEMBLER->value => [
            IngredientEnum::FANCY_FIREWORKS->value => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    IngredientEnum::FICSMAS_TREE_BRANCH->value => 10,
                    IngredientEnum::FICSMAS_BOW->value => 7.5,
                ],
            ],
            IngredientEnum::SPARKLY_FIREWORKS->value => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    IngredientEnum::FICSMAS_TREE_BRANCH->value => 7.5,
                    IngredientEnum::ACTUAL_SNOW->value => 5,
                ],
            ],
            IngredientEnum::SWEET_FIREWORKS->value => [
                'base_yield' => 1,
                'base_per_min' => 2.5,
                'ingredients' => [
                    IngredientEnum::FICSMAS_TREE_BRANCH->value => 15,
                    IngredientEnum::CANDY_CANE->value => 7.5,
                ],
            ],
            IngredientEnum::FICSMAS_DECORATION->value => [
                'base_yield' => 2,
                'base_per_min' => 2,
                'ingredients' => [
                    IngredientEnum::FICSMAS_TREE_BRANCH->value => 15,
                    IngredientEnum::FICSMAS_ORNAMENT_BUNDLE->value => 6,
                ],
            ],
            IngredientEnum::FICSMAS_ORNAMENT_BUNDLE->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::COPPER_FICSMAS_ORNAMENT->value => 5,
                    IngredientEnum::IRON_FICSMAS_ORNAMENT->value => 5,
                ],
            ],
            IngredientEnum::FICSMAS_WONDER_STAR->value => [
                'base_yield' => 1,
                'base_per_min' => 1,
                'ingredients' => [
                    IngredientEnum::FICSMAS_DECORATION->value => 5,
                    IngredientEnum::CANDY_CANE->value => 20,
                ],
            ],

        ],

        // alts done
        // no byproducts
        BuildingEnum::FOUNDRY->value => [
            IngredientEnum::COPPER_FICSMAS_ORNAMENT->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::RED_FICSMAS_ORNAMENT->value => 10,
                    IngredientEnum::COPPER_INGOT->value => 10,
                ],
            ],
            IngredientEnum::IRON_FICSMAS_ORNAMENT->value => [
                'base_yield' => 1,
                'base_per_min' => 5,
                'ingredients' => [
                    IngredientEnum::BLUE_FICSMAS_ORNAMENT->value => 15,
                    IngredientEnum::IRON_INGOT->value => 15,
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
