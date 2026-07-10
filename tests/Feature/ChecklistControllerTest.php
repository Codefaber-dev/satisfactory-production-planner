<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChecklistControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase does not flush the cache — a prior test's ingredient
        // list would otherwise leak through checklist_ingredients (cf B60 class)
        Cache::forget('checklist_ingredients');
    }

    #[Test]
    public function checklist_page_carries_the_full_ingredient_set(): void
    {
        Ingredient::factory()->count(5)->create();

        $response = $this->get(route('checklist'));

        $response->assertOk()->assertInertia(
            fn (Assert $page) => $page
                ->component('Checklist/Index')
                ->has('ingredients', Ingredient::count())
        );
    }

    #[Test]
    public function ingredient_rows_carry_the_fields_the_store_merge_needs(): void
    {
        Ingredient::factory()->create([
            'name' => 'Reanimated SAM',
            'tier' => 5,
            'raw' => false,
            'is_liquid' => false,
            'is_ficsmas' => false,
        ]);

        $response = $this->get(route('checklist'));

        $response->assertInertia(
            fn (Assert $page) => $page->has(
                'ingredients.0',
                fn (Assert $row) => $row
                    ->where('name', 'Reanimated SAM')
                    ->where('tier', 5)
                    ->has('raw')
                    ->has('is_liquid')
                    ->has('is_ficsmas')
            )
        );
    }
}
