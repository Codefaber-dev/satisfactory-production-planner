# SPEC — Dependency Upgrade + Tooling

## §G — Goal

Upgrade all PHP and JS dependencies from Laravel 9 / Vue 3.2 to current stable releases, then add static analysis (Larastan), code style enforcement (Pint, Biome), FE unit testing (Vitest), and CI pipelines (GitHub Actions). Post-upgrade: harden caching layer (cross-user contamination, TTL leaks, blocking ops, invalidation).

## §C — Constraints

- PHP 8.4.21 active via Herd; composer.json still requires `^8.1` — needs bumping.
- App uses Inertia.js (Laravel adapter + Vue adapter) — both must stay in sync.
- `intervention/image` v2→v3 has breaking API changes; code that uses it must be audited.
- Laravel 9→12 is a 3-major jump; must do 9→10→11→12 stepwise per Laravel upgrade guide.
- No Filament in this app (confirmed: no filament in composer.json).
- `maximebf/debugbar` abandoned — replace with `php-debugbar/php-debugbar` (pulled via `barryvdh/laravel-debugbar` v4 automatically).
- JS build uses `laravel-mix` v6 (webpack); upgrading tailwind v3→v4 may require switching to Vite.
- Tests must pass after each phase before proceeding.
- Biome replaces Prettier (installed in T16) — must remove `prettier` + `prettier-plugin-tailwindcss` in T21.
- Larastan level: start at 5; document suppressions in `phpstan-baseline.neon` if needed rather than downgrading level.
- Vitest config must share `vite.config.ts` to resolve Vue SFCs and aliases.

## §I — Surfaces

- `I.composer` — `composer.json` require/require-dev blocks
- `I.package` — `package.json` devDependencies/dependencies
- `I.config` — `config/` files affected by framework changes
- `I.routes` — `routes/` files (Laravel 10+ changes)
- `I.providers` — `app/Providers/` (Laravel 11 consolidates to bootstrap/app.php)
- `I.tests` — `tests/` PHPUnit 9→11 migration
- `I.image` — all uses of `intervention/image` in app code
- `I.inertia` — Inertia v0.6→v3 client + server adapter changes
- `I.pint` — `pint.json` (PHP code style config)
- `I.larastan` — `phpstan.neon` + `phpstan-baseline.neon` (static analysis config)
- `I.biome` — `biome.json` (FE linter/formatter config)
- `I.ci` — `.github/workflows/` (CI pipeline definitions)

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
- **V10** `./vendor/bin/pint --test` exits 0 — no PHP style violations.
- **V11** `./vendor/bin/phpstan analyse` exits 0 at configured level (≥5).
- **V12** `npx biome check .` exits 0 — no FE lint/format violations.
- **V13** `npm run test` (Vitest) exits 0 with ≥1 passing test.
- **V14** All GitHub Actions workflows pass on clean commits to main (BE: phpunit + pint; FE: vitest + biome).
- **V15** `energy('Iron Plate')` and `energy('Wire')` return values matching the manual stage-sum formula in RecipeEnergyTest (test formula is the spec).
- **V16** Two different guest users requesting the same production plan get identical results regardless of their session favorites (cache key must encode effective favorites, not the raw null sentinel).
- **V17** `flush-production-cache` artisan command exits 0 and all matching Redis keys are deleted; `Redis::select()` side effects do not persist to subsequent operations in the same worker.
- **V18** Guest Redis hashes (factories, multi-factories, favorites) have TTL ≥ session lifetime; no unbounded accumulation.

## §T — Tasks

