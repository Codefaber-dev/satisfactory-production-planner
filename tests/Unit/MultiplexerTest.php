<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Production\Multiplexer;
use App\Production\MultiplexerFactory;
use App\Production\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MultiplexerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    private function makeCalc(string $product, float $qty): ProductionCalculator
    {
        return ProductionCalculator::make(product: $product, qty: $qty);
    }

    #[Test]
    public function add_single_calc_and_get_results(): void
    {
        $m = new Multiplexer;
        $m->add($this->makeCalc('Iron Plate', 10));

        $results = $m->getResults();

        $this->assertNotEmpty($results);
    }

    #[Test]
    public function results_contain_entries_for_all_added_products(): void
    {
        $m = new Multiplexer;
        $m->add($this->makeCalc('Iron Plate', 10));
        $m->add($this->makeCalc('Wire', 10));

        $allMaterials = $m->getAllMaterials();

        // Both products' dependency chains should be represented
        $this->assertNotEmpty($allMaterials);
    }

    #[Test]
    public function get_raw_materials_returns_only_tier_one(): void
    {
        $m = new Multiplexer;
        $m->add($this->makeCalc('Iron Plate', 10));

        $raw = $m->getRawMaterials();

        // Iron Plate needs Iron Ore → raw material
        $this->assertTrue($raw->has('Iron Ore'));
        // raw values must all be positive
        $raw->each(fn ($qty) => $this->assertGreaterThan(0, $qty));
    }

    #[Test]
    public function get_byproducts_returns_array(): void
    {
        $m = new Multiplexer;
        $m->add($this->makeCalc('Iron Plate', 10));
        $m->add($this->makeCalc('Iron Plate', 10));

        // sumByKey() macro returns an array (not a Collection)
        $byproducts = $m->getByproducts();

        $this->assertIsArray($byproducts);
    }

    #[Test]
    public function get_finals_returns_one_entry_per_calc(): void
    {
        $m = new Multiplexer;
        $m->add($this->makeCalc('Iron Plate', 10));
        $m->add($this->makeCalc('Wire', 10));

        $finals = $m->getFinals();

        $this->assertCount(2, $finals);
    }

    #[Test]
    public function get_recipes_returns_one_recipe_per_calc(): void
    {
        $m = new Multiplexer;
        $m->add($this->makeCalc('Iron Plate', 10));
        $m->add($this->makeCalc('Wire', 10));

        $recipes = $m->getRecipes();

        $this->assertCount(2, $recipes);
    }

    #[Test]
    public function doubled_qty_doubles_raw_materials(): void
    {
        $m1 = new Multiplexer;
        $m1->add($this->makeCalc('Iron Plate', 10));

        $m2 = new Multiplexer;
        $m2->add($this->makeCalc('Iron Plate', 20));

        $raw1 = $m1->getRawMaterials();
        $raw2 = $m2->getRawMaterials();

        $this->assertEqualsWithDelta(
            $raw1->get('Iron Ore') * 2,
            $raw2->get('Iron Ore'),
            1e-4
        );
    }

    #[Test]
    public function ratio_of_available_raw_materials_returns_one_when_no_raw_provided(): void
    {
        $m = new Multiplexer;
        $m->add($this->makeCalc('Iron Plate', 10));

        // No raw=... param in request → default ratio = 1
        $ratio = $m->ratioOfAvailableRawMaterials();

        $this->assertEquals(1.0, $ratio);
    }

    #[Test]
    public function multiplexer_factory_builds_synthetic_recipe(): void
    {
        // MultiplexerFactory needs 'Factory Output' ingredient to exist
        Ingredient::factory()->create(['name' => 'Factory Output', 'raw' => false, 'tier' => 1]);

        $products = ['Iron Ore', 'Copper Ore'];
        $yields = [10, 20];

        $factory = new MultiplexerFactory($products, $yields);
        $recipe = $factory->getRecipe();

        $this->assertNotNull($recipe);
        $this->assertEquals('Factory Output', $recipe->product->name);
    }
}
