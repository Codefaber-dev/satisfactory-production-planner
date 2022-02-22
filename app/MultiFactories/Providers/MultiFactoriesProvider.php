<?php

namespace App\MultiFactories\Providers;

use App\MultiFactories\MultiFactoriesRepository;
use App\MultiFactories\Implementations\GuestMultiFactories;
use App\MultiFactories\Implementations\UserMultiFactories;
use Illuminate\Support\ServiceProvider;

class MultiFactoriesProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('MultiFactories', function() {
            if (auth()->guest()) {
                return new MultiFactoriesRepository(new GuestMultiFactories);
            }

            return new MultiFactoriesRepository(new UserMultiFactories);
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
