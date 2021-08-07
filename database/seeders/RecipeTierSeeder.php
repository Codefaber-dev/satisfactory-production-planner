<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Recipe::where('alt_recipe',true)
            ->get()
            ->each(function($recipe){
                $recipe->alt_tier = $recipe->ingredients->max('tier')+1;
                $recipe->save();
            });
    }
}
