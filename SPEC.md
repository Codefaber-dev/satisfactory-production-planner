# SPEC — Game Data Update v1.2

## §G — Goal

Backport `app/Helpers/UpdateOneZero.php` into the canonical seeders so a fresh `db:seed` produces an identical game-data state as the live migration. Also fix v1.1 recipe rate errors (Iron/Copper Alloy Ingots) that are absent from the helper, rename two legacy ingredient names (`SAM Ore` → `SAM`, `Green Power Slug` → `Blue Power Slug`), and add Ficsonium Fuel Rod support to the power planner.

## §C — Constraints

- `UpdateOneZero.php` is the authoritative source for all v1.0+ data; seeders must reproduce its final state on a fresh install
- RecipeSeeder format: `'Building' => ['Product' => recipe_data]`; byproducts use separate `byproducts` key; ingredients stored as per-minute consumption rates; use plain string names (not Enum::value) to match existing seeder style
- IngredientSeeder currently uses `'SAM Ore'` (tier1) and `'Green Power Slug'` (tier1); both names must change to canonical values: `'SAM'` and `'Blue Power Slug'`; any downstream seeder rows referencing the old names must also update
- BuildingSeeder currently has mk1–mk4 for each building; Converter and Quantum Encoder add mk1-only (base_power 500 each, matching helper)
- Excited Photonic Matter has zero material inputs — seeder uses empty `ingredients` array `[]`; production calculator must not throw on zero-input recipe
- SAM (raw) extraction uses Mk.2 miner (15 MW, 120/min) → 7.5 MJ/item; no rarity entry (inexhaustible nodes)
- Iron Alloy Ingot v1.1 change NOT in helper: output 50→75/min, Iron Ore 20→40/min, Copper Ore 20→10/min
- Copper Alloy Ingot v1.1 change NOT in helper: Iron Ore 25→50/min input; output/Copper Ore unchanged
- Quantum Encoder AI Expansion Server: Dark Matter Residue is byproduct (100/min), not input
- Ficsonium Fuel Rod: Nuclear Power Plant, 1/min, 2500 MW, zero waste — no waste product key

## §I — Surfaces

- `I.helper-six` — `app/Helpers/UpdateSix.php` (Update 6 combat/weapon content)
- `I.helper-1.0` — `app/Helpers/UpdateOneZero.php` (v1.0 endgame content: SAM chain, Converter, QE)
- `I.helper-1.0b` — `app/Helpers/UpdateOneZero2.php` (v1.0 patches: rate fixes, more alts, cleanup)
- `I.ingredient-seeder` — `database/seeders/IngredientSeeder.php`
- `I.recipe-seeder` — `database/seeders/RecipeSeeder.php`
- `I.building-seeder` — `database/seeders/BuildingSeeder.php`
- `I.raw-materials` — `config/raw_materials.php`
- `I.power-planner` — `app/PowerPlanner/` (FicsoniumFuelRod generator class)
- `I.tests` — `tests/` (RecipeEnergyTest, seeder smoke tests)
- `I.plan-params` — per-plan params object passed to `ProductionCalculator` (clock_speed, somersloop_slots, recipe choices, etc.)
- `I.build-diagram` — `resources/js/Pages/Production/BuildDiagram.vue` (footprint render: foundations grid, building tiles, monogram)
- `I.building-settings` — Building Settings panel in `resources/js/Pages/Production/Show.vue` (per-type count-multiple inputs)
- `I.local-storage` — browser localStorage keys (blueprint sizes global, per-factory toggles)

## §V — Invariants

