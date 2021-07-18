<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => Ingredient::factory(),
            'building_id' => Building::factory(),
            'base_yield' => 1,
            'base_per_min' => 45,
            'description' => $this->faker->word(),
            'alt_recipe' => true
        ];
    }
}
