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
    public function it_has_variants()
    {
        $building = Building::factory()->create();

        $variants = BuildingVariant::factory()->count(3)->create(['building_id' => $building]);

        $this->assertTrue($building->variants()->skip(0)->first()->is($variants[0]));
        $this->assertTrue($building->variants()->skip(1)->first()->is($variants[1]));
        $this->assertTrue($building->variants()->skip(2)->first()->is($variants[2]));
    }
}
