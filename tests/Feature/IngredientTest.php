<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_a_list_of_the_ingredients()
    {
        $ingredients = Ingredient::factory()->count(3)->create();

        $response = $this
            ->actingAsUser()
            ->get(route('ingredients.index'));

        $response->assertJsonCount(3)
            ->assertJsonFragment($ingredients->first()->toArray());
    }

    /** @test */
    public function it_can_get_a_single_ingredient()
    {
        $ingredients = Ingredient::factory()->count(3)->create();

        $response = $this
            ->actingAsUser()
            ->get(route('ingredients.show',$ingredients->first()));

        $response
            ->assertJsonFragment(['name' => $ingredients->first()->name]);
    }

    /** @test */
    public function it_can_store_a_new_ingredient()
    {
        $atts = Ingredient::factory()->make()->toArray();

        $response = $this
            ->actingAsUser()
            ->post(route('ingredients.store'), $atts);

        $response->assertCreated();

        $this->assertDatabaseHas('ingredients',$atts);
    }

    /** @test */
    public function it_can_update_an_ingredient()
    {
        $ingredient = Ingredient::factory()->create();

        $newAtts = [
            'name' => 'New Name',
            'raw' => false,
            'tier' => 6
        ];

        $response = $this
            ->actingAsUser()
            ->patch(route('ingredients.update',$ingredient), $newAtts);

        $response->assertStatus(202);

        $this->assertDatabaseHas('ingredients',$newAtts);
    }

    /** @test */
    public function it_can_deleted_an_ingredient()
    {
        $ingredient = Ingredient::factory()->create();

        $response = $this
            ->actingAsUser()
            ->delete(route('ingredients.destroy',$ingredient));

        $response->assertStatus(202);

        $this->assertDeleted($ingredient);
    }
}
