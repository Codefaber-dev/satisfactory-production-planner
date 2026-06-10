<?php

namespace App\Production\Concerns;

use Illuminate\Support\Facades\Redis;

trait InvalidatesProductionCache
{
    private function flushProductionCalcKeys(): void
    {
        $connection = Redis::connection('cache');
        $cursor = null;

        do {
            $result = $connection->scan($cursor, ['match' => '*production_calc*', 'count' => 100]);

            if ($result === false) {
                break;
            }

            [$cursor, $batch] = $result;

            if ($batch) {
                $connection->client()->rawCommand('DEL', ...$batch);
            }
        } while ($cursor != 0);
    }
}
