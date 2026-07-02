<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * V71/B47: the unauthenticated GET /apply-fixes route mutated recipe data and
 * flushed the production cache on every hit. It must stay gone — GET routes
 * are side-effect-free; one-off data fixes belong in artisan commands.
 */
class ApplyFixesRouteTest extends TestCase
{
    #[Test]
    public function apply_fixes_route_is_removed(): void
    {
        $this->get('/apply-fixes')->assertNotFound();
    }
}
