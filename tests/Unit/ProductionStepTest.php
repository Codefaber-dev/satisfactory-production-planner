<?php

namespace Tests\Unit;

use App\Favorites\Facades\Favorites;
use App\Production\Step;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionStepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function it_has_getters()
    {
        $step = Step::make(
            product: 'Heavy Modular Frame',
            qty: 5,
            globals: [
                'overrides' => [],
                'favorites' => [],
            ]
        );

        $this->assertEquals('Heavy Modular Frame', $step->getName());
        $this->assertTrue($step->getProduct()->is(i('Heavy Modular Frame')));
        $this->assertTrue($step->getRecipe()->is(r('Heavy Modular Frame')));
        $this->assertEquals('default', $step->getDescription());
        $this->assertEquals(5, $step->getQty());
        $this->assertNull($step->getParent());
        $this->assertCount(4, $step->getChildren());
        $this->assertEquals(7, $step->getTier());
        $this->assertCount(0, $step->getOverrides());
        $this->assertCount(1, $step->getChain());
        $this->assertCount(0, $step->getFavorites());
        $this->assertCount(4, $step->getIngredients());
        $this->assertCount(0, $step->getByproducts());
    }

    #[Test]
    public function it_can_specify_a_recipe()
    {
        $step = Step::make(
            product: 'Heavy Modular Frame',
            qty: 5,
            recipe: 'Heavy Encased Frame',
            globals: [
                'overrides' => [],
                'favorites' => [],
            ]
        );

        $step->assertRecipe('Heavy Encased Frame');
        $this->assertEquals('Heavy Encased Frame', $step->getDescription());
    }

    #[Test]
    public function it_can_specify_recipe_favorites()
    {
        $step = Step::make(
            product: 'Heavy Modular Frame',
            qty: 5,
            recipe: 'Heavy Encased Frame',
            globals: [
                'overrides' => [],
                'favorites' => [
                    'Screw' => r('Steel Screw'),
                ],
            ]
        );

        $step->assertIntermediateRecipe('Screw', 'Steel Screw');

    }

    #[Test]
    public function it_can_use_recipe_favorites()
    {
        Favorites::set(i('Screw'), r('Steel Screw'));

        $step = Step::make(
            product: 'Heavy Modular Frame',
            qty: 5,
            recipe: 'Heavy Encased Frame',
            globals: [
                'overrides' => [],
            ]
        );

        $step->assertIntermediateRecipe('Screw', 'Steel Screw');

    }

    #[Test]
    public function it_can_import_things()
    {
        $step = Step::make(
            product: 'Heavy Modular Frame',
            qty: 5,
            recipe: 'Heavy Encased Frame',
            globals: [
                'overrides' => [],
                'favorites' => [
                    'Screw' => r('Steel Screw'),
                ],
                'imports' => [
                    'Screw',
                ],
            ]
        );

        $step->assertImported('Screw');
    }

    #[Test]
    public function it_can_specify_recipe_overrides()
    {
        $step = Step::make(
            product: 'Heavy Modular Frame',
            qty: 5,
            recipe: 'Heavy Encased Frame',
            globals: [
                'overrides' => [
                    'Screw' => r('Steel Screw'),
                ],
                'favorites' => [],
                'imports' => [
                    'Screw',
                ],
            ]
        );

        $step->assertIntermediateRecipe('Screw', 'Steel Screw');
        $step->assertOverride('Screw', 'Steel Screw');
    }

    #[Test]
    public function it_can_override_a_favorite()
    {
        $step = Step::make(
            product: 'Heavy Modular Frame',
            qty: 5,
            recipe: 'Heavy Encased Frame',
            globals: [
                'overrides' => [
                    'Screw' => r('Cast Screw'),
                ],
                'favorites' => [
                    'Screw' => r('Steel Screw'),
                ],
            ]
        );

        $step->assertIntermediateRecipe('Screw', 'Cast Screw');
    }

    #[Test]
    public function it_can_resolve_a_circular_dependency()
    {
        $step = Step::make(
            product: 'Rubber',
            qty: 5,
            recipe: 'Recycled Rubber',
            globals: [
                'overrides' => [],
                'favorites' => [
                    'Plastic' => r('Recycled Plastic'),
                ],
            ]
        );

        $step->assertOverride('Plastic', 'Plastic');
        $step->assertRecipe('Recycled Rubber');

        $step = Step::make(
            product: 'Plastic',
            qty: 5,
            recipe: 'Recycled Plastic',
            globals: [
                'overrides' => [],
                'favorites' => [
                    'Plastic' => r('Recycled Rubber'),
                ],
            ]
        );

        $step->assertOverride('Rubber', 'Rubber');
        $step->assertRecipe('Recycled Plastic');

        $step = Step::make(
            product: 'Fuel',
            qty: 5,
            recipe: 'Unpackage Fuel',
            globals: [
                'overrides' => [],
                'favorites' => [
                    'Packaged Fuel' => r('Packaged Fuel'),
                ],
            ]
        );

        $step->assertOverride('Packaged Fuel', 'Diluted Packaged Fuel');
        $step->assertRecipe('Unpackage Fuel');
    }

    #[Test]
    public function somersloops_reduce_upstream_ingredient_demand(): void
    {
        // Smelter max=1 slot → amplifier 2× at full. Iron Ingot recipe: 30 ore → 30 ingots/min.
        // For qty=60 ingots: without slots sub_qty=60 ore; with 1 slot sub_qty=30 ore.
        $qty = 60;

        $stepBase = Step::make(
            product: 'Iron Ingot',
            qty: $qty,
            globals: ['overrides' => [], 'favorites' => []],
        );
        $baseOreQty = collect($stepBase->getIngredients())->get('Iron Ore');

        // Frontend keys default recipes as "name|name" (recipe.description || product.name),
        // never "name|default" — backend lookup must match that convention.
        request()->merge(['somersloops' => ['Iron Ingot|Iron Ingot' => '1']]);

        $stepSlot = Step::make(
            product: 'Iron Ingot',
            qty: $qty,
            globals: ['overrides' => [], 'favorites' => []],
        );
        $slotOreQty = collect($stepSlot->getIngredients())->get('Iron Ore');

        $this->assertEqualsWithDelta(60.0, $baseOreQty, 0.01, 'Without somersloops: 60 ore/min expected');
        $this->assertEqualsWithDelta(30.0, $slotOreQty, 0.01, 'With 1 somersloop slot (2× amplifier): 30 ore/min expected');
    }
}
