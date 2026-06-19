<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T108 / V65: AWESOME Sink points data + sinkability.
 *
 * Sinkable = solid only (is_liquid false) with a positive sink_points value.
 * Unpackaged fluids/gases are NOT sinkable; their Packaged forms ARE. Items with
 * no value are not recyclable. Recyclable points/min = qty/min × sink_points.
 */
class SinkPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function a_known_solid_item_has_sink_points_and_is_sinkable(): void
    {
        $plate = i('Reinforced Iron Plate');

        $this->assertSame(120, $plate->sink_points);
        $this->assertFalse($plate->is_liquid);
        $this->assertTrue($plate->isSinkable());
    }

    #[Test]
    public function an_unpackaged_fluid_is_not_sinkable(): void
    {
        // V65: Water is a fluid (is_liquid) → never sinkable, even with no points.
        $water = i('Water');

        $this->assertTrue($water->is_liquid);
        $this->assertFalse($water->isSinkable());
    }

    #[Test]
    public function a_packaged_fluid_is_sinkable(): void
    {
        // V65: the Packaged form is a solid item and carries a points value.
        $packaged = i('Packaged Water');

        $this->assertFalse($packaged->is_liquid);
        $this->assertSame(130, $packaged->sink_points);
        $this->assertTrue($packaged->isSinkable());
    }

    #[Test]
    public function packaged_fluids_carry_wiki_sink_points(): void
    {
        // V65: packaged fluid/gas forms are solid + sinkable (satisfactory.wiki.gg).
        $this->assertSame(270, i('Packaged Fuel')->sink_points);
        $this->assertSame(180, i('Packaged Heavy Oil Residue')->sink_points);
        $this->assertSame(5246, i('Packaged Ionized Fuel')->sink_points);
        $this->assertTrue(i('Packaged Fuel')->isSinkable());
    }

    #[Test]
    public function an_item_with_no_value_is_not_recycled(): void
    {
        // V65: no sink value → not sinkable (null, not 0).
        $motor = i('Motor');

        $this->assertNull($motor->sink_points);
        $this->assertFalse($motor->isSinkable());
        $this->assertSame(0.0, $motor->recyclablePoints(60));
    }

    #[Test]
    public function recyclable_points_is_qty_times_sink_points(): void
    {
        // V65: recyclable points/min = qty/min × sink_points for a sinkable item.
        $this->assertSame(1200.0, i('Reinforced Iron Plate')->recyclablePoints(10));
        // a fluid yields no recyclable points regardless of qty
        $this->assertSame(0.0, i('Water')->recyclablePoints(120));
    }
}