- **V21** After seeding: `Ingredient::ofName('SAM')` exists (not `'SAM Ore'`); `Ingredient::ofName('Blue Power Slug')` exists (not `'Green Power Slug'`); `Ingredient::ofName()` returns model for every item in `UpdateOneZero::ingredients()` plus `'Ficsonium Fuel Rod'`.
- **V22** Iron Alloy Ingot recipe in DB: `base_per_min=75`, Iron Ore 40/min, Copper Ore 10/min, building = Foundry.
- **V23** Copper Alloy Ingot recipe in DB: `base_per_min=100`, Copper Ore 50/min, Iron Ore 50/min, building = Foundry.
- **V24** `Building::ofName('Converter')` and `Building::ofName('Quantum Encoder')` both return model with `mk1` variant (base_power 500).
- **V25** All Converter recipes seeded (from both helpers); Excited Photonic Matter has empty ingredients array; Dark Matter Residue uses 50 Reanimated SAM/min (UpdateOneZero2 corrected rate, not 5).
- **V26** All 6 `UpdateOneZero::recipes()['Quantum Encoder']` entries seeded; Dark Matter Residue is byproduct (not input) at 1:1 with Excited Photonic Matter.
- **V27** All Diamond recipes (5) and Ficsonium recipe from `UpdateOneZero::recipes()['Particle Accelerator']` seeded; Dark Matter Crystal recipes (3) seeded.
- **V28** `php artisan db:seed` completes without exceptions.
- **V29** `RecipeEnergyTest` passes without regression after V22/V23 corrections.
- **V30** `NuclearPowerPlant` fuel map includes `Ingredient::FICSONIUM_FUEL_ROD` at `fuel_per_min=1`; no standalone `FicsoniumFuelRod` generator class exists.
- **V31** `FuelGenerator` fuel map has no duplicate keys; includes all 5 valid liquid fuels: Fuel (750 MJ), Turbofuel (2000 MJ), Liquid Biofuel (750 MJ), Rocket Fuel (3600 MJ), Ionized Fuel (5000 MJ). Packaged variants excluded (Fuel Generator is pipe-input only). `Base::buildable_fuels` includes `ROCKET_FUEL`, `IONIZED_FUEL`, `FICSONIUM_FUEL_ROD`.
- **V32** Factory PATCH (`PATCH /factories/:id`) persists full recipe-choice state: re-loading the factory via its Plan link restores identical recipe selections (same recipe ids, same nested sub-choices) as when saved. No recipe reverts to default on reload.
- **V33** Clicking Update on a multi-output plan with N outputs that each have a valid `product` and `recipe` returns a plan containing those same N outputs. No valid output is silently dropped after the Inertia round-trip.
- **V34** Somersloop slots stored per building type: Smelter=1, Constructor=1, Foundry=2, Assembler=2, Refinery=2, Blender=4, Particle Accelerator=4, Packager=0. Each filled slot adds proportional output boost up to 2× at max slots. Power consumption scales up to 4× at max amplification. UI allows per-building-instance slot selection (0 to max_slots) on each production step.
- **V35** `php artisan satis:export-game-data` (or equivalent) produces a portable JSON/SQL dump of all game-data tables (ingredients, recipes, buildings, raw_materials) suitable for seeding a fresh Docker install without running all migration helpers.
- **V36** Recipe cost multiplier is a numeric plan param (default 1.0, range 0.1–10.0). When set, all ingredient consumption quantities in `BuildingDetails` are multiplied by it (output qty and building count unchanged). Setting persists in plan params across fetch/save. UI exposes it as a numeric input (or preset buttons) alongside clock_speed controls.
- **V37** Power cost multiplier is a numeric plan param (default 1.0, range 0.1–10.0, step 0.1). When set, all building power consumption values in the plan output are multiplied by it (ingredient qty and building count unchanged). Persists in plan params. UI exposes it alongside other plan-level power controls.
- **V38** UI renders usably on mobile viewports (≥320px): no horizontal overflow on any plan page; recipe selectors, building steps, and output controls are legible and tappable (min 44px touch targets) at 375px width.
- **V39** Per-building-type count-multiple: each building type can have a user-configured step value (positive integer, default 1). Building count in plan output rounds up to nearest multiple of that step. Config persists in plan params. UI allows setting per-type multiples in a building settings panel.
- **V40** ∀ backend lookup keyed by product+recipe into request params (`somersloops`, future per-step params): key = `name|(recipe->description ?? name)` — frontend convention (`recipe.description || product.name`, ProductionStep.vue). `getDescription()` 'default' fallback ⊥ forbidden in request-param keys. Single source: `Step::getProductKey()`. Test must use frontend-shaped key (`Iron Ingot|Iron Ingot`, not `Iron Ingot|default`).
- **V41** Blueprint grouping toggleable per building type per factory. Blueprint size X = the V39 count-multiple (same number, one param — not a second value). Toggle ON for a type: building count rounds up to multiple of X (V39 mechanism active with multiple=X), build diagram renders count/X blueprint tiles instead of individual building outlines, each tile labeled `<monogram>x<X>` (e.g. `Cx8`, `Sx10`). Toggle OFF: effective multiple = 1, diagram renders individual building tiles unchanged from current behavior.
- **V42** Persistence split: blueprint sizes per building type → global localStorage (shared across all factories — blueprints reusable in-game); per-type enable toggles → localStorage keyed by factory id. Backend contract unchanged: effective multiples (size if enabled, else 1) still sent via `building_multiples` plan param; no server schema change. Fresh browser/no LS = all toggles off, all sizes 1.
- **V43** Blueprint designer mark selectable: Mk.1 = 32×32 m (4×4 foundations), Mk.2 = 40×40 m (5×5), Mk.3 = 48×48 m (6×6). Grouped diagram tile drawn at selected designer footprint dims. No fit validation/warning — in-game workarounds exist (clipping, vertical stacking). Blueprint size hard-clamped to 1–40 buildings per group (input max + clamp on LS read).
- **V44** Grouping supersedes force-even-rows: when effective multiple X > 1 for a building type, the even-rows adjustment (`BuildingDetails.php` even branch — `even` plan param OR `building_delta > 1` auto-trigger) must not alter `num_buildings`; count stays exact multiple of X. Even setting applies only to ungrouped types.

