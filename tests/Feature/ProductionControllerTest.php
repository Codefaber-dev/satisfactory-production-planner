<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function index_renders_production_page_for_guest(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Production/Index'));
    }

    #[Test]
    public function index_renders_production_page_for_auth_user(): void
    {
        $response = $this->actingAsUser()->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Production/Index'));
    }

    #[Test]
    public function show_returns_200_for_guest_with_valid_product(): void
    {
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Production/Show'));
    }

    #[Test]
    public function show_returns_200_for_auth_user_with_valid_product(): void
    {
        $response = $this->actingAsUser()->get('/dashboard/Iron Plate/30/Iron Plate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Production/Show'));
    }

    #[Test]
    public function show_returns_404_for_invalid_ingredient(): void
    {
        $response = $this->get('/dashboard/NonExistentItem12345/10/NonExistentItem12345');

        $response->assertStatus(404);
    }

    #[Test]
    public function show_passes_product_and_yield_to_view(): void
    {
        $response = $this->get('/dashboard/Iron Plate/60/Iron Plate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Production/Show')
            ->has('product')
            ->has('production')
            ->where('yield', '60')
        );
    }

    #[Test]
    public function show_accepts_variant_parameter(): void
    {
        $response = $this->get('/dashboard/Iron Plate/30/Iron Plate/mk1');

        $response->assertStatus(200);
    }
}
