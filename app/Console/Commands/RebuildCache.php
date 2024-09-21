<?php

namespace App\Console\Commands;

use App\Enums\Ingredient;
use App\Enums\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RebuildCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rebuild-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the ingredient and recipe cache';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        collect(Ingredient::cases())->each(function ($ingredient) {
            $this->info("Caching $ingredient->value");

            i($ingredient->value, force: true);
        });

        collect(Recipe::cases())->each(function ($recipe) {
           $this->info("Caching $recipe->value");

           r($recipe->value, force: true);
        });

        $this->info("Caching all recipes");
        Cache::remember('all_recipes', now()->addDay(), function() {
            return \App\Models\Recipe::all()->groupBy(fn($recipe) => $recipe->product->name);
        });

        return static::SUCCESS;
    }
}
