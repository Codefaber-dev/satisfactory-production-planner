<?php

namespace Tests\Unit;

use App\Models\Building;
use App\Models\BuildingVariant;
use App\Models\Ingredient;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class BuildingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_name()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'name' => null
        ]);
    }

    /** @test */
    public function it_has_a_inputs()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'inputs' => null
        ]);
    }

    /** @test */
    public function it_has_a_outputs()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'outputs' => null
        ]);
    }

    /** @test */
    public function it_has_a_height()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'height' => null
        ]);
    }

    /** @test */
    public function it_has_a_width()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'width' => null
        ]);
    }

    /** @test */
    public function it_has_a_length()
    {
        $this->expectException(QueryException::class);

        Building::factory()->create([
            'length' => null
        ]);
    }

    /** @test */
    public function it_has_variants()
    {
        $building = Building::factory()->create();

        $variants = BuildingVariant::factory()->count(3)->create(['building_id' => $building]);

        $this->assertTrue($building->variants()->skip(0)->first()->is($variants[0]));
        $this->assertTrue($building->variants()->skip(1)->first()->is($variants[1]));
        $this->assertTrue($building->variants()->skip(2)->first()->is($variants[2]));
    }


}