## §T — Tasks

| id  | status | task                                                                                      | cites          |
|-----|--------|-------------------------------------------------------------------------------------------|----------------|
| T48 | x      | Fix Iron Alloy Ingot in RecipeSeeder: base_yield 5→15, base_per_min 50→75, Iron Ore 20→40/min, Copper Ore 20→10/min (in UpdateOneZero2 Foundry block) | I.recipe-seeder, I.helper-1.0b, V22 |
| T49 | x      | Fix Copper Alloy Ingot in RecipeSeeder: base_yield 20→10, Iron Ore 25→50/min (in UpdateOneZero2 Foundry block) | I.recipe-seeder, I.helper-1.0b, V23 |
| T50 | x      | Remove deprecated items from IngredientSeeder: `Alien Carapace` (tier1), `Color Cartridge` (tier2), `Spiked Rebar` (tier4), `Beacon` (tier5), `Rifle Cartridge` (tier6) | I.ingredient-seeder, I.helper-six, I.helper-1.0b |
| T51 | x      | Remove deprecated recipes from RecipeSeeder: `Beacon`, `Color Cartridge`, `Steel Coated Plate`; also remove `Rifle Cartridge` recipe (uses deleted Beacon ingredient) | I.recipe-seeder, I.helper-1.0b |
| T52 | x      | Rename `'SAM Ore'`→`'SAM'` and `'Green Power Slug'`→`'Blue Power Slug'` in IngredientSeeder tier1; update all RecipeSeeder string references to match | I.ingredient-seeder, I.recipe-seeder, I.helper-1.0, V21 |
| T53 | x      | Add UpdateSix ingredients to IngredientSeeder: tier1 raw: Hog Remains, Plasma Spitter Remains, Hatcher Remains, Stinger Remains; tier2: Alien Protein, Organic Data Capsule, Gas Nobelisk, Pulse Nobelisk, Smokeless Powder; tier3: Nuke Nobelisk; tier4: Cluster Nobelisk, Explosive Rebar, Iron Rebar, Stun Rebar, Shatter Rebar; tier6: Rifle Ammo, Turbo Rifle Ammo, Homing Rifle Ammo | I.ingredient-seeder, I.helper-six, V21 |
| T54 | x      | Backport UpdateOneZero ingredients to IngredientSeeder using helper tier values; add Ficsonium Fuel Rod; use plain strings matching Ingredient enum values | I.ingredient-seeder, I.helper-1.0, V21 |
| T55 | x      | Add `'SAM'` energy cost to `config/raw_materials.php`: `'SAM' => 15 * 60 / 120` (7.5 MJ/item, Mk.2 miner); no rarity entry | I.raw-materials |
| T56 | x      | Add Converter to BuildingSeeder (mk1 only): 2 in / 2 out, base_power 500, build cost from helper: 10 Fused Modular Frame, 25 Cooling System, 50 Radio Control Unit, 100 SAM Fluctuator | I.building-seeder, I.helper-1.0, V24 |
| T57 | x      | Add Quantum Encoder to BuildingSeeder (mk1 only): 4 in / 2 out, base_power 500, build cost from helper: 20 Turbo Motor, 20 Supercomputer, 50 Cooling System, 50 Time Crystal, 100 Ficsite Trigon | I.building-seeder, I.helper-1.0, V24 |
| T58 | x      | Backport UpdateSix Constructor recipes to RecipeSeeder: Alien Protein (Hog/Spitter/Hatcher/Stinger variants), Biomass (Alien Protein alt), Organic Data Capsule, Iron Rebar | I.recipe-seeder, I.helper-six |
| T59 | x      | Backport UpdateSix Assembler/Refinery/Manufacturer/Blender recipes to RecipeSeeder: Black Powder (new Assembler version), Nobelisk, Gas Nobelisk, Pulse Nobelisk, Cluster Nobelisk, Rifle Ammo, Stun Rebar, Shatter Rebar, Homing Rifle Ammo; Smokeless Powder + Polyester Fabric (Refinery); Explosive Rebar, Nuke Nobelisk, Turbo Rifle Ammo (Manufacturer); Turbo Rifle Ammo Turbofuel variant (Blender) | I.recipe-seeder, I.helper-six |
| T60 | x      | Replace/update existing RecipeSeeder entries per UpdateOneZero Constructor additions: Alien DNA Capsule, Aluminum Beam alt, Aluminum Rod alt, Iron Pipe alt, Power Shard 1/2/5 alts, Reanimated SAM, Ficsite Trigon | I.recipe-seeder, I.helper-1.0, V21 |
| T61 | x      | Update Assembler recipes in RecipeSeeder per UpdateOneZero: Plastic AI Limiter alt, Silicon Circuit Board alt, updated Computer rates, OC Supercomputer update, Encased Industrial Pipe alt, updated Magnetic Field Generator; also UpdateOneZero2 additions: Fine Black Powder alt, Fine Concrete alt, Rubber Concrete alt, Coated Iron Plate alt, Automated Miner alt, Adhered Iron Plate alt, Copper Rotor alt, Cheap Silica alt | I.recipe-seeder, I.helper-1.0, I.helper-1.0b |
| T62 | x      | Update Foundry recipes in RecipeSeeder per UpdateOneZero: Basic Iron Ingot alt, Fused Quartz Crystal alt, Molded Beam alt, Molded Steel Pipe alt, Steel Cast Plate alt, Tempered Caterium Ingot alt, Tempered Copper Ingot alt; UpdateOneZero2: Compacted Steel Ingot alt | I.recipe-seeder, I.helper-1.0, I.helper-1.0b |
| T63 | x      | Update Refinery recipes in RecipeSeeder per UpdateOneZero: Electrode Aluminum Scrap alt, Ionized Fuel, Leached Caterium/Copper/Iron alts, Quartz Purification alt | I.recipe-seeder, I.helper-1.0 |
| T64 | x      | Update Manufacturer recipes in RecipeSeeder: UpdateOneZero additions (updated Computer/Supercomputer/Heavy Modular Frame, Automated Speed Wiring alt, Ballistic Warp Drive, Iodine Infused Filter, Rigor Motor alt, Uranium Fuel Unit alt, SAM Fluctuator, Singularity Cell); UpdateOneZero2 additions (Cooling Device alt, updated Adaptive Control Unit/Radio Control Unit, Radio Connection Unit alt, Gas Filter, updated Explosive Rebar rate) | I.recipe-seeder, I.helper-1.0, I.helper-1.0b, V21 |
| T65 | x      | Backport Blender/Packager recipe additions from UpdateOneZero: Biochemical Sculptor, Distilled Silica alt, Rocket Fuel, Nitro Rocket Fuel alt; Packaged Ionized Fuel, Packaged Rocket Fuel, Unpackage Ionized Fuel, Unpackage Rocket Fuel | I.recipe-seeder, I.helper-1.0 |
| T66 | x      | Backport Particle Accelerator recipes from UpdateOneZero: Diamonds (5 variants), Dark Matter Crystal (3 variants), Ficsonium | I.recipe-seeder, I.helper-1.0, V27 |
| T67 | x      | Backport Quantum Encoder recipes from UpdateOneZero: all 6 recipes with Dark Matter Residue byproduct | I.recipe-seeder, I.helper-1.0, V26 |
| T68 | x      | Backport Converter recipes from both helpers: Excited Photonic Matter (empty ingredients), Dark Matter Residue (50 Reanimated SAM/min — use UpdateOneZero2 corrected rate), Time Crystal, Ficsite Ingot (3 variants), Pink Diamonds, ore-conversion alts (both Bauxite/Caterium/Coal/Copper/Iron/Limestone/Nitrogen/Quartz/Sulfur/Uranium alts), Dark Ion Fuel alt | I.recipe-seeder, I.helper-1.0, I.helper-1.0b, V25 |
| T69 | x      | Create `FicsoniumFuelRod` power planner class in `app/PowerPlanner/` mirroring existing fuel rod classes: power_output=2500 MW, fuel_per_min=1, no byproduct | I.power-planner, V30 |
| T70 | x      | Run `php artisan db:seed` (fresh DB) and verify V28; log failures as §B entries | V28 |
| T71 | x      | Verify/update RecipeEnergyTest after T48/T49 corrections; run full test suite; verify ≥ 80% coverage | I.tests, V29 |
| T72 | x      | Fix `FuelGenerator`: remove duplicate `IONIZED_FUEL` key (second entry → `ROCKET_FUEL` at 3600 MJ); add `ROCKET_FUEL`, `IONIZED_FUEL`, `FICSONIUM_FUEL_ROD` to `Base::buildable_fuels` | I.power-planner, V31 |
| T73 | x      | Fix `saveMyFactory` PATCH: capture full recipe-choice state (all `newChoices` + current sub-recipe choices from the production tree) before sending to `/factories/:id`; verify reload restores identical selections — resolves issue #4 | V32 |
| T74 | x      | Fix multi-output line-disappears bug: trace Inertia component lifecycle on `fetch()` call after second output added; ensure all outputs with valid product+recipe survive the round-trip and are present in re-initialized `data()` `outputs` array — resolves issue #3 | V33 |
| T75 | ~      | ~~Per-output Scale Up~~ — not practical; all outputs in multi plan are interconnected; issue #6 closed as won't-fix | — |
| T76 | x      | Somersloop support: per-building-instance slot selector (0…max_slots) on each production step; max_slots from V34 table; amplified output = base_per_min × (1 + slots/max_slots); power cost scales up to 4× at max; slot state persists in plan params across fetch — resolves issue #2 remaining item | V34 |
| T77 | x      | Create `php artisan satis:export-game-data` command that dumps ingredients/recipes/buildings/raw_materials to JSON; add README note for Docker self-hosters to run after seed — resolves issue #9 | V35 |
| T78 | x      | Recipe cost multiplier: add `cost_multiplier` plan param (float, default 1.0); apply in `BuildingDetails::calc()` to ingredient pivot quantities; thread param through `ProductionCalculator` → `CalculatesSteps` → `BuildingOverview`; persist in plan params via existing save/fetch flow; add UI control | I.plan-params, V36 |
| T79 | x      | Power cost multiplier: add `power_multiplier` plan param (float, default 1.0, range 0.1–10.0 step 0.1); apply to all power consumption values at display layer in `BuildingOverview`/plan summary; persist in plan params; add UI control (slider or numeric input) alongside clock_speed/somersloop controls | I.plan-params, V37 |
| T80 | x      | Responsive design pass: audit all plan pages at 375px; fix horizontal overflow (tables → cards or scroll-x); ensure recipe selectors are touch-friendly; stack multi-column layouts to single column below `md` breakpoint; verify on real mobile viewport | V38 |
| T81 | x      | Update checklist feature for v1.2: add new buildings (Converter, Quantum Encoder), new tier-9 ingredients and recipes from §T60–T68 to any checklist/milestone tracker; remove deprecated items (Alien Carapace, Color Cartridge, Spiked Rebar, Beacon, Rifle Cartridge) from checklists | V21, V24 |
| T82 | x      | Per-building count multiples: add `building_multiples` plan param (map of building_name → step int, default empty = all 1); apply in building-count rounding in `BuildingDetails`; expose in a "Building Settings" panel in the UI with per-type numeric inputs; persist via existing save/fetch flow | I.plan-params, V39 |
| T83 | x      | Rework Building Settings panel (Show.vue): per-type row = blueprint size input + enable toggle; sizes read/write global LS key (e.g. `bp_sizes`); toggles read/write per-factory LS key (e.g. `bp_enabled:<factory_id>`); derive effective `building_multiples` plan param (enabled → size, else 1) on change + on mount; immutably update state | I.building-settings, I.local-storage, I.plan-params, V41, V42 |
| T84 | x      | Diagram grouping: when type grouping enabled, BuildDiagram renders count/X blueprint tiles at designer dims with `<monogram>x<X>` label; individual building outlines hidden; footprint stats (foundations/walls/offsets/rows) recomputed against blueprint tile dims; toggle off = current render path untouched | I.build-diagram, V41, V43 |
| T85 | .      | Designer mk selector (Mk.1/2/3, global LS, default Mk.1); clamp blueprint size to 1–40 (input min/max + clamp on LS read); no fit warning | I.building-settings, I.local-storage, V43 |
| T86 | .      | Tests: LS persistence round-trip (sizes global, toggles per-factory), effective-multiple derivation, grouped tile count/label, size clamp 1–40, even-rows superseded when multiple > 1; backend `building_multiples` rounding already covered by T82 tests | V41, V42, V43, V44 |
| T87 | .      | Backend: skip even-rows branch in `BuildingDetails::getBuildingDetails()` when `$multiple > 1` (both `even` param and `building_delta` auto-trigger paths); recompute rows/buildings_per_row against grouped count instead | I.plan-params, V44 |

