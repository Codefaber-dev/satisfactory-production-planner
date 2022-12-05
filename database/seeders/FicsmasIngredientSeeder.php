<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class FicsmasIngredientSeeder extends Seeder
{
    protected $tier1 = [
        'FICSMAS Gift' //image
    ];

    protected $tier2 = [
        'Actual Snow', //image
        'Blue FICSMAS Ornament', //image
        'Red FICSMAS Ornament', //image
        'Candy Cane', //image
        'FICSMAS Bow', //image
        'FICSMAS Tree Branch', //image
    ];

    protected $tier3 = [
        'Copper FICSMAS Ornament',//image
        'Iron FICSMAS Ornament',//image
        'Fancy Fireworks',//image
        'Snowball', //image
        'Sparkly Fireworks',//image
        'Sweet Fireworks',//image
    ];

    protected $tier4 = [
        'FICSMAS Ornament Bundle', //image
    ];

    protected $tier5 = [
        'FICSMAS Decoration',
    ];

    protected $tier6 = [
        'FICSMAS Wonder Star'
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->tier1)
            ->each(fn($name) => Ingredient::forceCreate(['name' => $name, 'raw' => true, 'tier' => 1, 'is_ficsmas' => true]));

        collect(range(2,6))
            ->each(function($num) {
                $tier = "tier{$num}";
                collect($this->$tier)
                    ->each(fn($name) => Ingredient::forceCreate(['name' => $name, 'raw' => false, 'tier' => $num, 'is_ficsmas' => true]));
            });

    }
}
