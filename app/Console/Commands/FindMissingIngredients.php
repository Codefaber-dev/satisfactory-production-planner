<?php

namespace App\Console\Commands;

use App\Enums\Ingredient as Enum;
use App\Models\Ingredient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FindMissingIngredients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find-missing-ingredients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find the missing ingredients';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('Missing Ingredients');
        collect(Enum::cases())->each(function (Enum $ingredient) {
            if (! Ingredient::ofName($ingredient->value)?->exists()) {
                $this->info($ingredient->value);
                Log::debug($ingredient->name);
            }
        });

        return self::SUCCESS;
    }
}
