<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FlushProductionCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::store('redis')->flush();
    }

    protected function tearDown(): void
    {
        Cache::store('redis')->flush();
        parent::tearDown();
    }

    #[Test]
    public function command_exits_zero_with_no_matching_keys(): void
    {
        $this->artisan('flush-production-cache')->assertExitCode(0);
    }

    #[Test]
    public function flushes_production_calc_keys(): void
    {
        Cache::store('redis')->put('production_calc_abc', 'val1', 60);
        Cache::store('redis')->put('production_calc_xyz', 'val2', 60);
        Cache::store('redis')->put('unrelated_key', 'keep', 60);

        $this->artisan('flush-production-cache')->assertExitCode(0);

        $this->assertNull(Cache::store('redis')->get('production_calc_abc'));
        $this->assertNull(Cache::store('redis')->get('production_calc_xyz'));
        $this->assertEquals('keep', Cache::store('redis')->get('unrelated_key'));
    }

    #[Test]
    public function flushes_multi_production_keys(): void
    {
        Cache::store('redis')->put('multi_production_def', 'val', 60);

        $this->artisan('flush-production-cache')->assertExitCode(0);

        $this->assertNull(Cache::store('redis')->get('multi_production_def'));
    }

    #[Test]
    public function does_not_contaminate_default_redis_connection(): void
    {
        $defaultConn = Redis::connection();
        $defaultConn->set('test_db0_sentinel', 'exists_in_db0');

        $this->artisan('flush-production-cache')->assertExitCode(0);

        $this->assertEquals('exists_in_db0', $defaultConn->get('test_db0_sentinel'));

        $defaultConn->del('test_db0_sentinel');
    }
}
