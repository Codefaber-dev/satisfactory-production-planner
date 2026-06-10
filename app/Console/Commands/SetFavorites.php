<?php

namespace App\Console\Commands;

use App\Enums\Recipe as RecipeEnum;
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
            RecipeEnum::PURE_ALUMINUM_INGOT->value,
            RecipeEnum::BIOMASS_LEAVES->value,
            RecipeEnum::BIOCOAL->value,
            RecipeEnum::STEEL_SCREW->value,
            RecipeEnum::CATERIUM_WIRE->value,
            RecipeEnum::INSULATED_CABLE->value,
            RecipeEnum::CATERIUM_CIRCUIT_BOARD->value,
            RecipeEnum::ENCASED_INDUSTRIAL_PIPE->value,
            'Steel Coated Plate',
            RecipeEnum::STEELED_FRAME->value,
            RecipeEnum::STITCHED_IRON_PLATE->value,
            RecipeEnum::SOLID_STEEL_INGOT->value,
            RecipeEnum::SLOPPY_ALUMINA->value,
            RecipeEnum::WET_CONCRETE->value,
            RecipeEnum::CATERIUM_COMPUTER->value,
            RecipeEnum::HEAVY_ENCASED_FRAME->value,
            RecipeEnum::DILUTED_FUEL->value,
        ];

        collect($defaults)
            ->each(function ($recipe) {
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

            $recipe = Recipe::find((int) (string) Str::of($ingredient->selectChoice($choice))->after('id:'));

            $description = $recipe->description ?: 'default';

            $this->info("You chose $description");

            auth()->user()->addFavorite($recipe);
        });

        return 0;
    }
}
