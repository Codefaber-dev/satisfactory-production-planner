<?php

namespace Tests\Unit;

use App\Favorites\Facades\Favorites;
use App\Production\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionCacheIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
    }

    #[Test]
    public function favorites_do_not_contaminate_cache_across_users()
    {
        // User A sets a non-default favorite then computes production
        Favorites::set(i('Screw'), r('Steel Screw'));
        $productionA = ProductionCalculator::make(product: 'Computer', qty: 10);
        $productionA->getSteps()->assertIntermediateRecipe('Screw', 'Steel Screw');

        // User B resets to default — do NOT flush cache; test that B gets own entry
        Favorites::setDefault(i('Screw'));
        $productionB = ProductionCalculator::make(product: 'Computer', qty: 10);

        // B must get the default Screw recipe, not A's Steel Screw from a shared cache entry
        $productionB->getSteps()->assertIntermediateRecipe('Screw', 'Screw');
    }

    #[Test]
    public function explicit_favorites_parameter_is_used_for_cache_key()
    {
        // Passing explicit favorites overrides session favorites in cache key
        $steelScrew = r('Steel Screw');

        $productionExplicit = ProductionCalculator::make(
            product: 'Computer',
            qty: 10,
            favorites: ['Screw' => $steelScrew],
        );
        $productionExplicit->getSteps()->assertIntermediateRecipe('Screw', 'Steel Screw');

        // No session favorite set; no explicit favorites — should get default Screw
        $productionDefault = ProductionCalculator::make(product: 'Computer', qty: 10);
        $productionDefault->getSteps()->assertIntermediateRecipe('Screw', 'Screw');
    }
}