| id  | status | task                                                                                      | cites          |
|-----|--------|-------------------------------------------------------------------------------------------|----------------|
| T1  | x      | Audit intervention/image usage in app — list all files and methods used                   | I.image, V4    |
| T2  | x      | Audit Inertia usage — list all Inertia::render calls and JS `useForm`/`usePage` patterns  | I.inertia, V3  |
| T3  | x      | Bump PHP constraint to `^8.4` in composer.json (Herd already on 8.4.21)                   | I.composer, V6 |
| T4  | x      | Bump inertia-laravel to ^1.0 (PHP 8.4 unblock), then minor/patch PHP upgrades             | I.composer, I.inertia, V1, V2, V9 |
| T5  | x      | Laravel 9→10 upgrade: follow laravel.com/docs/10.x/upgrade; update config, providers      | I.composer, I.config, I.providers, V1, V2 |
| T6  | x      | Laravel 10→11 upgrade: bootstrap/app.php consolidation, middleware changes                | I.composer, I.providers, V1, V2 |
| T7  | x      | Laravel 11→12 upgrade: review breaking changes, update config                             | I.composer, I.config, V1, V2 |
| T8  | x      | Upgrade PHPUnit 10→11 and update test syntax (static data providers, etc.)                | I.tests, V2    |
| T9  | x      | Upgrade predis v1→v3 and verify Redis config compatibility                                | I.composer, I.config, V1 |
| T10 | x      | Remove intervention/image from composer.json (audit T1: zero usages in app code)          | I.composer, V5  |
| T11 | x      | Upgrade inertia-laravel v0.6→v3 (server); align with Inertia Vue adapter version          | I.composer, I.inertia, V3 |
| T12 | x      | Upgrade @inertiajs/inertia-vue3 to match server adapter (v1 or v3 SDK)                   | I.package, I.inertia, V3 |
| T13 | x      | Upgrade JS minor/patch deps: vue 3.2→3.5, @vueuse/core, axios 0.x→1.x, pinia 2→2.x      | I.package, V7  |
| T14 | x      | Upgrade tailwind v3→v4 OR pin to v3 and upgrade within v3; update tailwind config         | I.package, V7, V8 |
| T15 | x      | Evaluate laravel-mix→Vite migration (required if tailwind v4 chosen in T14)              | I.package, V7, V8 |
| T16 | x      | Upgrade prettier v2→v3, prettier-plugin-tailwindcss to compatible version                 | I.package, V7  |
| T17 | x      | Upgrade ziggy v1→v2 and update any route() JS calls with changed API                     | I.composer, I.package, V1, V7 |
| T18 | x      | Final: run full test suite + npm build + manual smoke test; verify V1–V8                  | V1,V2,V3,V4,V5,V6,V7,V8 |
| T19 | x      | Install Laravel Pint, configure pint.json (Laravel preset), run + fix all violations      | I.composer, I.pint, V10 |
| T20 | x      | Install Larastan (nunomaduro/larastan), configure phpstan.neon at level 5, fix or baseline violations | I.composer, I.larastan, V11 |
| T21 | x      | Replace Prettier with Biome: remove prettier + prettier-plugin-tailwindcss, add @biomejs/biome, configure biome.json, update scripts | I.package, I.biome, V7, V12 |
| T22 | x      | Add Vitest: add vitest + @vitejs/plugin-vue to devDeps, configure vitest.config.ts, write ≥1 smoke test | I.package, V13 |
| T23 | x      | Add GitHub Actions BE workflow: phpunit + pint check on push/PR to main                   | I.ci, V1, V2, V10 |
| T24 | x      | Add GitHub Actions FE workflow: vitest + biome check on push/PR to main                   | I.ci, V7, V12, V13 |
| T25 | x      | Fix all 56 failing Unit tests: B4 ProductionGlobals collect(), B5 remove MySQL override + add RefreshDatabase, B6 fix DatabaseSeeder seeder order, B7 convert @test/@dataProvider to #[Test]/#[DataProvider] attributes | I.tests, V2 |
| T26 | x      | Fix BuildingDetails.php even-rows energy: preserve non-even `$energy_per_item` inside `if ($this->even \|\| $building_delta > 1)` block — current wrong formula `$power_usage / (base_per_min * clock_speed_pct)` returns ~6× too small; use pre-computed `$energy_per_item = $mj_per_min * $min_per_item` from before the block | I.tests, V2, V15 |
| T27 | x      | Fix raw_materials.php miner power values to wiki v1.0: Mk.2 12 MW → 15 MW; audit all other entries (Water extractor, Oil extractor, etc.) against wiki; update RecipeEnergyTest expected values to match corrected config | I.tests, V2, V15 |
| T28 | x      | Fix raw material energy unit inconsistency: config stores MW/(items/min) but production formula uses MJ/item (= MW × 60 / base_per_min); multiply all config values by 60 OR update energyStage() fallback to `return 60 * config(...)` — then update RecipeEnergyTest expected values | I.tests, V2, V15 |
| T29 | x      | Fix cross-user cache contamination: ProductionCalculator::make() passes favorites=null → ProductionGlobals loads session favorites inside the cached closure; cache key must include resolved favorites (load before key hash, not inside closure) | V2, V16 |
| T30 | x      | Fix FlushProductionCache: replace `Redis::select(1)` + `Redis::keys()` with SCAN on the configured cache connection; avoid mutating shared phpredis connection state | V17 |
| T31 | x      | Fix guest Redis TTL leak: GuestFactories, GuestMultiFactories, GuestFavorites store hashes with no expiry; set TTL on hSet operations equal to session lifetime (config('session.lifetime') * 60) | V18 |
| T32 | x      | Fix cache key typo: ProductionCalculator.php:48 `'production_calc_ '` has trailing space — strip it | V2 |
| T33 | .      | Add cache for b() helper: Building::whereName() called on every invocation with no memoization; wrap in Cache::rememberForever("buildings.{$name}") | V2 |
| T34 | .      | Fix even-rows branch energy: BuildingDetails.php:144-166 even-rows branch updates clock_speed but not energy_per_item/total_energy; recalculate both after num_buildings/clock_speed update using same formula as lines 103-110 | V2, V15 |
| T35 | .      | Add model-update cache invalidation: Ingredient and Recipe `saved`/`updated` observers should forget base_recipe.*, recipes.*, ingredients.* and flush production_calc_* keys | V2, V16 |

