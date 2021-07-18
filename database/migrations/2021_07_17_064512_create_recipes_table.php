<?php

use App\Models\Ingredient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('base_yield')->default(1);
            $table->decimal('base_per_min',8,2);
            $table->boolean('alt_recipe')->default(false);
            $table->string('description')->nullable();


            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('ingredients')->cascadeOnDelete();
        });

        Schema::create('ingredient_recipe', function(Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_qty',8,2);

            $table->primary(['recipe_id','ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipes');
    }
}
