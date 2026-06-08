# SPEC — Dependency Upgrade

## §G — Goal

Upgrade all PHP and JS dependencies from Laravel 9 / Vue 3.2 baseline to current stable releases, in phases to minimize breakage risk.

## §C — Constraints

- PHP 8.4.21 active via Herd; composer.json still requires `^8.1` — needs bumping.
- App uses Inertia.js (Laravel adapter + Vue adapter) — both must stay in sync.
- `intervention/image` v2→v3 has breaking API changes; code that uses it must be audited.
- Laravel 9→12 is a 3-major jump; must do 9→10→11→12 stepwise per Laravel upgrade guide.
- No Filament in this app (confirmed: no filament in composer.json).
- `maximebf/debugbar` abandoned — replace with `php-debugbar/php-debugbar` (pulled via `barryvdh/laravel-debugbar` v4 automatically).
- JS build uses `laravel-mix` v6 (webpack); upgrading tailwind v3→v4 may require switching to Vite.
- Tests must pass after each phase before proceeding.

## §I — Surfaces

- `I.composer` — `composer.json` require/require-dev blocks
- `I.package` — `package.json` devDependencies/dependencies
- `I.config` — `config/` files affected by framework changes
- `I.routes` — `routes/` files (Laravel 10+ changes)
- `I.providers` — `app/Providers/` (Laravel 11 consolidates to bootstrap/app.php)
- `I.tests` — `tests/` PHPUnit 9→11 migration
- `I.image` — all uses of `intervention/image` in app code
- `I.inertia` — Inertia v0.6→v3 client + server adapter changes

## §V — Invariants

- **V1** App boots (`php artisan tinker --execute="echo 'ok'"`) after every phase.
- **V2** All existing tests pass (`php artisan test`) after every phase.
- **V3** Inertia server adapter version matches Inertia client adapter version (both v0.x or both v1/v2/v3).
- **V4** `intervention/image` calls use the API for the installed major version (v2 API removed in v3).
- **V5** No abandoned packages remain in production `require` (only dev is tolerated temporarily).
- **V6** PHP version constraint in `composer.json` matches or narrows to running PHP version (`^8.4`).
- **V7** JS build succeeds (`npm run production`) after every JS phase.
- **V8** `laravel-mix` and tailwind major versions are compatible (mix v6 supports tailwind v3 max; tailwind v4 requires Vite).
- **V9** All packages in `require` declare support for installed PHP version before `composer update` runs.

## §T — Tasks

| id  | status | task                                                                                      | cites          |
|-----|--------|-------------------------------------------------------------------------------------------|----------------|
| T1  | x      | Audit intervention/image usage in app — list all files and methods used                   | I.image, V4    |
| T2  | x      | Audit Inertia usage — list all Inertia::render calls and JS `useForm`/`usePage` patterns  | I.inertia, V3  |
| T3  | x      | Bump PHP constraint to `^8.4` in composer.json (Herd already on 8.4.21)                   | I.composer, V6 |
| T4  | x      | Bump inertia-laravel to ^1.0 (PHP 8.4 unblock), then minor/patch PHP upgrades             | I.composer, I.inertia, V1, V2, V9 |
| T5  | x      | Laravel 9→10 upgrade: follow laravel.com/docs/10.x/upgrade; update config, providers      | I.composer, I.config, I.providers, V1, V2 |
| T6  | .      | Laravel 10→11 upgrade: bootstrap/app.php consolidation, middleware changes                | I.composer, I.providers, V1, V2 |
| T7  | .      | Laravel 11→12 upgrade: review breaking changes, update config                             | I.composer, I.config, V1, V2 |
| T8  | .      | Upgrade PHPUnit 10→11 and update test syntax (static data providers, etc.)                | I.tests, V2    |
| T9  | .      | Upgrade predis v1→v3 and verify Redis config compatibility                                | I.composer, I.config, V1 |
| T10 | .      | Remove intervention/image from composer.json (audit T1: zero usages in app code)          | I.composer, V5  |
| T11 | .      | Upgrade inertia-laravel v0.6→v3 (server); align with Inertia Vue adapter version          | I.composer, I.inertia, V3 |
| T12 | .      | Upgrade @inertiajs/inertia-vue3 to match server adapter (v1 or v3 SDK)                   | I.package, I.inertia, V3 |
| T13 | .      | Upgrade JS minor/patch deps: vue 3.2→3.5, @vueuse/core, axios 0.x→1.x, pinia 2→2.x      | I.package, V7  |
| T14 | .      | Upgrade tailwind v3→v4 OR pin to v3 and upgrade within v3; update tailwind config         | I.package, V7, V8 |
| T15 | .      | Evaluate laravel-mix→Vite migration (required if tailwind v4 chosen in T14)              | I.package, V7, V8 |
| T16 | .      | Upgrade prettier v2→v3, prettier-plugin-tailwindcss to compatible version                 | I.package, V7  |
| T17 | .      | Upgrade ziggy v1→v2 and update any route() JS calls with changed API                     | I.composer, I.package, V1, V7 |
| T18 | .      | Final: run full test suite + npm build + manual smoke test; verify V1–V8                  | V1,V2,V3,V4,V5,V6,V7,V8 |

## §B — Bug Log

| id | date | cause | fix |
|----|------|-------|-----|
| B1 | 2026-06-08 | inertia-laravel ^0.6.x caps PHP ~8.3.0; blocked composer update on PHP 8.4 | V9 |
| B2 | 2026-06-08 | 56 of 88 Unit tests were already failing on L9 (MySQL access denied + pre-existing logic bugs); same count on L10 — no regression, but V2 cannot be clean until T8 fixes syntax and pre-existing DB issues are resolved | V2 |
