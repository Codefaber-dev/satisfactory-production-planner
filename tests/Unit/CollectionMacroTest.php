<?php

namespace Tests\Unit;

use App\CollectionMacro;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CollectionMacroTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_can_cross_sum()
    {
        $a = collect([
            [
                1,2,3,4
            ],
            [
                5,6,7,8
            ]
        ]);

        $this->assertEquals([6,8,10,12], $a->crossSum());
    }

    /** @test */
    public function it_can_cross_sum_by_key()
    {
        $a = collect([
            [
                "val" => [1,2,3,4]
            ],
            [
                "val" => [5,6,7,8]
            ]
        ]);

       $this->assertEquals([6,8,10,12], $a->crossSumByKey('val'));
    }

    /** @test */
    public function it_can_cross_sum_by_key2()
    {
        $a = collect([
            [
                "val" => [1,2,3,4]
            ],
        ]);

       $this->assertEquals([1,2,3,4], $a->crossSumByKey('val'));
    }
}
