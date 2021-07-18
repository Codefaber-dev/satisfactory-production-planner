<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Building::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->name(),
            'inputs' => $this->faker->numberBetween(1,4),
            'outputs' => $this->faker->numberBetween(1,2),
            'width' => $this->faker->numberBetween(8,24),
            'length' => $this->faker->numberBetween(8,24),
            'height' => $this->faker->numberBetween(8,24),

        ];
    }
}
