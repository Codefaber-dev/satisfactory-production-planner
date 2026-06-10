<?php

namespace Tests\Unit;

use App\Helpers\ProductionTree;
use App\Helpers\RawIngredientCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionTreeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    // ─── ProductionTree ───────────────────────────────────────────────────────

    #[Test]
    public function tree_builds_without_exceptions(): void
    {
        $product = i('Iron Plate');
        $recipe = r('Iron Plate');

        $tree = ProductionTree::make($product, $recipe, 30);

        $this->assertNotNull($tree);
        $this->assertEmpty($tree->circularWarning);
    }

    #[Test]
    public function tree_amounts_contains_target_product(): void
    {
        $product = i('Iron Plate');
        $recipe = r('Iron Plate');
        $qty = 30.0;

        $tree = ProductionTree::make($product, $recipe, $qty);
        $tree->doWalk();

        // Root entry should be keyed by product name
        $keys = collect($tree->amounts)->keys();
        $this->assertTrue($keys->contains(fn ($k) => str_contains($k, 'Iron Plate')));
    }

    #[Test]
    public function tree_amounts_contains_raw_material(): void
    {
        $product = i('Iron Plate');
        $recipe = r('Iron Plate');

        $tree = ProductionTree::make($product, $recipe, 30.0);
        $tree->doWalk();

        // Iron Plate → Iron Ingot → Iron Ore (raw)
        $keys = collect($tree->amounts)->keys();
        $this->assertTrue(
            $keys->contains(fn ($k) => str_contains($k, 'Iron Ore')),
            'Tree must include raw material Iron Ore'
        );
    }

    #[Test]
    public function tree_sorted_contains_product_after_dependencies(): void
    {
        $product = i('Iron Plate');
        $recipe = r('Iron Plate');

        $tree = ProductionTree::make($product, $recipe, 30.0);
        $sorted = $tree->getSorted();

        // In topological order Iron Ore appears before Iron Ingot, Iron Ingot before Iron Plate
        $oreIdx = $sorted->search('Iron Ore');
        $ingotIdx = $sorted->search('Iron Ingot');
        $plateIdx = $sorted->search('Iron Plate');

        $this->assertNotFalse($oreIdx);
        $this->assertNotFalse($ingotIdx);
        $this->assertNotFalse($plateIdx);
        $this->assertLessThan($ingotIdx, $oreIdx);
        $this->assertLessThan($plateIdx, $ingotIdx);
    }

    #[Test]
    public function imports_short_circuit_walk(): void
    {
        $product = i('Iron Plate');
        $recipe = r('Iron Plate');

        $tree = ProductionTree::make($product, $recipe, 30.0, [], ['Iron Ingot']);
        $tree->doWalk();

        // Iron Ingot is imported, so its sub-dependency Iron Ore should NOT appear
        $keys = collect($tree->amounts)->keys();
        $this->assertFalse(
            $keys->contains(fn ($k) => str_contains($k, 'Iron Ore')),
            'Imported ingredient should block sub-walk'
        );
    }

    // ─── RawIngredientCalculator ──────────────────────────────────────────────

    #[Test]
    public function raw_calculator_returns_raw_materials_for_iron_plate(): void
    {
        $recipe = r('Iron Plate');
        $raw = RawIngredientCalculator::calc($recipe);

        $this->assertArrayHasKey('Iron Ore', $raw);
        $this->assertGreaterThan(0, $raw['Iron Ore']);
    }

    #[Test]
    public function raw_calculator_qty_scales_proportionally(): void
    {
        $recipe = r('Iron Plate');

        $raw1 = RawIngredientCalculator::calc($recipe, false, 1);
        $raw2 = RawIngredientCalculator::calc($recipe, false, 2);

        $this->assertEqualsWithDelta(
            $raw1['Iron Ore'] * 2,
            $raw2['Iron Ore'],
            1e-9
        );
    }

    #[Test]
    public function raw_calculator_sums_shared_raw_across_branches(): void
    {
        // Wire uses Copper Ingot → Copper Ore only
        $recipe = r('Wire');
        $raw = RawIngredientCalculator::calc($recipe);

        $this->assertArrayHasKey('Copper Ore', $raw);
        $this->assertArrayNotHasKey('Iron Ore', $raw);
    }
}
