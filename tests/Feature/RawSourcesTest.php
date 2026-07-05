<?php

namespace Tests\Feature;

use App\Models\MultiProductionLine;
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

    #[Test]
    public function crafted_unknown_raw_source_name_is_ignored(): void
    {
        // V105(a) / B59(a): a raw_sources key that is not a real ingredient must be
        // ignored, not passed to defaultRawRecipe(null) (TypeError → 500).
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate?'.http_build_query([
            'raw_sources' => ['Bogus' => ['mode' => 'convert']],
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function non_raw_ingredient_in_raw_sources_is_ignored(): void
    {
        // V105(a): raw_sources only applies to raws — a crafted non-raw name
        // (Iron Plate is a product) must be filtered out, not treated as a raw.
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate?'.http_build_query([
            'raw_sources' => ['Iron Plate' => ['mode' => 'convert']],
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function recipe_less_raw_in_convert_mode_is_ignored(): void
    {
        // V105(a) / B59(b): Wood is a raw with no converter/unpackage recipe. In
        // convert mode it must not reach the detectLoops candidate merge, where
        // baseRecipe() throws on a recipe-less member (500).
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate?'.http_build_query([
            'raw_sources' => ['Wood' => ['mode' => 'convert']],
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function oversized_import_note_is_rejected_on_save(): void
    {
        // V105(b): import_notes values are bounded (≤ 500 chars) — unbounded
        // free text must not persist into the JSON column.
        $this->actingAsUser()->post('/factories', [
            'name' => 'Oversized Note',
            'ingredient_id' => i('Iron Plate')->id,
            'recipe_id' => r('Iron Plate')->id,
            'yield' => 30,
            'import_notes' => ['Iron Ore' => str_repeat('a', 501)],
        ])->assertSessionHasErrors('import_notes.Iron Ore');

        $this->assertNull(ProductionLine::where('name', 'Oversized Note')->first());
    }

    #[Test]
    public function invalid_raw_source_fields_are_rejected_on_save(): void
    {
        // V105(b): mode/purity/miner enums + shards 0–3 validated on persistence.
        $base = [
            'name' => 'Invalid Config',
            'ingredient_id' => i('Iron Plate')->id,
            'recipe_id' => r('Iron Plate')->id,
            'yield' => 30,
        ];

        $this->actingAsUser()->post('/factories', $base + [
            'raw_sources' => ['Iron Ore' => ['mode' => 'hack']],
        ])->assertSessionHasErrors('raw_sources.Iron Ore.mode');

        $this->actingAsUser()->post('/factories', $base + [
            'raw_sources' => ['Iron Ore' => ['mode' => 'extract', 'shards' => 5]],
        ])->assertSessionHasErrors('raw_sources.Iron Ore.shards');

        $this->actingAsUser()->post('/factories', $base + [
            'raw_sources' => ['Iron Ore' => ['mode' => 'extract', 'purity' => 'radiant']],
        ])->assertSessionHasErrors('raw_sources.Iron Ore.purity');

        $this->actingAsUser()->post('/factories', $base + [
            'raw_sources' => ['Iron Ore' => ['mode' => 'extract', 'miner' => 'mk9']],
        ])->assertSessionHasErrors('raw_sources.Iron Ore.miner');

        $this->assertNull(ProductionLine::where('name', 'Invalid Config')->first());
    }

    #[Test]
    public function multi_factory_save_validates_raw_sources_and_notes(): void
    {
        // V105(b): MultiProductionLineController applies the same validation.
        $this->actingAsUser()->post('/factories/multi', [
            'name' => 'Multi Invalid',
            'outputs' => [
                ['product' => ['id' => i('Iron Plate')->id, 'name' => 'Iron Plate'], 'yield' => 30, 'recipe' => ['id' => r('Iron Plate')->id, 'description' => null]],
            ],
            'raw_sources' => ['Iron Ore' => ['mode' => 'hack']],
            'import_notes' => ['Iron Ore' => str_repeat('a', 501)],
        ])->assertSessionHasErrors(['raw_sources.Iron Ore.mode', 'import_notes.Iron Ore']);

        $this->assertNull(MultiProductionLine::where('name', 'Multi Invalid')->first());
    }

    #[Test]
    public function unknown_raw_source_config_keys_are_dropped_on_save(): void
    {
        // V105(b): only {mode, purity, miner, shards} persist — crafted extra keys
        // never reach the JSON column.
        $this->actingAsUser()->post('/factories', [
            'name' => 'Sanitized',
            'ingredient_id' => i('Iron Plate')->id,
            'recipe_id' => r('Iron Plate')->id,
            'yield' => 30,
            'raw_sources' => ['Iron Ore' => ['mode' => 'extract', 'purity' => 'pure', 'payload' => 'x']],
        ])->assertRedirect();

        $line = ProductionLine::where('name', 'Sanitized')->firstOrFail();

        $this->assertSame(['mode' => 'extract', 'purity' => 'pure'], $line->raw_sources['Iron Ore']);
    }
}
