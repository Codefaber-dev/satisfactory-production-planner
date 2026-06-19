<?php

use Database\Seeders\IngredientSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Backfill is_liquid + sink_points onto existing ingredient rows (V65/V86) so
 * recycling works on databases seeded before those columns existed — without a
 * destructive migrate:fresh that would drop saved factories. No-op on an empty
 * table (fresh installs populate via the seeder directly). Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new IngredientSeeder)->backfill();
    }

    public function down(): void
    {
        // data-only backfill; nothing to reverse
    }
};
