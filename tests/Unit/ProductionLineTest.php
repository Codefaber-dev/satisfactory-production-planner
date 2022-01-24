<?php

namespace Tests\Unit;

use App\Models\ProductionLine;
use Illuminate\Database\QueryException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductionLineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_has_a_name()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['name' => null]);
    }

    /**
     * @test
     */
    public function it_has_a_ingredient_id()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['ingredient_id' => null]);
    }

    /**
     * @test
     */
    public function it_has_a_recipe_id()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['recipe_id' => null]);
    }

    /**
     * @test
     */
    public function it_has_a_user_id()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['user_id' => null]);
    }

    /**
     * @test
     */
    public function it_has_a_yield()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['yield' => null]);
    }
}
