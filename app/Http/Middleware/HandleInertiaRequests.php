<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Middleware;
use Laravel\Jetstream\Jetstream;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     *
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'user' => auth()->check() ? $request->user()->toArray() : new User,
            // V72: optional prop — only rendered + shipped when the client
            // partial-reloads it (release-notes modal), not on every response.
            'releaseNotes' => Inertia::optional(fn () => static::releaseNotesHtml()),
            'version' => config('app.version'),
            'donations' => 1 * config('app.donations'),
        ]);
    }

    /**
     * Rendered release-notes HTML, cached per app version (V72). The key
     * rotates with config('app.version'), so rememberForever self-invalidates
     * on release; the markdown file is parsed once per version, not per request.
     */
    public static function releaseNotesHtml(): string
    {
        return Cache::rememberForever(
            'release_notes_html:'.config('app.version'),
            fn () => Str::markdown(file_get_contents(Jetstream::localizedMarkdownPath('release-notes.md')))
        );
    }
}
