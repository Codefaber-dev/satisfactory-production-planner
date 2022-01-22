<?php

namespace App\Providers;

use App\Favorites\FavoritesRepository;
use App\Favorites\Implementations\GuestFavorites;
use App\Favorites\Implementations\UserFavorites;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
