<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportGameDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Storage::fake('local');
    }

    #[Test]
    public function command_exits_successfully(): void
    {
        $this->artisan('satis:export-game-data')
            ->assertExitCode(0);
    }

    #[Test]
    public function command_creates_json_file_with_expected_keys(): void
    {
        $this->artisan('satis:export-game-data')->assertExitCode(0);

        Storage::assertExists('game_data_export.json');

        $raw = Storage::get('game_data_export.json');
        $data = json_decode($raw, true);

        $this->assertArrayHasKey('ingredients', $data);
        $this->assertArrayHasKey('recipes', $data);
        $this->assertArrayHasKey('buildings', $data);
        $this->assertArrayHasKey('raw_materials', $data);
    }

    #[Test]
    public function command_respects_custom_path_option(): void
    {
        $this->artisan('satis:export-game-data', ['--path' => 'exports/custom.json'])
            ->assertExitCode(0);

        Storage::assertExists('exports/custom.json');
    }

    #[Test]
    public function exported_ingredients_are_non_empty(): void
    {
        $this->artisan('satis:export-game-data')->assertExitCode(0);

        $data = json_decode(Storage::get('game_data_export.json'), true);

        $this->assertNotEmpty($data['ingredients']);
        $this->assertArrayHasKey('name', $data['ingredients'][0]);
    }

    #[Test]
    public function exported_recipes_include_ingredients_and_building(): void
    {
        $this->artisan('satis:export-game-data')->assertExitCode(0);

        $data = json_decode(Storage::get('game_data_export.json'), true);
        $recipe = collect($data['recipes'])->first(fn ($r) => ! empty($r['ingredients']));

        $this->assertNotNull($recipe, 'At least one recipe must have ingredients');
        $this->assertArrayHasKey('building', $recipe);
    }

    #[Test]
    public function exported_raw_materials_match_config(): void
    {
        $this->artisan('satis:export-game-data')->assertExitCode(0);

        $data = json_decode(Storage::get('game_data_export.json'), true);

        $this->assertSame(config('raw_materials'), $data['raw_materials']);
    }
}
