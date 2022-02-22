<?php

namespace Tests\Unit;


use App\Production\Multiplexer;
use App\Production\ProductionCalculator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultiplexerTest extends TestCase
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
    public function it_()
    {
        $m = new Multiplexer;

        $m->add(ProductionCalculator::make(i('Iron Plate'), 100));
        $m->add(ProductionCalculator::make(i('Iron Rod'), 100));

    }
}
