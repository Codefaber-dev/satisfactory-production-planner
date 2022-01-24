<?php

namespace App\Factories\Providers;

use App\Factories\FactoriesRepository;
use App\Factories\Implementations\GuestFactories;
use App\Factories\Implementations\UserFactories;
use Illuminate\Support\ServiceProvider;

class FactoriesProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Factories', function() {
            if (auth()->guest()) {
                return new FactoriesRepository(new GuestFactories);
            }

            return new FactoriesRepository(new UserFactories);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
