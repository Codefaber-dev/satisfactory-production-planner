<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Models\BuildingVariant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BuildingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_name()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'name' => null,
        ]);
    }

    #[Test]
    public function it_has_a_inputs()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'inputs' => null,
        ]);
    }

    #[Test]
    public function it_has_a_outputs()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'outputs' => null,
        ]);
    }

    #[Test]
    public function it_has_a_height()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'height' => null,
        ]);
    }

    #[Test]
    public function it_has_a_width()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'width' => null,
        ]);
    }

    #[Test]
    public function it_has_a_length()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'length' => null,
        ]);
    }

    #[Test]
    public function of_name_returns_the_building(): void
    {
        $building = Building::factory()->create(['name' => 'Smelter']);

        $this->assertTrue(Building::ofName('Smelter')->is($building));
    }

    #[Test]
    public function of_name_returns_null_for_a_missing_building(): void
    {
        // B61: the old scopeOfName returned ->first() from inside a scope; on a
        // miss, callScope()'s `?? $this` handed callers the Builder itself, and
        // optional(Builder)->variant('mk1') fataled with Builder::variant().
        $this->assertNull(Building::ofName('Does Not Exist'));
    }

    #[Test]
    public function variant_returns_the_named_variant(): void
    {
        $building = Building::factory()->create();
        $variant = BuildingVariant::factory()->create(['building_id' => $building, 'name' => 'mk1']);

        $this->assertTrue($building->fresh()->variant('mk1')->is($variant));
    }

    #[Test]
    public function variant_returns_null_for_a_missing_variant(): void
    {
        // B61: variants()->ofName() had the same scope-null fallback — a missing
        // variant name returned the HasMany builder instead of null.
        $building = Building::factory()->create();

        $this->assertNull($building->fresh()->variant('mk9'));
    }

    #[Test]
    public function it_has_variants()
    {
        $building = Building::factory()->create();

        $variants = BuildingVariant::factory()->count(3)->create(['building_id' => $building]);

        $this->assertTrue($building->variants()->skip(0)->first()->is($variants[0]));
        $this->assertTrue($building->variants()->skip(1)->first()->is($variants[1]));
        $this->assertTrue($building->variants()->skip(2)->first()->is($variants[2]));
    }
}
