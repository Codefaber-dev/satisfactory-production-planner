<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductionLinesAddNotesAndImportsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production_lines', function (Blueprint $table) {
            $table->text('notes')->nullable();
            $table->text('imports')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_lines', function (Blueprint $table) {
            $table->dropColumn('notes');
            $table->dropColumn('imports');
        });
    }
}
