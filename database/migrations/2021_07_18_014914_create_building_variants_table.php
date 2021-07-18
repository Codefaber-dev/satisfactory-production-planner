<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('base_power');
            $table->unsignedInteger('multiplier')->default(1);
            $table->timestamps();

            $table->unique(['name','building_id']);
        });

        Schema::create('building_variant_ingredient', function(Blueprint $table) {
           $table->foreignId('building_variant_id')->constrained()->cascadeOnDelete();
           $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
           $table->unsignedInteger('qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_variants');
    }
}
