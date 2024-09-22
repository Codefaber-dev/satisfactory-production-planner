<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class FlushProductionCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flush-production-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the cached production lines';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Redis::select(1);

        $keys = collect(Redis::keys('*production_calc*'));

        $this->info("Flushing {$keys->count()} keys");

        $prefix = config('cache.prefix');

        $keys->each(function ($item) use ($prefix) {
            $key = str($item)->after($prefix . ":");

            if (Cache::forget($key)) {
                $this->info("Flushed $key");
            }
            else {
                $this->warn("$key is not a valid cache key");
            }
        });

        return static::SUCCESS;
    }
}