## §B — Bug Log

| id | date | cause | fix |
|----|------|-------|-----|
| B1 | 2026-06-08 | inertia-laravel ^0.6.x caps PHP ~8.3.0; blocked composer update on PHP 8.4 | V9 |
| B2 | 2026-06-08 | 56 of 88 Unit tests were already failing on L9 (MySQL access denied + pre-existing logic bugs) | V2 |
| B3 | 2026-06-08 | L11 upgrade: `assertDeleted` removed; phpunit.xml needed APP_KEY for EncryptionServiceProvider | V2 |
| B4 | 2026-06-08 | ProductionGlobals::$used_byproducts typed as Collection but constructor assigns raw array — missing collect() wrap | V2 |
| B5 | 2026-06-08 | RecipeProductionTest + ProductionStepTest force `database.default=mysql` overriding phpunit.xml sqlite_testing | V2 |
| B6 | 2026-06-08 | DatabaseSeeder only calls FicsmasIngredientSeeder+FicsmasRecipeSeeder; FicsmasRecipeSeeder calls Building::ofName() which returns null (no buildings seeded) | V2 |
| B7 | 2026-06-08 | PHPUnit 11 deprecation: all test files still use @test/@dataProvider doc annotations | V2 |
| B8 | 2026-06-08 | RecipeEnergyTest iron plate/wire — energyStage() variable shadowing masked by DB issue; formula equivalence not established | V15 |
| B9 | 2026-06-08 | .env missing from repo clone → PHP Dotenv warning output → PHPUnit risky tests | V2 |
| B10 | 2026-06-08 | BuildingDetails.php even-rows branch: wrong energy_per_item formula gives ~6× too small value | V15 |
| B11 | 2026-06-08 | raw_materials.php stores MW/(items/min) without ×60; Mk.2 config comment said 12 MW, wiki says 15 MW | V15 |
| B12 | 2026-06-09 | ProductionCalculator.php:48 cache key `'production_calc_ '` has trailing space | T32 |
| B13 | 2026-06-09 | Cross-user cache contamination: favorites=null baked into shared cached production result | V16 |
| B14 | 2026-06-09 | GuestFactories/GuestMultiFactories/GuestFavorites Redis hashes have no TTL | V18 |
| B15 | 2026-06-09 | FlushProductionCache::Redis::select(1) mutates shared phpredis connection for worker lifetime | V17 |
| B16 | 2026-06-09 | FlushProductionCache uses Redis::keys() — O(N) blocking scan | V17 |
| B17 | 2026-06-09 | No cache invalidation on Recipe/Ingredient model save | T35 |
| B18 | 2026-06-09 | BuildingDetails.php even-rows branch updates clock_speed but not energy_per_item/total_energy | V15 |
| B19 | 2026-06-09 | b() helper calls Building::whereName() on every invocation with no caching | T33 |
| B20 | 2026-06-10 | RecipeSeeder Iron Alloy Ingot has v1.0 rates (50/min, 20+20); v1.1 changed to 75/min (40 iron + 10 copper) | V22, T48 |
| B21 | 2026-06-10 | RecipeSeeder Copper Alloy Ingot has v1.0 iron ore rate (25/min); v1.1 changed to 50/min | V23, T49 |
| B22 | 2026-06-10 | Entire Tier 9 quantum endgame chain absent from seeders: no Converter/Quantum Encoder buildings, no SAM processing recipes, no Diamond/Time Crystal/Dark Matter chain, no Quantum Encoder recipes — app was last updated before Satisfactory v1.0 endgame content; all data exists in UpdateOneZero.php helper but never backported | V21, T51–T64 |
| B23 | 2026-06-10 | IngredientSeeder uses legacy names `'SAM Ore'` and `'Green Power Slug'`; canonical names are `'SAM'` and `'Blue Power Slug'` per UpdateOneZero cleanup + Ingredient enum values | V21, T50 |
| B24 | 2026-06-10 | Smokeless Powder added to IngredientSeeder but no Refinery recipe seeded; ProductionTree iterates all processed ingredients and throws ErrorException | T59, T71 |
| B25 | 2026-06-10 | Dissolved Silica added to IngredientSeeder but no base recipe; only appears as byproduct of Quartz Purification; fix: add Refinery entry with Quartz Crystal as byproduct | T71 |
| B26 | 2026-06-10 | energyStage() finds Converter recipes for raw ores (Copper Ore, Iron Ore, etc.) after T68 backport and returns 500 MW Converter cost instead of config mining cost; fix: check isRaw() before recipe lookup | V15, T71 |
| B27 | 2026-06-10 | FuelGenerator fuel map has duplicate key `IONIZED_FUEL` — second entry (15000/5000) silently shadows first; Rocket Fuel absent from generator; packaged liquid fuel variants not listed | V31, T72 |
| B28 | 2026-06-10 | T69 created standalone `FicsoniumFuelRod` generator class — conceptually wrong (it's a fuel, not a building type) and never registered in `PowerPlanner::$options`; `NuclearPowerPlant` already has Ficsonium Fuel Rod as a fuel entry; orphaned class deleted | V30 |
| B29 | 2026-06-10 | `saveMyFactory` PATCH sends only `newChoices` (overridden-from-default recipes) — sub-recipe choices set during the session that happen to match defaults are not persisted; factory reload reverts to default recipe tree — issue #4 | V32, T73 |
| B30 | 2026-06-10 | Multi-output Update: second output added via "Add Output" and fully selected still disappears after fetch — Inertia full-page navigation recreates component; `data()` `outputs` initialized from server `multi` prop which may only include the first output if the fetch dropped the second | V33, T74 |
| B31 | 2026-06-10 | Scale Up Factory scales all outputs proportionally — per-output independent scale not feasible (outputs share ingredient graph); issue #6 closed won't-fix | T75 |
| B32 | 2026-06-12 | Somersloops inert on default recipes: backend keyed `request('somersloops')` lookups via `getDescription()` ('default' fallback) in CalculatesSteps/Getters/ParsesSteps; frontend sends `name\|name`. Test was green because it used backend-shaped key `Iron Ingot\|default` that frontend never emits | V40 |
