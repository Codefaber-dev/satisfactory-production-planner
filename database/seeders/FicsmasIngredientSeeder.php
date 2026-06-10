<?php

namespace Database\Seeders;

use App\Enums\Ingredient as IngredientEnum;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class FicsmasIngredientSeeder extends Seeder
{
    protected $tier1 = [
        IngredientEnum::FICSMAS_GIFT->value, // image
    ];

    protected $tier2 = [
        IngredientEnum::ACTUAL_SNOW->value, // image
        IngredientEnum::BLUE_FICSMAS_ORNAMENT->value, // image
        IngredientEnum::RED_FICSMAS_ORNAMENT->value, // image
        IngredientEnum::CANDY_CANE->value, // image
        IngredientEnum::FICSMAS_BOW->value, // image
        IngredientEnum::FICSMAS_TREE_BRANCH->value, // image
    ];

    protected $tier3 = [
        IngredientEnum::COPPER_FICSMAS_ORNAMENT->value, // image
        IngredientEnum::IRON_FICSMAS_ORNAMENT->value, // image
        IngredientEnum::FANCY_FIREWORKS->value, // image
        IngredientEnum::SNOWBALL->value, // image
        IngredientEnum::SPARKLY_FIREWORKS->value, // image
        IngredientEnum::SWEET_FIREWORKS->value, // image
    ];

    protected $tier4 = [
        IngredientEnum::FICSMAS_ORNAMENT_BUNDLE->value,
    ];

    protected $tier5 = [
        IngredientEnum::FICSMAS_DECORATION->value,
    ];

    protected $tier6 = [
        IngredientEnum::FICSMAS_WONDER_STAR->value,
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->tier1)
            ->each(fn ($name) => Ingredient::forceCreate(['name' => $name, 'raw' => true, 'tier' => 1, 'is_ficsmas' => true]));

        collect(range(2, 6))
            ->each(function ($num) {
                $tier = "tier{$num}";
                collect($this->$tier)
                    ->each(fn ($name) => Ingredient::forceCreate(['name' => $name, 'raw' => false, 'tier' => $num, 'is_ficsmas' => true]));
            });

    }
}
