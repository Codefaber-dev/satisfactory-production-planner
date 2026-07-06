<?php

namespace Tests\Feature;

use App\Models\MultiProductionLine;
use App\Models\ProductionLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T110 / V66 / V67: recycling wired into the plan + auto_package_recycle persistence.
 *
 * The plan payload carries a `recycling` block (points/min + recycled/packaged/waste
 * from RecyclingCalc), and the `auto_package_recycle` toggle round-trips through the
 * request and persists to the saved factory.
 */
class RecyclingPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function show_payload_includes_a_recycling_block(): void
    {
        // V66: the plan surfaces recycled points/min + recycled/packaged/waste breakdown.
        $response = $this->get('/dashboard/Plastic/20/Plastic');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('production.recycling.points')
            ->has('production.recycling.recycled')
            ->has('production.recycling.packaged')
            ->has('production.recycling.waste')
            ->where('auto_package_recycle', false)
            ->etc()
        );
    }

    #[Test]
    public function unused_sinkable_byproduct_is_recycled_end_to_end(): void
    {
        // B52: an Alumina Solution plan emits a Silica byproduct (sink_points 20) that
        // nothing consumes → recycled. Proves leftover is computed from the real nested
        // getByproductsUsed shape (produced − Σ consumers), not a bad scalar cast.
        $response = $this->get('/dashboard/Alumina Solution/120/Alumina Solution');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('production.recycling.recycled.Silica.qty', fn ($qty) => $qty > 0)
            ->where('production.recycling.points', fn ($points) => $points > 0)
            ->etc()
        );
    }

    #[Test]
    public function toggle_on_packages_a_leftover_fluid_byproduct_end_to_end(): void
    {
        // V67: a Plastic plan leaves unused Heavy Oil Residue (fluid). With the toggle
        // on it is packaged (Packaged Heavy Oil Residue = 180 pts) and recycled.
        $off = $this->get('/dashboard/Plastic/60/Plastic');
        $off->assertInertia(fn ($page) => $page->where('production.recycling.packaged', [])->etc());

        $on = $this->get('/dashboard/Plastic/60/Plastic?auto_package_recycle=1');
        $on->assertInertia(fn ($page) => $page
            ->where('production.recycling.packaged', fn ($packaged) => count($packaged) === 1
                && $packaged[0]['fluid'] === 'Heavy Oil Residue'
                && $packaged[0]['product'] === 'Packaged Heavy Oil Residue')
            ->where('production.recycling.points', fn ($points) => $points > 0)
            ->etc()
        );
    }

    #[Test]
    public function auto_package_recycle_prop_reflects_the_request(): void
    {
        // V67: toggle round-trips via the request param.
        $response = $this->get('/dashboard/Plastic/20/Plastic?auto_package_recycle=1');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('auto_package_recycle', true)->etc());
    }

    #[Test]
    public function single_factory_save_persists_auto_package_recycle(): void
    {
        // V67: persists to the saved factory + restored via the Plan URL.
        $this->actingAsUser()->post('/factories', [
            'name' => 'Recycle Test',
            'ingredient_id' => i('Iron Plate')->id,
            'recipe_id' => r('Iron Plate')->id,
            'yield' => 30,
            'auto_package_recycle' => true,
        ])->assertRedirect();

        $line = ProductionLine::where('name', 'Recycle Test')->firstOrFail();

        $this->assertTrue($line->auto_package_recycle);
        $this->assertStringContainsString('auto_package_recycle=1', $line->getPlanUrl());
    }

    #[Test]
    public function multi_factory_save_persists_auto_package_recycle(): void
    {
        $this->actingAsUser()->post('/factories/multi', [
            'name' => 'Multi Recycle Test',
            'outputs' => [
                ['product' => ['id' => i('Iron Plate')->id, 'name' => 'Iron Plate'], 'yield' => 30, 'recipe' => ['id' => r('Iron Plate')->id, 'description' => null]],
            ],
            'auto_package_recycle' => true,
        ])->assertRedirect();

        $line = MultiProductionLine::where('name', 'Multi Recycle Test')->firstOrFail();

        $this->assertTrue($line->auto_package_recycle);
    }

    #[Test]
    public function default_auto_package_recycle_is_false(): void
    {
        // V67: default OFF.
        $this->actingAsUser()->post('/factories', [
            'name' => 'Default Recycle Test',
            'ingredient_id' => i('Iron Plate')->id,
            'recipe_id' => r('Iron Plate')->id,
            'yield' => 30,
        ])->assertRedirect();

        $line = ProductionLine::where('name', 'Default Recycle Test')->firstOrFail();

        $this->assertFalse($line->auto_package_recycle);
    }
}
