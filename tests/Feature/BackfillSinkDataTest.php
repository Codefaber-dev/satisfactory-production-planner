<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use Database\Seeders\IngredientSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Non-destructive backfill of is_liquid + sink_points onto existing ingredient
 * rows (V65/V86, B52) — for databases seeded before those columns existed,
 * avoiding a destructive migrate:fresh.
 */
class BackfillSinkDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function backfill_sets_sink_data_on_existing_rows(): void
    {
        // simulate an old DB: rows present, sink data unset
        $plate = Ingredient::forceCreate(['name' => 'Reinforced Iron Plate', 'raw' => false, 'tier' => 5]);
        $water = Ingredient::forceCreate(['name' => 'Water', 'raw' => true, 'tier' => 1]);
        $fuel = Ingredient::forceCreate(['name' => 'Packaged Fuel', 'raw' => false, 'tier' => 4]);

        $this->assertNull($plate->fresh()->sink_points);
        $this->assertFalse((bool) $water->fresh()->is_liquid);

        (new IngredientSeeder)->backfill();

        $this->assertSame(120, $plate->fresh()->sink_points);
        $this->assertTrue($water->fresh()->is_liquid);
        $this->assertFalse($water->fresh()->isSinkable()); // fluid → not sinkable
        $this->assertSame(270, $fuel->fresh()->sink_points);
        $this->assertTrue($fuel->fresh()->isSinkable());
    }
}
