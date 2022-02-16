<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Collection;
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
        Collection::macro('crossSum', function () {
            return $this->reduce(function ($a, $b) {
                $ret = [];

                if(is_array($b)) {
                    foreach ($b as $key => $val) {
                        $ret[$key] = $val + ($a[$key] ?? 0);
                    }
                }

                return $ret;
            }, []);
        });

        Collection::macro('crossSumByKey', function ($target) {
            return $this->pluck($target)->crossSum();
        });

        Collection::macro('dataGet', function ($key) {
            return data_get($this,$key);
        });
    }
}
