<?php

namespace Tests\Unit;

use App\Favorites\Facades\Favorites;
use App\Production\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeProductionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function it_can_set_the_parameters()
    {
        $product = 'Iron Ingot';
        $qty = 100;

        $production = ProductionCalculator::make($product, $qty);

        $this->assertEquals($product, $production->getSteps()->getName());
        $this->assertEquals($qty, $production->getSteps()->getQty());
    }

    #[Test]
    public function it_can_create_the_initial_step_calculation()
    {
        $product = 'Iron Ingot';
        $qty = 100;

        $production = ProductionCalculator::make($product, $qty);

        $this->assertEquals($product, $production->get('2.Iron Ingot.production.0.name'));
        $this->assertEquals($qty, $production->get('2.Iron Ingot.production.0.qty'));
    }

    #[Test]
    #[DataProvider('rawIngredientsData')]
    public function it_can_calculate_the_raw_ingredients_needed($product, $qty, $key, $expectedQty)
    {
        $production = ProductionCalculator::make($product, $qty);

        $this->assertEqualsWithDelta($expectedQty, $production->get($key), 0.001);
    }

    #[Test]
    public function it_can_set_favorites()
    {
        Favorites::set(i('Screw'), r('Steel Screw'));

        $production = ProductionCalculator::make(
            product: 'Computer',
            qty: 10
        );

        $production->getSteps()->assertIntermediateRecipe('Screw', 'Steel Screw');
    }

    #[Test]
    public function it_can_handle_circular_dependencies()
    {
        // V58: with both recycled recipes selected, the Plastic⇄Rubber loop is
        // solved as a linear system (both recipes kept), not broken by forcing
        // Plastic to its base recipe. 100 net Rubber needs 133.33 gross Rubber +
        // 66.67 gross Plastic (the surplus is consumed internally by the loop).
        $production = ProductionCalculator::make(
            product: 'Rubber',
            qty: 100,
            recipe: 'Recycled Rubber',
            overrides: [],
            favorites: [
                'Plastic' => r('Recycled Plastic'),
            ]
        );

        $this->assertEqualsWithDelta(133.3333, $production->get('3.Rubber.total'), 0.001);
        $this->assertEqualsWithDelta(66.6667, $production->get('3.Plastic.total'), 0.001);
        $this->assertEqualsWithDelta(100, $production->get('2.Fuel.total'), 0.001);
        $this->assertEqualsWithDelta(150, $production->get('1.Crude Oil.total'), 0.001);
    }

    #[Test]
    public function it_can_calculate_fused_quickwire()
    {
        $production = ProductionCalculator::make(
            product: 'Quickwire',
            qty: 1740,
            recipe: 'Fused Quickwire',
            overrides: [],
            favorites: [],
            imports: []
        );

        $this->assertEquals(145, $production->get('2.Caterium Ingot.total'));
        $this->assertEquals(725, $production->get('2.Copper Ingot.total'));
        $this->assertEquals(1740, $production->get('3.Quickwire.total'));
    }

    #[Test]
    public function it_can_handle_the_caterium_computer_issue()
    {
        $production = ProductionCalculator::make(
            product: 'Computer',
            qty: 30,
            recipe: 'Caterium Computer',
            overrides: [],
            favorites: [
                'Circuit Board' => r('Caterium Circuit Board'),
                'Plastic' => r('Recycled Plastic'),
                'Rubber' => r('Recycled Rubber'),
                'Fuel' => r('Unpackage Fuel'),
                'Packaged Fuel' => r('Packaged Fuel'),
                'Quickwire' => r('Fused Quickwire'),
            ],
            imports: [
                'Caterium Ingot',
                'Copper Ingot',
            ]

        );

        $steps = $production->getSteps();

        // V58/B43 hybrid: the Plastic⇄Rubber loop is SOLVED (both recipes kept, no
        // forced override), while the degenerate 1:1 Fuel⇄Packaged Fuel loop is
        // singular/unsolvable and falls back to the forced Diluted Packaged Fuel.
        $steps->assertImported('Caterium Ingot');
        $steps->assertImported('Copper Ingot');
        $steps->assertOverride('Packaged Fuel', 'Diluted Packaged Fuel');
        $steps->assertIntermediateRecipe('Plastic', 'Recycled Plastic');
        $steps->assertIntermediateRecipe('Rubber', 'Recycled Rubber');
        $steps->assertIntermediateRecipe('Quickwire', 'Fused Quickwire');

        // no loops left unsolved (the degenerate one is handled by the fallback)
        $this->assertSame([], $production->getLoopWarnings());

        $this->assertNull($production->get('1.Caterium Ore.total'));
        $this->assertNull($production->get('1.Copper Ore.total'));
        $this->assertEquals(30, $production->get('5.Computer.total'));

        $this->assertEqualsWithDelta(327.857, $production->get('1.Crude Oil.total'), 0.001);
        $this->assertEqualsWithDelta(177.143, $production->get('1.Water.total'), 0.001);
        $this->assertEqualsWithDelta(77.857, $production->get('2.Caterium Ingot.total'), 0.001);
        $this->assertEqualsWithDelta(389.286, $production->get('2.Copper Ingot.total'), 0.001);
        $this->assertEqualsWithDelta(88.571, $production->get('2.Heavy Oil Residue.total'), 0.001);
        // Plastic⇄Rubber solved to gross (both recipes kept)
        $this->assertEqualsWithDelta(348.571, $production->get('3.Plastic.total'), 0.001);
        $this->assertEqualsWithDelta(354.286, $production->get('3.Rubber.total'), 0.001);
        $this->assertEqualsWithDelta(934.286, $production->get('3.Quickwire.total'), 0.001);
        $this->assertEquals(120, $production->get('4.Circuit Board.total'));
    }

    #[Test]
    public function importing_a_looped_product_suppresses_the_forced_override()
    {
        // B44: importing Packaged Fuel breaks the degenerate Fuel⇄Packaged Fuel loop,
        // so the forced Diluted Packaged Fuel override must NOT fire — otherwise the
        // import is ignored and the "Circular Dependencies Found" banner shows wrongly.
        $production = ProductionCalculator::make(
            product: 'Plastic',
            qty: 60,
            recipe: 'Recycled Plastic',
            overrides: [],
            favorites: [
                'Fuel' => r('Unpackage Fuel'),
                'Rubber' => r('Rubber'),
            ],
            imports: ['Packaged Fuel'],
        );

        $this->assertSame([], $production->getSteps()->getOverrides()->keys()->all());

        // sanity: without the import, the fallback override still fires
        $forced = ProductionCalculator::make(
            product: 'Plastic',
            qty: 60,
            recipe: 'Recycled Plastic',
            overrides: [],
            favorites: [
                'Fuel' => r('Unpackage Fuel'),
                'Rubber' => r('Rubber'),
            ],
            imports: [],
        );

        $this->assertContains('Packaged Fuel', $forced->getSteps()->getOverrides()->keys()->all());
    }

    public static function rawIngredientsData()
    {
        return [
            // Iron Ingot
            ['Iron Ingot', 100, '2.Iron Ingot.total', 100],
            ['Iron Ingot', 100, '1.Iron Ore.total', 100],
            // Iron Plate
            ['Iron Plate', 100, '3.Iron Plate.total', 100],
            ['Iron Plate', 100, '2.Iron Ingot.total', 150],
            ['Iron Plate', 100, '1.Iron Ore.total', 150],
            // Iron Rod
            ['Iron Rod', 100, '3.Iron Rod.total', 100],
            ['Iron Rod', 100, '2.Iron Ingot.total', 100],
            ['Iron Rod', 100, '1.Iron Ore.total', 100],
            // Screw
            ['Screw', 100, '4.Screw.total', 100],
            ['Screw', 100, '3.Iron Rod.total', 25],
            ['Screw', 100, '2.Iron Ingot.total', 25],
            ['Screw', 100, '1.Iron Ore.total', 25],
            // Rein Iron Plate
            ['Reinforced Iron Plate', 10, '5.Reinforced Iron Plate.total', 10],
            ['Reinforced Iron Plate', 10, '4.Screw.total', 120],
            ['Reinforced Iron Plate', 10, '3.Iron Rod.total', 30],
            ['Reinforced Iron Plate', 10, '3.Iron Plate.total', 60],
            ['Reinforced Iron Plate', 10, '2.Iron Ingot.total', 120],
            ['Reinforced Iron Plate', 10, '1.Iron Ore.total', 120],
            // Modular Frame
            ['Modular Frame', 10, '6.Modular Frame.total', 10],
            ['Modular Frame', 10, '5.Reinforced Iron Plate.total', 15],
            ['Modular Frame', 10, '4.Screw.total', 180],
            ['Modular Frame', 10, '3.Iron Rod.total', 105],
            ['Modular Frame', 10, '3.Iron Plate.total', 90],
            ['Modular Frame', 10, '2.Iron Ingot.total', 240],
            ['Modular Frame', 10, '1.Iron Ore.total', 240],
            // Heavy Modular Frame
            ['Heavy Modular Frame', 5, '7.Heavy Modular Frame.total', 5],
            ['Heavy Modular Frame', 5, '6.Modular Frame.total', 25],
            ['Heavy Modular Frame', 5, '5.Reinforced Iron Plate.total', 37.5],
            ['Heavy Modular Frame', 5, '4.Screw.total', 1050],
            ['Heavy Modular Frame', 5, '4.Encased Industrial Beam.total', 25],
            ['Heavy Modular Frame', 5, '3.Iron Rod.total', 412.5],
            ['Heavy Modular Frame', 5, '3.Steel Pipe.total', 100],
            ['Heavy Modular Frame', 5, '3.Iron Plate.total', 225],
            ['Heavy Modular Frame', 5, '2.Iron Ingot.total', 750],
            ['Heavy Modular Frame', 5, '2.Concrete.total', 150],
            ['Heavy Modular Frame', 5, '2.Steel Ingot.total', 450],
            ['Heavy Modular Frame', 5, '1.Iron Ore.total', 1200],
            ['Heavy Modular Frame', 5, '1.Limestone.total', 450],
            ['Heavy Modular Frame', 5, '1.Coal.total', 450],
        ];
    }
}
