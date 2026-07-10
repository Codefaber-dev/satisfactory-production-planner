<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * V72: releaseNotes is an optional (lazy) Inertia prop backed by a per-version
 * cache — the markdown file is parsed once per app version, not per request,
 * and the rendered HTML is only shipped when the client partial-reloads it.
 */
class ReleaseNotesPropTest extends TestCase
{
    // /checklist (the fixture page) queries ingredients since V149 (B73)
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Per-process store: keeps cache assertions isolated from parallel
        // workers flushing the shared redis test prefix.
        config()->set('cache.default', 'array');
    }

    protected function cacheKey(): string
    {
        return 'release_notes_html:'.config('app.version');
    }

    #[Test]
    public function default_page_props_exclude_release_notes(): void
    {
        $response = $this->get('/checklist');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Checklist/Index')
            ->missing('releaseNotes')
        );

        // Optional prop untouched → markdown never parsed/cached.
        $this->assertFalse(Cache::has($this->cacheKey()));
    }

    #[Test]
    public function partial_reload_returns_rendered_release_notes(): void
    {
        // First visit establishes the asset version for the partial request.
        $version = $this->get('/checklist')->viewData('page')['version'];

        $response = $this->get('/checklist', [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
            'X-Inertia-Partial-Component' => 'Checklist/Index',
            'X-Inertia-Partial-Data' => 'releaseNotes',
        ]);

        // Partial responses are raw Inertia JSON (no view), so assertInertia
        // does not apply — assert on the JSON payload directly.
        $response->assertOk();
        $html = $response->json('props.releaseNotes');
        $this->assertIsString($html);
        $this->assertStringContainsString('Release Notes', $html);

        // Rendered HTML now cached for subsequent requests this version.
        $this->assertTrue(Cache::has($this->cacheKey()));
    }

    #[Test]
    public function cached_html_is_served_without_reparsing(): void
    {
        Cache::forever($this->cacheKey(), '<p>SENTINEL</p>');

        $version = $this->get('/checklist')->viewData('page')['version'];

        $response = $this->get('/checklist', [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
            'X-Inertia-Partial-Component' => 'Checklist/Index',
            'X-Inertia-Partial-Data' => 'releaseNotes',
        ]);

        // Sentinel comes back verbatim → cache hit, no markdown re-parse.
        $response->assertOk();
        $this->assertSame('<p>SENTINEL</p>', $response->json('props.releaseNotes'));
    }

    #[Test]
    public function release_notes_html_renders_markdown_once_per_version(): void
    {
        $first = HandleInertiaRequests::releaseNotesHtml();

        $this->assertStringContainsString('Release Notes', $first);
        $this->assertSame($first, Cache::get($this->cacheKey()));
    }
}
