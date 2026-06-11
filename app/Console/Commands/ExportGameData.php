<?php

namespace App\Console\Commands;

use App\Models\Building;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportGameData extends Command
{
    protected $signature = 'satis:export-game-data
                            {--path= : Output file path relative to storage/app (default: game_data_export.json)}
                            {--stdout : Print JSON to stdout instead of writing to file}';

    protected $description = 'Export all game data (ingredients, recipes, buildings, raw_materials) as JSON for self-hosting / Docker seed verification';

    public function handle(): int
    {
        $data = [
            'ingredients' => Ingredient::orderBy('name')->get()->toArray(),
            'recipes' => Recipe::with(['ingredients', 'byproducts', 'building'])->orderBy('id')->get()->toArray(),
            'buildings' => Building::with('variants')->orderBy('name')->get()->toArray(),
            'raw_materials' => config('raw_materials'),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($this->option('stdout')) {
            $this->line($json);

            return self::SUCCESS;
        }

        $path = $this->option('path') ?: 'game_data_export.json';
        Storage::put($path, $json);
        $this->info("Game data exported to storage/app/{$path}");

        return self::SUCCESS;
    }
}
