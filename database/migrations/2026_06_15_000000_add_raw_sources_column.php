<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_lines', function (Blueprint $table) {
            $table->text('raw_sources')->nullable();
        });

        Schema::table('multi_production_lines', function (Blueprint $table) {
            $table->text('raw_sources')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('production_lines', function (Blueprint $table) {
            $table->dropColumn('raw_sources');
        });

        Schema::table('multi_production_lines', function (Blueprint $table) {
            $table->dropColumn('raw_sources');
        });
    }
};
