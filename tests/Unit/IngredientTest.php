<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_a_name()
    {
        $this->expectException(QueryException::class);

        Ingredient::factory()->create([
            'name' => null,
        ]);
    }

    #[Test]
    public function it_has_a_unique_name()
    {
        $this->expectException(QueryException::class);

        Ingredient::factory()->create([
            'name' => 'Some Name',
        ]);

        Ingredient::factory()->create([
            'name' => 'Some Name',
        ]);
    }

    #[Test]
    public function it_can_be_raw()
    {
        $in1 = Ingredient::factory()->create([
            'raw' => true,
        ]);

        $this->assertTrue($in1->isRaw());

        $in2 = Ingredient::factory()->create([
            'raw' => false,
        ]);

        $this->assertFalse($in2->isRaw());
    }

    #[Test]
    public function it_can_have_a_tier()
    {
        $in = Ingredient::factory()->create([
            'tier' => 4,
        ]);

        $in->refresh();

        $this->assertEquals(4, $in->tier);
    }

    #[Test]
    public function it_can_have_multiple_recipes()
    {
        $in = Ingredient::factory()->create();

        $recipe2 = Recipe::factory()->count(3)->create([
            'product_id' => $in,
        ]);

        $this->assertCount(3, $in->recipes);
    }
}
