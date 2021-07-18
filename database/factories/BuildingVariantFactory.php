<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\BuildingVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BuildingVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->name(),
            'building_id' => Building::factory(),
            'base_power' => $this->faker->numberBetween(6,100),
        ];
    }
}
