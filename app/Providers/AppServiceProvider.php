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
        // seo stuff
        seo()->title(config('app.name'))
            ->description(config('app.description'))
            ->url(config('app.url'))
            ->twitterCreator(config('app.twitterUsername'))
            ->twitterImage(config('app.cardImage'))
            ->twitter()
            ->favicon();

        Collection::macro('crossSum', function () {
            if ($this->isEmpty()) {
                return 0;
            }

            $ret = collect($this->reduce(function ($a, $b) {
                $ret = [];

                if(is_array($b)) {
                    foreach ($b as $key => $val) {
                        if(!is_null($val) || $a[$key]) {
                            $ret[$key] = $val + ($a[$key] ?? 0);
                        }
                    }
                }

                return $ret;
            }, []));

            return $ret;
        });

        Collection::macro('crossSumByKey', function ($target) {
            if ( ! $this->filter(fn($row) => isset($row[$target]) && !! $row[$target])->count() ) {
                return 0;
            }

            return $this->filter(fn($row) => isset($row[$target]) && !! $row[$target])->pluck($target)->crossSum();
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
