<?php

namespace Tests\Feature;

use App\Models\Building;
use Database\Seeders\BuildingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * B61: databases seeded before v1.3.0 lack the extractor + AWESOME Sink
 * buildings (BuildingSeeder only ran on fresh installs). backfill() inserts
 * just the missing buildings — non-destructive and idempotent, same pattern
 * as IngredientSeeder::backfill (V86).
 */
class BuildingBackfillTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_BUILDINGS = [
        'Miner Mk.1', 'Miner Mk.2', 'Miner Mk.3',
        'Water Extractor', 'Oil Extractor', 'Resource Well Pressurizer',
        'AWESOME Sink',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
    }

    #[Test]
    public function backfill_restores_missing_buildings_with_variants_and_recipes(): void
    {
        Building::whereIn('name', self::NEW_BUILDINGS)->delete();
        $countBefore = Building::count();

        (new BuildingSeeder)->backfill();

        foreach (self::NEW_BUILDINGS as $name) {
            $building = Building::ofName($name);
            $this->assertNotNull($building, "$name should be backfilled");

            $variant = $building->variant('mk1');
            $this->assertNotNull($variant, "$name should have its mk1 variant");
            $this->assertGreaterThan(0, $variant->recipe->count(), "$name mk1 variant should have a build recipe");
        }

        $this->assertSame($countBefore + count(self::NEW_BUILDINGS), Building::count());
    }

    #[Test]
    public function backfill_is_idempotent_on_a_fully_seeded_database(): void
    {
        $count = Building::count();

        (new BuildingSeeder)->backfill();

        $this->assertSame($count, Building::count());
    }
}
