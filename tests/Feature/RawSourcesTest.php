<?php

namespace Tests\Feature;

use App\Models\ProductionLine;
use App\Production\ProductionGlobals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RawSourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function get_raw_source_defaults_to_import(): void
    {
        // V59: a raw with no explicit config defaults to import (current leaf behavior).
        $globals = ProductionGlobals::make([], [], [], [], [], 'mk1', []);

        $this->assertSame(['mode' => 'import'], $globals->getRawSource('Iron Ore'));
    }

    #[Test]
    public function get_raw_source_returns_stored_config(): void
    {
        request()->merge(['raw_sources' => ['Iron Ore' => ['mode' => 'extract', 'purity' => 'normal']]]);

        $globals = ProductionGlobals::make([], [], [], [], [], 'mk1', []);

        $this->assertSame(['mode' => 'extract', 'purity' => 'normal'], $globals->getRawSource('Iron Ore'));
        $this->assertSame(['mode' => 'import'], $globals->getRawSource('Copper Ore'));
    }

    #[Test]
    public function factory_save_persists_raw_sources_and_plan_url_round_trips(): void
    {
        // V59: raw_sources persists across save and is restored via the Plan URL.
        $rawSources = ['Iron Ore' => ['mode' => 'extract', 'purity' => 'pure', 'shards' => 3]];

        $this->actingAsUser()->post('/factories', [
            'name' => 'Raw Test',
            'ingredient_id' => i('Iron Plate')->id,
            'recipe_id' => r('Iron Plate')->id,
            'yield' => 30,
            'choices' => [],
            'imports' => '',
            'raw_sources' => $rawSources,
        ])->assertRedirect();

        $line = ProductionLine::where('name', 'Raw Test')->firstOrFail();

        $this->assertSame($rawSources, $line->raw_sources);
        $this->assertStringContainsString('raw_sources', $line->getPlanUrl());
    }

    #[Test]
    public function default_import_keeps_raw_a_leaf_with_no_building(): void
    {
        // V59: default (no raw_sources) → raw stays a tree leaf, no extractor/building.
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('raw_sources', [])->etc());
    }
}
