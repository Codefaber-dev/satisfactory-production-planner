<?php

namespace App\Console\Commands;

use App\Support\ProductionCanary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExportProductionFixtures extends Command
{
    protected $signature = 'satis:export-production-fixtures
                            {--path= : Output path (default: ProductionCanary::FIXTURE)}';

    protected $description = 'Snapshot current production-calculator output for the canary plan set, as a golden regression fixture';

    public function handle(): int
    {
        // ensure a clean recompute, not a cached result
        Cache::flush();

        $fixture = ProductionCanary::snapshotAll();

        $json = json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $path = $this->option('path') ?: ProductionCanary::FIXTURE;

        file_put_contents(base_path($path), $json."\n");

        $this->info('Production canary fixture written to '.$path.' ('.count($fixture).' plans)');

        return self::SUCCESS;
    }
}
