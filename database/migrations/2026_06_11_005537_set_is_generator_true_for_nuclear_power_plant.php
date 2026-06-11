<?php

use App\Models\Building;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $building = Building::where('name', 'Nuclear Power Plant')->first();

        if ($building) {
            $building->variants()->update(['is_generator' => true]);
        }
    }

    public function down(): void
    {
        $building = Building::where('name', 'Nuclear Power Plant')->first();

        if ($building) {
            $building->variants()->update(['is_generator' => false]);
        }
    }
};
