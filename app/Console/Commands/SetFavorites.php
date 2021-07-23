<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetFavorites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setFavorites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        auth()->loginUsingId(1);

        $defaults = [
            "Pure Aluminum Ingot",
            "Biomass (Leaves)",
            "Biocoal",
            "Steel Screw",
            "Caterium Wire",
            "Insulated Cable",
            "Caterium Circuit Board",
            "Encased Industrial Pipe",
            "Steel Coated Plate",
            "Steeled Frame",
            "Stitched Iron Plate",
            "Solid Steel Ingot",
            "Sloppy Alumina",
            "Wet Concrete",
            "Caterium Computer",
            "Heavy Encased Frame",
            "Diluted Fuel",
        ];

        collect($defaults)
            ->each(function($recipe){
                auth()->user()->addFavoriteByName($recipe);
            });

        $ingredients = Ingredient::with(['recipes.ingredients', 'recipes.byproducts'])
            ->has('recipes')
            ->orderBy('name')
            ->get()
            ->filter(function ($ingredient) {
                return $ingredient->recipes()->count() > 1;
            });

        $this->info("Setting favorites for {$ingredients->count()} ingredients");

        $ingredients->each(function ($ingredient) {

            $choice = $this->choice("Select a recipe for {$ingredient->name}", $choices = $ingredient->getRecipeChoices()
                ->values()
                ->all(), 0);

            $recipe = Recipe::find((int) (string) Str::of($ingredient->selectChoice($choice))->after("id:"));

            $description = $recipe->description ?: "default";

            $this->info("You chose $description");

            auth()->user()->addFavorite($recipe);
        });

        return 0;
    }
}