## §B — Bug Log

| id | date | cause | fix |
|----|------|-------|-----|
| B1 | 2026-06-08 | inertia-laravel ^0.6.x caps PHP ~8.3.0; blocked composer update on PHP 8.4 | V9 |
| B2 | 2026-06-08 | 56 of 88 Unit tests were already failing on L9 (MySQL access denied + pre-existing logic bugs); same count on L10 — no regression, but V2 cannot be clean until T8 fixes syntax and pre-existing DB issues are resolved | V2 |
| B3 | 2026-06-08 | L11 upgrade: `assertDeleted` removed from test helpers; 1 additional test failure in Feature/IngredientTest.php:85. Fix in T8 — replace with `assertModelMissing`. Also: phpunit.xml needed APP_KEY for tests — L11 EncryptionServiceProvider throws early on empty key | V2 |
| B4 | 2026-06-08 | ProductionGlobals::$used_byproducts typed as Collection but constructor assigns raw array — PHP 8.4 strict property type check; missing collect() wrap at app/Production/ProductionGlobals.php:37 | V2 |
| B5 | 2026-06-08 | RecipeProductionTest + ProductionStepTest force `database.default=mysql` in setUp() — overrides phpunit.xml sqlite_testing; all DB queries fail with 'forge'@'localhost' access denied | V2 |
| B6 | 2026-06-08 | DatabaseSeeder only calls FicsmasIngredientSeeder+FicsmasRecipeSeeder; FicsmasRecipeSeeder calls Building::ofName() which returns null (no buildings seeded) causing `?: $this` fallback to return builder; RecipeEnergyTest seeds via DatabaseSeeder so also fails | V2 |
| B7 | 2026-06-08 | All test files still use `@test`/`@dataProvider` doc annotations — deprecated in PHPUnit 11, triggers 64 warnings; T8 marked done but annotation migration was not completed | V2 |
| B8 | 2026-06-08 | RecipeEnergyTest::it_calcs_energy_cost_of_iron_plate/wire — test compares manual formula vs energy() function; two paths compute different values (~24.15 vs ~4.19); pre-existing logic mismatch (was in original 56 failures due to DB issue masking this); energyStage() variable shadowing bug fixed (helpers.php) but formula equivalence is not established | V2 |
| B9 | 2026-06-08 | .env file missing from repo clone — Laravel Dotenv tries to read it, generates PHP warning output during tests, PHPUnit marks tests as risky; fix: cp .env.example .env locally | V2 |
| B10 | 2026-06-08 | BuildingDetails.php even-rows branch (triggered when `building_delta > 1`, i.e. belt_speed 780 + qty = base_per_min * 1000) overwrites `energy_per_item` with `$power_usage / (base_per_min * clock_speed_pct)` — wrong formula gives ~6× too small energy; correct value is `$mj_per_min * $min_per_item` computed before the block (= `building_base_power * 60 / base_per_min`); getTotalEnergy() chains this, so energy() helper returns ~4.19 instead of ~24.15 for Iron Plate | V2, V15 |
| B11 | 2026-06-08 | raw_materials.php config stores miner energy as `MW / (items/min)` (no ×60), but production formula uses `MW * 60 / base_per_min` (= MJ/item); raw extraction contribution is 60× too small in absolute terms. Also: config comment says Miner Mk.2 = 12 MW but wiki.gg (v1.0) says 15 MW — pre-1.0 early access value. Neither issue causes RecipeEnergyTest to fail (test uses same config values via energyStage()), but physically incorrect energy model | V15 |
| B12 | 2026-06-09 | ProductionCalculator.php:48 cache key literal `'production_calc_ '` has trailing space — key format is `production_calc_ <hash>` not `production_calc_<hash>`; cosmetic but inconsistent with FlushProductionCache search pattern | T32 |
| B13 | 2026-06-09 | Cross-user cache contamination: show() passes favorites=null to ProductionCalculator::make(); inside the cached closure ProductionGlobals calls Favorites::getMappedFavorites(null) which loads session-specific favorites from Redis; cache key hashes null for all users → first requesting user's favorites bake into shared cached production result; subsequent users with same product/qty/recipe get first user's recipe choices | V16, T29 |
| B14 | 2026-06-09 | GuestFactories, GuestMultiFactories, GuestFavorites store Redis hashes with no TTL; PHP sessions expire (default 120 min) but Redis guest data accumulates indefinitely → unbounded Redis memory growth | V18, T31 |
| B15 | 2026-06-09 | FlushProductionCache::handle() calls Redis::select(1) which mutates the shared phpredis default connection for the lifetime of the PHP-FPM worker; subsequent Redis operations on the default connection in the same worker silently use DB 1 instead of DB 0 | V17, T30 |
| B16 | 2026-06-09 | FlushProductionCache uses Redis::keys('*production_calc*') — O(N) blocking scan that freezes the Redis server for all clients during execution; must use SCAN with cursor for production safety | V17, T30 |
| B17 | 2026-06-09 | No cache invalidation on model save: editing a Recipe or Ingredient in DB leaves stale base_recipe.*, recipes.*, ingredients.*, and production_calc_* entries; no Eloquent observer or model event hooks trigger invalidation | T35 |
| B18 | 2026-06-09 | BuildingDetails.php even-rows branch (lines 144-166) updates num_buildings and clock_speed but skips recalculating energy_per_item and total_energy; calculatePowerUsage() is nonlinear (exponent 1.321928) so energy_per_item must be recomputed from updated clock_speed; T26 removed the ~6× wrong formula but the remaining pre-branch value uses the wrong clock_speed | V15, T34 |
| B19 | 2026-06-09 | b() helper (helpers.php:63) calls Building::whereName($name)->first() on every invocation with no caching; buildings are static game data; causes redundant DB queries in every production calculation | T33 |
