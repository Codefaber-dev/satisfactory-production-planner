<?php

namespace Tests\Feature;

use App\Models\MultiProductionLine;
use App\Models\ProductionLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T107 / V64 / V32 / V57: per-ingredient import notes.
 *
 * `import_notes` is a plan param (map ingredient → free-text note), persisted to
 * the saved factory (single + multi) and restored via the Plan URL round-trip.
 * It carries no calc impact (V64) — purely display/persistence.
 */
class ImportNotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function single_factory_save_persists_import_notes_and_plan_url_round_trips(): void
    {
        // V64/V32/V57: import_notes persists across save and is restored via the Plan URL.
        // An empty note is normalised to null by Laravel's ConvertEmptyStringsToNull
        // middleware (V64: empty = no display, so null is fine).
        $importNotes = ['Caterium Ingot' => 'From NW Steel Factory', 'Copper Ingot' => ''];

        $this->actingAsUser()->post('/factories', [
            'name' => 'Notes Test',
            'ingredient_id' => i('Iron Plate')->id,
            'recipe_id' => r('Iron Plate')->id,
            'yield' => 30,
            'choices' => [],
            'imports' => 'Caterium Ingot',
            'import_notes' => $importNotes,
        ])->assertRedirect();

        $line = ProductionLine::where('name', 'Notes Test')->firstOrFail();

        $this->assertSame('From NW Steel Factory', $line->import_notes['Caterium Ingot']);
        $this->assertNull($line->import_notes['Copper Ingot']);
        $this->assertStringContainsString('import_notes', $line->getPlanUrl());
        $this->assertStringContainsString('From+NW+Steel+Factory', $line->getPlanUrl());
    }

    #[Test]
    public function multi_factory_save_persists_import_notes(): void
    {
        // V64: multi-output factories persist import_notes the same way.
        $importNotes = ['Iron Ore' => 'mined on site'];

        $this->actingAsUser()->post('/factories/multi', [
            'name' => 'Multi Notes Test',
            'outputs' => [
                ['product' => ['id' => i('Iron Plate')->id, 'name' => 'Iron Plate'], 'yield' => 30, 'recipe' => ['id' => r('Iron Plate')->id, 'description' => null]],
            ],
            'choices' => [],
            'imports' => '',
            'import_notes' => $importNotes,
        ])->assertRedirect();

        $line = MultiProductionLine::where('name', 'Multi Notes Test')->firstOrFail();

        $this->assertSame($importNotes, $line->import_notes);
        $this->assertStringContainsString('import_notes', $line->getPlanUrl());
    }

    #[Test]
    public function show_endpoint_returns_import_notes_prop(): void
    {
        // The Plan page receives the import_notes map so the UI can restore notes.
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate?'.http_build_query([
            'import_notes' => ['Iron Ore' => 'hello'],
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('import_notes', ['Iron Ore' => 'hello'])->etc());
    }

    #[Test]
    public function show_endpoint_defaults_import_notes_to_empty(): void
    {
        // V64: no import_notes param → empty map (no display), plan output unchanged.
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('import_notes', [])->etc());
    }
}
