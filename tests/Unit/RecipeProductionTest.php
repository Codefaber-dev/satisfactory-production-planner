<?php

namespace Tests\Unit;


use App\Favorites\Facades\Favorites;
use App\Production\ProductionCalculator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecipeProductionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default','mysql');
        config()->set('database.connections.mysql.database','satis_pp');
    }

    /**
     * @test
     */
    public function it_can_set_the_parameters()
    {
        $product = 'Iron Ingot';
        $qty = 100;

        $production = ProductionCalculator::make($product, $qty);

        $this->assertEquals($product, $production->getSteps()->getName());
        $this->assertEquals($qty, $production->getSteps()->getQty());
    }

    /** @test */
    public function it_can_create_the_initial_step_calculation()
    {
        $product = 'Iron Ingot';
        $qty = 100;

        $production = ProductionCalculator::make($product, $qty);

        $this->assertEquals($product, $production->get('2.Iron Ingot.production.0.name'));
        $this->assertEquals($qty, $production->get('2.Iron Ingot.production.0.qty'));
    }

    /**
     * @test
     * @dataProvider rawIngredientsData
     */
    public function it_can_calculate_the_raw_ingredients_needed($product, $qty, $key, $expectedQty)
    {
        $production = ProductionCalculator::make($product, $qty);


        $this->assertEquals($expectedQty, $production->get($key));
    }

    /** @test */
    public function it_can_set_favorites()
    {
        Favorites::set(i('Screw'),r('Steel Screw'));

        $production = ProductionCalculator::make(
            product: "Computer",
            qty: 10
        );

        $production->getSteps()->assertIntermediateRecipe('Screw','Steel Screw');
    }

    /** @test */
    public function it_can_handle_circular_dependencies()
    {
        $production = ProductionCalculator::make(
            product: "Rubber",
            qty:100,
            recipe: "Recycled Rubber",
            overrides: [],
            favorites: [
                'Plastic' => r('Recycled Plastic')
            ]
        );

        $this->assertEquals(100, $production->get('3.Rubber.total'));
        $this->assertEquals(50, $production->get('2.Plastic.total'));
        $this->assertEquals(50, $production->get('2.Fuel.total'));
        $this->assertEquals(150, $production->get('1.Crude Oil.total'));
    }

    /** @test */
    public function it_can_calculate_fused_quickwire()
    {
        $production = ProductionCalculator::make(
            product: "Quickwire",
            qty: 1740,
            recipe: "Fused Quickwire",
            overrides: [],
            favorites: [
                //'Circuit Board' => r('Caterium Circuit Board'),
                //'Plastic' => r('Recycled Plastic'),
                //'Rubber' => r('Recycled Rubber'),
                //'Fuel' => r('Unpackage Fuel'),
                //'Packaged Fuel' => r('Packaged Fuel'),
                //'Quickwire' => r('Fused Quickwire')
            ],
            imports: [
                //'Caterium Ingot',
                //'Copper Ingot'
            ]
        );

        //dd($production->getSlimResults());

        $this->assertEquals(145, $production->get('2.Caterium Ingot.total'));
        $this->assertEquals(725, $production->get('2.Copper Ingot.total'));
        $this->assertEquals(1740, $production->get('3.Quickwire.total'));
    }

    /** @test */
    public function it_can_handle_the_caterium_computer_issue()
    {
        $production = ProductionCalculator::make(
            product: "Computer",
            qty: 30,
            recipe: "Caterium Computer",
            overrides: [],
            favorites: [
                'Circuit Board' => r('Caterium Circuit Board'),
                'Plastic' => r('Recycled Plastic'),
                'Rubber' => r('Recycled Rubber'),
                'Fuel' => r('Unpackage Fuel'),
                'Packaged Fuel' => r('Packaged Fuel'),
                'Quickwire' => r('Fused Quickwire')
            ],
            imports: [
                'Caterium Ingot',
                'Copper Ingot'
            ]

        );

        $steps = $production->getSteps();

        $steps->assertImported('Caterium Ingot');
        $steps->assertImported('Copper Ingot');
        $steps->assertOverride('Rubber','Rubber');
        $steps->assertOverride('Packaged Fuel','Diluted Packaged Fuel');
        $steps->assertIntermediateRecipe('Plastic','Recycled Plastic');
        $steps->assertIntermediateRecipe('Quickwire','Fused Quickwire');


        //dd($production->getSlimResults());

        $this->assertNull($production->get('1.Caterium Ore.total'));
        $this->assertNull($production->get('1.Copper Ore.total'));

        $this->assertEquals(765, $production->get('1.Crude Oil.total'));
        $this->assertEquals(150, $production->get('1.Water.total'));
        $this->assertEquals(145, $production->get('2.Caterium Ingot.total'));
        $this->assertEquals(725, $production->get('2.Copper Ingot.total'));
        $this->assertEquals(0, $production->get('2.Heavy Oil Residue.total'));
        $this->assertEquals(510, $production->get('2.Rubber.total'));
        $this->assertEquals(0, $production->get('2.Plastic.total'));
        $this->assertEquals(1740, $production->get('3.Quickwire.total'));
        $this->assertEquals(300, $production->get('3.Plastic.total'));
        $this->assertEquals(0, $production->get('3.Empty Canister.total'));
        $this->assertEquals(150, $production->get('4.Packaged Water.total'));
        $this->assertEquals(210, $production->get('4.Circuit Board.total'));
        $this->assertEquals(150, $production->get('5.Fuel.total'));
        $this->assertEquals(150, $production->get('5.Packaged Fuel.total'));
        $this->assertEquals(30, $production->get('5.Computer.total'));
    }

    public function rawIngredientsData()
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
            ['Heavy Modular Frame', 5, '4.Screw.total', 950],
            ['Heavy Modular Frame', 5, '4.Encased Industrial Beam.total', 25],
            ['Heavy Modular Frame', 5, '3.Iron Rod.total', 387.5],
            ['Heavy Modular Frame', 5, '3.Steel Pipe.total', 75],
            ['Heavy Modular Frame', 5, '3.Iron Plate.total', 225],
            ['Heavy Modular Frame', 5, '2.Iron Ingot.total', 725],
            ['Heavy Modular Frame', 5, '2.Concrete.total', 125],
            ['Heavy Modular Frame', 5, '2.Steel Ingot.total', 512.5],
            ['Heavy Modular Frame', 5, '1.Iron Ore.total', 1237.5],
            ['Heavy Modular Frame', 5, '1.Limestone.total', 375],
            ['Heavy Modular Frame', 5, '1.Coal.total', 512.5],
        ];
    }
}
