<?php

namespace Tests\Unit;


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
