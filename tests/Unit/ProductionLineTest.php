<?php

namespace Tests\Unit;

use App\Models\ProductionLine;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionLineTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_name()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['name' => null]);
    }

    #[Test]
    public function it_has_a_ingredient_id()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['ingredient_id' => null]);
    }

    #[Test]
    public function it_has_a_recipe_id()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['recipe_id' => null]);
    }

    #[Test]
    public function it_has_a_user_id()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['user_id' => null]);
    }

    #[Test]
    public function it_has_a_yield()
    {
        $this->expectException(QueryException::class);

        ProductionLine::factory()->create(['yield' => null]);
    }
}
