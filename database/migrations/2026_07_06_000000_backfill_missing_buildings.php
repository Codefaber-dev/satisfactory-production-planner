<?php

use Database\Seeders\BuildingSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Backfill the extractor + AWESOME Sink buildings (B61/V109) onto databases
 * seeded before v1.3.0 — BuildingSeeder only runs on fresh installs, so prod
 * lacked the rows and every recycle/extract plan fataled on the missing
 * building lookup. Inserts only missing buildings; idempotent; no-op on a
 * freshly seeded database.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new BuildingSeeder)->backfill();
    }

    public function down(): void
    {
        // data-only backfill; nothing to reverse
    }
};
