<?php

namespace App\Console\Commands;

use App\Enums\Ingredient;
use App\Enums\Recipe;
use App\Support\BaseDataPayload;
use Illuminate\Console\Command;

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

        $this->info('Caching base-data payloads');
        BaseDataPayload::flush();
        BaseDataPayload::recipes();
        BaseDataPayload::products();

        return static::SUCCESS;
    }
}
