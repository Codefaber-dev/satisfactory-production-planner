<?php

namespace Tests\Feature;

use App\Production\Multiplexer;
use App\Production\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * V74: byproduct recalculation iterates to a steady state with an early exit
 * (cap 3) instead of a fixed 3× pass. Convergence means one more pass changes
 * nothing — outputs stay byte-identical to the fixed-pass baseline (the
 * production canary in the suite is the acyclic/loop-plan proof).
 */
class ByproductConvergenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        config()->set('cache.default', 'array');
        Cache::flush();
    }

    #[Test]
    public function converged_single_plan_is_idempotent_under_extra_passes(): void
    {
        // Aluminum chain recycles its water byproduct — byproduct passes engage.
        $calc = ProductionCalculator::make('Aluminum Ingot', 60);

        $this->assertNotEmpty($calc->getByproductsUsed()->all(), 'plan should exercise byproduct recycling');

        $before = json_encode($calc->toPayload());
        $stateBefore = $calc->byproductState();

        // One extra pass beyond make() must change nothing once converged.
        $calc->recalculateUsingByproducts();

        $this->assertSame($stateBefore, $calc->byproductState());
        $this->assertSame($before, json_encode($calc->toPayload()));
    }

    #[Test]
    public function multiplexer_until_stable_converges_within_cap_and_is_idempotent(): void
    {
        $m = new Multiplexer;
        $m->add(ProductionCalculator::make('Aluminum Ingot', 60));
        $m->add(ProductionCalculator::make('Iron Plate', 30));

        $passes = $m->recalculateUsingByproductsUntilStable();

        $this->assertLessThanOrEqual(3, $passes);
        $this->assertGreaterThanOrEqual(1, $passes);

        $stateBefore = $m->byproductState();
        $resultsBefore = json_encode($m->getResults());

        // Converged: one more manual pass is a no-op.
        $m->recalculateUsingByproducts();

        $this->assertSame($stateBefore, $m->byproductState());
        $this->assertSame($resultsBefore, json_encode($m->getResults()));
    }
}
