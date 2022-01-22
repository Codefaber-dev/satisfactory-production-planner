<?php

namespace App\Favorites\Providers;

use App\Favorites\FavoritesRepository;
use App\Favorites\Implementations\GuestFavorites;
use App\Favorites\Implementations\UserFavorites;
use Illuminate\Support\ServiceProvider;

class FavoritesProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Favorites', function() {
            if (auth()->guest()) {
                return new FavoritesRepository(new GuestFavorites);
            }

            return new FavoritesRepository(new UserFavorites);
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
