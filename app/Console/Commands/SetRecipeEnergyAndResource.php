<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

class SetRecipeEnergyAndResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setEnergy';

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
        Recipe::all()->each(function(Recipe $recipe){
            $name = $recipe->description ?? $recipe->product->name;


            if ( true ) { // } $recipe->energy == 0 ) {
                $this->info("Setting energy for recipe $name");
                $recipe->energy = $recipe->getEnergyUsage() ?? 0;
            }

            if ( true ) { // if ( $recipe->resource == 0 ) {
                $this->info("Setting resource for recipe $name");
                $recipe->resource = $recipe->getRarity() ?? 0;
            }


            $recipe->save();
        });

        return 0;
    }
}
