<?php

namespace Tests\Feature;

use App\Models\Building;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T126 / V89: the AWESOME Sink building is seeded — one mk1 variant, base_power 30,
 * build cost from satisfactory.wiki.gg. Backs the recycle step's building + diagram
 * (V88). db:seed completes (V28).
 */
class SinkBuildingSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
    }

    #[Test]
    public function awesome_sink_building_is_seeded_with_base_power(): void
    {
        $building = Building::ofName('AWESOME Sink');

        $this->assertNotNull($building, 'AWESOME Sink should be seeded.');

        $variant = $building->variant('mk1');
        $this->assertNotNull($variant, 'AWESOME Sink should have an mk1 variant.');
        $this->assertEqualsWithDelta(30, $variant->base_power, 1e-9);
    }

    #[Test]
    public function awesome_sink_has_its_wiki_build_cost(): void
    {
        $variant = Building::ofName('AWESOME Sink')->variant('mk1');

        $cost = $variant->recipe->mapWithKeys(fn ($i) => [$i->name => $i->pivot->qty]);

        $this->assertSame(15.0, (float) $cost['Reinforced Iron Plate']);
        $this->assertSame(30.0, (float) $cost['Cable']);
        $this->assertSame(45.0, (float) $cost['Concrete']);
    }

    #[Test]
    public function db_seed_completes_without_exception(): void
    {
        // setUp seeded already; reaching here means V28 held with the new building.
        $this->assertTrue(true);
    }
}
