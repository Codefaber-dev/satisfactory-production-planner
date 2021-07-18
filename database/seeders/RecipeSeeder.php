<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    protected $recipes = [
        'Smelter' =>
        [
            'Iron Ingot' => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Iron Ore' => 30
                ]
            ],

            'Copper Ingot' => [
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Copper Ore' => 30
                ]
            ],

            'Caterium Ingot' => [
                'base_yield' => 1,
                'base_per_min' => 15,
                'ingredients' => [
                    'Caterium Ore' => 45
                ]
            ],

            'Aluminum Ingot' => [
                'alt_recipe' => true,
                'description' => 'Pure Aluminum Ingot',
                'base_yield' => 1,
                'base_per_min' => 30,
                'ingredients' => [
                    'Aluminum Scrap' => 60
                ]
            ],

        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->recipes)
            ->each(function($recipes,$building_name) {
               $building = Building::ofName($building_name);

               collect($recipes)
                   ->each(function($recipe,$product_name) use ($building) {
                       $product = Ingredient::ofName($product_name);

                       $atts = [
                           'building_id' => $building->id,
                           'product_id' => $product->id,
                           'base_yield' => $recipe['base_yield'],
                           'base_per_min' => $recipe['base_per_min'],
                           'alt_recipe' => isset($recipe['alt_recipe']) && (bool) $recipe['alt_recipe'],
                       ];

                       if ( isset($recipe['description']))
                           $atts['description'] = $recipe['description'];

                       $recipe = Recipe::create($atts);

                       collect($recipe['ingredients'])
                           ->each(function($qty, $name) use ($recipe) {
                              $recipe->addIngredient(Ingredient::ofName($name),$qty);
                           });
                   });
            });
    }
}
