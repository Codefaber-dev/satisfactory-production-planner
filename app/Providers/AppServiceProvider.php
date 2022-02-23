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
            $ret = collect($this->reduce(function ($a, $b) {
                $ret = [];

                if(is_array($b)) {
                    foreach ($b as $key => $val) {
                        if($val || $a[$key]) {
                            $ret[$key] = $val + ($a[$key] ?? 0);
                        }
                    }
                }

                return $ret;
            }, []));

            return $ret;
        });

        Collection::macro('crossSumByKey', function ($target) {
            return $this->pluck($target)->crossSum();
        });

        Collection::macro('sumByKey', function(){
            return $this->reduce(function($curr, $acc){
                foreach($curr as $key => $value) {
                    $acc[$key] = isset($acc[$key]) ? $acc[$key] + $value : $value;
                }
                return $acc;
            }, []);
        });

        Collection::macro('dataGet', function ($key) {
            return data_get($this,$key);
        });
    }
}
