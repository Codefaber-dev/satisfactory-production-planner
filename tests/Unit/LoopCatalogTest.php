<?php

namespace Tests\Unit;

use App\Production\LoopCatalog;
use PHPUnit\Framework\TestCase;

class LoopCatalogTest extends TestCase
{
    public function test_detects_mutual_loop_pair(): void
    {
        $recipes = [
            ['product' => 'Plastic', 'recipe' => 'Recycled Plastic', 'ingredients' => ['Rubber', 'Heavy Oil Residue']],
            ['product' => 'Rubber', 'recipe' => 'Recycled Rubber', 'ingredients' => ['Plastic', 'Heavy Oil Residue']],
            ['product' => 'Plastic', 'recipe' => 'Plastic', 'ingredients' => ['Polymer Resin']],
            ['product' => 'Rubber', 'recipe' => 'Rubber', 'ingredients' => ['Polymer Resin']],
        ];

        $clusters = LoopCatalog::detect($recipes, ['Heavy Oil Residue', 'Polymer Resin']);

        $this->assertCount(1, $clusters);
        $this->assertEqualsCanonicalizing(['Plastic', 'Rubber'], $clusters[0]['members']);

        $enabling = array_map(fn ($e) => $e['recipe'], $clusters[0]['enabledBy']);
        $this->assertContains('Recycled Plastic', $enabling);
        $this->assertContains('Recycled Rubber', $enabling);
        // base recipes do not create an intra-cluster edge → not enabling
        $this->assertNotContains('Plastic', $enabling);
        $this->assertNotContains('Rubber', $enabling);
    }

    public function test_acyclic_catalog_has_no_clusters(): void
    {
        $recipes = [
            ['product' => 'Iron Plate', 'recipe' => 'Iron Plate', 'ingredients' => ['Iron Ingot']],
            ['product' => 'Iron Ingot', 'recipe' => 'Iron Ingot', 'ingredients' => ['Iron Ore']],
        ];

        $this->assertSame([], LoopCatalog::detect($recipes, ['Iron Ore']));
    }

    public function test_boundary_node_cuts_the_loop(): void
    {
        // A -> B -> A would cycle, but B is a raw/import boundary node → no cluster
        $recipes = [
            ['product' => 'A', 'recipe' => 'A', 'ingredients' => ['B']],
            ['product' => 'B', 'recipe' => 'B', 'ingredients' => ['A']],
        ];

        $this->assertSame([], LoopCatalog::detect($recipes, ['B']));
    }

    public function test_three_node_cycle_detected_as_single_cluster(): void
    {
        $recipes = [
            ['product' => 'A', 'recipe' => 'A', 'ingredients' => ['B']],
            ['product' => 'B', 'recipe' => 'B', 'ingredients' => ['C']],
            ['product' => 'C', 'recipe' => 'C', 'ingredients' => ['A']],
        ];

        $clusters = LoopCatalog::detect($recipes, []);

        $this->assertCount(1, $clusters);
        $this->assertEqualsCanonicalizing(['A', 'B', 'C'], $clusters[0]['members']);
    }
}
