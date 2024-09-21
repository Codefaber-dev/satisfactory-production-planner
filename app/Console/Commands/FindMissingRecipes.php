<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Enums\Recipe as Enum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FindMissingRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find-missing-recipes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find the missing recipes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->warn("Missing Recipes");
        collect(Enum::cases())->each(function (Enum $recipe) {
            if (! Recipe::ofName($recipe->value)?->exists()) {
                $this->info($recipe->value);
                Log::debug($recipe->name);
            }
        });

        return self::SUCCESS;
    }
}
