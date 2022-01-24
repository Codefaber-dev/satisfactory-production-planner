<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'ingredient_id' => Ingredient::factory(),
            'recipe_id' => Recipe::factory(),
            'user_id' => User::factory(),
            'yield' => $this->faker->numberBetween(100,500),
        ];
    }
}
