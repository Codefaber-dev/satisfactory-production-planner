<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_lines', function (Blueprint $table) {
            // V67: auto-package-and-recycle excess fluids toggle (default OFF).
            $table->boolean('auto_package_recycle')->default(false);
        });

        Schema::table('multi_production_lines', function (Blueprint $table) {
            $table->boolean('auto_package_recycle')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('production_lines', function (Blueprint $table) {
            $table->dropColumn('auto_package_recycle');
        });

        Schema::table('multi_production_lines', function (Blueprint $table) {
            $table->dropColumn('auto_package_recycle');
        });
    }
};
