<?php

namespace Tests\Unit;

use App\Favorites\Facades\Favorites;
use App\Production\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * V70 / B46: the production result cache must store only a bounded plain-array
 * payload (never the live ProductionCalculator/Step graph or Eloquent models),
 * under a finite TTL and a whitelisted key — so a cache hit cannot unserialize
 * a giant object graph and exhaust the 128 MB PHP memory limit.
 */
class ProductionCachePayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Parallel CI workers share the redis test prefix and flush it in their
        // own setUp, which can evict this test's entries between a write and a
        // read-back. Use the in-memory array store (per-process, isolated) — the
        // plain-array guarantee under test is store-agnostic.
        config()->set('cache.default', 'array');
        Cache::flush();
    }

    #[Test]
    public function cached_payload_is_a_plain_array_with_no_objects(): void
    {
        $payload = ProductionCalculator::cachedPayload(product: 'Computer', qty: 10);

        $this->assertIsArray($payload);
        $this->assertNoObjects($payload);

        // The cached entry itself must be plain — no live object graph (V70).
        $cached = Cache::get($this->keyFor('Computer', 10));
        $this->assertIsArray($cached);
        $this->assertNoObjects($cached);
    }

    #[Test]
    public function make_no_longer_caches_the_live_object(): void
    {
        // make() returns the live object for callers but must NOT persist it to
        // the cache (V70/B46 — forever-cached object graphs were the memory bomb).
        ProductionCalculator::make(product: 'Computer', qty: 10);

        $this->assertNull(
            Cache::get($this->keyFor('Computer', 10)),
            'make() must not populate the production cache with an object graph'
        );
    }

    #[Test]
    public function irrelevant_request_params_do_not_mint_new_cache_entries(): void
    {
        // factory id is not a computation input. The old md5(request()->all())
        // key spawned a new never-evicted entry per distinct param (B46); the
        // whitelisted key must ignore it.
        request()->merge(['factory' => 1]);
        $a = ProductionCalculator::cachedPayload(product: 'Computer', qty: 10);
        $keyA = $this->keyFor('Computer', 10);

        request()->merge(['factory' => 999, 'foo' => 'bar']);
        $b = ProductionCalculator::cachedPayload(product: 'Computer', qty: 10);
        $keyB = $this->keyFor('Computer', 10);

        $this->assertSame($keyA, $keyB);
        $this->assertEquals($a, $b);
    }

    #[Test]
    public function cache_hit_does_not_balloon_memory(): void
    {
        ProductionCalculator::cachedPayload(product: 'Computer', qty: 10); // prime

        $before = memory_get_usage(true);
        ProductionCalculator::cachedPayload(product: 'Computer', qty: 10); // hit
        $delta = memory_get_usage(true) - $before;

        // A plain-array cache hit must not unserialize a giant object graph.
        $this->assertLessThan(32 * 1024 * 1024, $delta, 'cache hit ballooned memory');
        $this->assertLessThan(128 * 1024 * 1024, memory_get_peak_usage(true), 'peak memory exceeds the 128 MB limit');
    }

    #[Test]
    public function stale_object_cache_entry_is_recomputed_not_returned(): void
    {
        // V108/B60: a cache entry holding anything but an array (e.g. a stale
        // object graph) must be recomputed and overwritten, never returned —
        // returning it fatals on cachedPayload()'s array return type.
        Cache::forever($this->keyFor('Computer', 10), new \stdClass);

        $payload = ProductionCalculator::cachedPayload(product: 'Computer', qty: 10);

        $this->assertIsArray($payload);
        $this->assertIsArray(Cache::get($this->keyFor('Computer', 10)));
    }

    #[Test]
    public function versioned_key_prefix_never_collides_with_legacy_object_entries(): void
    {
        // V108/B60: pre-V70 code rememberForever'd the live calculator under
        // 'production_calc_<md5(params)><md5(request()->all())>'. When the
        // request holds only whitelisted params, that legacy key is identical
        // to the V70 key except for the version segment — without versioning,
        // a cache hit returned the object and fataled in production.
        $favoritesKey = Favorites::all()->pluck('id')->sort()->values()->all();
        $paramsHash = md5(collect(['product' => 'Computer', 'qty' => 10])
            ->put('recipe', null)
            ->put('overrides', [])
            ->put('imports', [])
            ->put('variant', 'mk1')
            ->put('choices', [])
            ->put('byproducts', [])
            ->put('favorites', $favoritesKey)
            ->toJson());
        $legacyKey = 'production_calc_'.$paramsHash.md5(collect(request()->all())->toJson());

        Cache::forever($legacyKey, new \stdClass);

        $payload = ProductionCalculator::cachedPayload(product: 'Computer', qty: 10);

        $this->assertIsArray($payload);
        $this->assertNotSame($legacyKey, $this->keyFor('Computer', 10));
    }

    private function keyFor($product, $qty): string
    {
        $favoritesKey = Favorites::all()->pluck('id')->sort()->values()->all();

        return 'production_calc_v2_'
            .md5(collect(compact('product', 'qty'))
                ->put('recipe', null)
                ->put('overrides', [])
                ->put('imports', [])
                ->put('variant', 'mk1')
                ->put('choices', [])
                ->put('byproducts', [])
                ->put('favorites', $favoritesKey)
                ->toJson())
            .ProductionCalculator::requestCacheSegment();
    }

    private function assertNoObjects($value, string $path = 'root'): void
    {
        if (is_object($value)) {
            $this->fail("Cached payload contains an object at {$path}: ".$value::class);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $this->assertNoObjects($item, "{$path}.{$key}");
            }
        }
    }
}
