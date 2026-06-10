<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class FlushProductionCache extends Command
{
    protected $signature = 'flush-production-cache';

    protected $description = 'Flush the cached production lines';

    public function handle(): int
    {
        $connection = Redis::connection('cache');

        $flushed = 0;
        $flushed += $this->scanAndDelete($connection, '*production_calc*');
        $flushed += $this->scanAndDelete($connection, '*multi_production*');

        $this->info("Flushed {$flushed} keys total");

        return static::SUCCESS;
    }

    private function scanAndDelete(Connection $connection, string $pattern): int
    {
        $cursor = null;
        $count = 0;

        do {
            $result = $connection->scan($cursor, ['match' => $pattern, 'count' => 100]);

            if ($result === false) {
                break;
            }

            [$cursor, $batch] = $result;

            if ($batch) {
                $deleted = $connection->client()->rawCommand('DEL', ...$batch);
                $count += (int) $deleted;

                foreach ($batch as $key) {
                    $this->info("Flushed $key");
                }
            }
        } while ($cursor != 0);

        return $count;
    }
}
