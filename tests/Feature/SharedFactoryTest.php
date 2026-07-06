<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * B61: /shared/{hashId} with an undecodable or unknown hash must 404, not 500.
 * The old scopeOfHashId returned null from inside a scope, so callScope()'s
 * `?? $this` handed the controller a Builder and ->getPlanUrl() fataled.
 */
class SharedFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function garbage_production_line_hash_is_a_404(): void
    {
        $this->get('/shared/pl_garbage')->assertNotFound();
    }

    #[Test]
    public function garbage_multi_line_hash_is_a_404(): void
    {
        $this->get('/shared/ml_garbage')->assertNotFound();
    }

    #[Test]
    public function unprefixed_nonsense_hash_is_a_404(): void
    {
        $this->get('/shared/nonsense')->assertNotFound();
    }
}
