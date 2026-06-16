<?php

namespace Tests\Feature;

use App\Support\ProductionCanary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Golden-master regression guard for the production calculator. The fixture is
 * generated (in the testing env) on first run, then compared on every run. A
 * refactor that should be behaviour-preserving (e.g. acyclic plans under the
 * loop-solver work) must keep every snapshot identical; an intentional change
 * (e.g. T100b switching deeper loops from forced-recipe to solving) shows up as
 * specific failing plans, which are then re-baselined with the export command.
 */
class ProductionCanaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    #[Test]
    public function canary_plans_match_the_golden_snapshot(): void
    {
        $path = base_path(ProductionCanary::FIXTURE);
        $current = ProductionCanary::snapshotAll();

        if (! file_exists($path)) {
            @mkdir(dirname($path), 0777, true);
            file_put_contents($path, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
            $this->markTestSkipped('Canary fixture generated from current implementation — re-run to compare.');
        }

        $golden = json_decode(file_get_contents($path), true);

        foreach ($golden as $name => $expected) {
            $this->assertArrayHasKey($name, $current, "canary plan '{$name}' no longer produced");

            $this->assertEqualsWithDelta(
                $expected['snapshot']['power'],
                $current[$name]['snapshot']['power'],
                0.01,
                "canary '{$name}': total power drifted"
            );

            $this->assertEqualsCanonicalizing(
                array_keys($expected['snapshot']['totals']),
                array_keys($current[$name]['snapshot']['totals']),
                "canary '{$name}': product set drifted"
            );

            foreach ($expected['snapshot']['totals'] as $key => $val) {
                $this->assertEqualsWithDelta(
                    $val,
                    $current[$name]['snapshot']['totals'][$key] ?? null,
                    0.01,
                    "canary '{$name}': {$key} total drifted"
                );
            }
        }
    }
}
