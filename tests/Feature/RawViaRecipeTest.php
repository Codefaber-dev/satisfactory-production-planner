<?php

namespace Tests\Feature;

use App\Production\ProductionCalculator;
use App\Production\Step;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T103 / V61 / V63 / V58: raw-via-recipe source modes.
 *
 * `convert` and `unpackage` turn a raw ingredient from a tree leaf into a
 * recipe-bearing step that recurses into its own inputs (each carrying its own
 * source mode). raw↔raw convert loops are resolved by the V58 linear solver,
 * never by forcing a substitute recipe.
 */
class RawViaRecipeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--env' => 'testing']);
        Cache::flush();
    }

    /** Find a step by product name anywhere in the tree. */
    protected function findStep(Step $step, string $name): ?Step
    {
        if ($step->getName() === $name) {
            return $step;
        }

        foreach ($step->getChildren() ?? [] as $child) {
            if ($found = $this->findStep($child, $name)) {
                return $found;
            }
        }

        return null;
    }

    #[Test]
    public function convert_mode_sources_a_raw_via_its_converter_recipe(): void
    {
        // V61/V79: Coal in `convert` mode; the recipe ("Coal (Iron)") is chosen via
        // the main picker → choices, not raw_sources. Produced from Iron Ore +
        // Reanimated SAM (the recipe's own inputs).
        request()->merge(['raw_sources' => [
            'Coal' => ['mode' => 'convert'],
        ]]);

        $production = ProductionCalculator::make(
            product: 'Steel Ingot',
            qty: 45,
            choices: ['Coal' => r('Coal (Iron)')],
        );

        $coal = $this->findStep($production->getSteps(), 'Coal');

        $this->assertNotNull($coal, 'Coal should be a step in the tree.');
        $this->assertTrue(optional($coal->getRecipe())->is(r('Coal (Iron)')),
            'Coal should be produced via the Coal (Iron) converter recipe.');

        $children = $coal->getChildren()->map(fn ($c) => $c->getName())->all();
        $this->assertContains('Iron Ore', $children);
        $this->assertContains('Reanimated SAM', $children);
    }

    #[Test]
    public function convert_mode_with_no_choice_defaults_the_recipe_and_recurses(): void
    {
        // V79: a convert raw with no main-picker choice defaults to the first
        // Converter recipe producing it, becoming a recipe-bearing step that recurses.
        request()->merge(['raw_sources' => [
            'Coal' => ['mode' => 'convert'],
        ]]);

        $production = ProductionCalculator::make(product: 'Steel Ingot', qty: 45);

        $coal = $this->findStep($production->getSteps(), 'Coal');

        $this->assertNotNull($coal, 'Coal should be a step in the tree.');

        $recipe = $coal->getRecipe();
        $this->assertNotNull($recipe, 'Coal should be recipe-bearing after defaulting.');
        $this->assertStringStartsNotWith('Unpackage', $recipe->description ?? '');
        $this->assertSame('Coal', $recipe->product->name);

        // recurses into the default recipe's own inputs (not a leaf)
        $this->assertNotEmpty($coal->getChildren(), 'Coal should recurse into its recipe inputs.');
        $this->assertContains('Reanimated SAM', $coal->getChildren()->map(fn ($c) => $c->getName())->all());
    }

    #[Test]
    public function mutual_convert_loop_is_solved_not_forced(): void
    {
        // V61/V58: a raw↔raw convert loop is resolved by the linear solver, keeping
        // the user's selected recipes — NOT by forcing a substitute recipe.
        //
        // Seed has no real Iron⇄Coal 2-cycle (no "Iron Ore (Coal)" recipe), so the
        // genuine convert cycle is Iron Ore ⇄ Limestone ⇄ Sulfur:
        //   Iron Ore (Limestone): Iron Ore ← Limestone + Reanimated SAM
        //   Limestone (Sulfur):   Limestone ← Sulfur + Reanimated SAM
        //   Sulfur (Iron):        Sulfur ← Iron Ore + Reanimated SAM
        request()->merge(['raw_sources' => [
            'Iron Ore' => ['mode' => 'convert'],
            'Limestone' => ['mode' => 'convert'],
            'Sulfur' => ['mode' => 'convert'],
        ]]);

        $production = ProductionCalculator::make(
            product: 'Iron Ingot',
            qty: 20,
            choices: [
                'Iron Ore' => r('Iron Ore (Limestone)'),
                'Limestone' => r('Limestone (Sulfur)'),
                'Sulfur' => r('Sulfur (Iron)'),
            ],
        );
        $steps = $production->getSteps();

        // selected convert recipes are kept (not swapped to a loop-free fallback)
        $steps->assertIntermediateRecipe('Iron Ore', 'Iron Ore (Limestone)');
        $steps->assertIntermediateRecipe('Limestone', 'Limestone (Sulfur)');
        $steps->assertIntermediateRecipe('Sulfur', 'Sulfur (Iron)');

        // no forced override on any loop member (V58: solved, not forced)
        $overrides = $steps->getOverrides()->keys()->all();
        $this->assertNotContains('Iron Ore', $overrides);
        $this->assertNotContains('Limestone', $overrides);
        $this->assertNotContains('Sulfur', $overrides);

        // loop solved cleanly → no unsolvable/auto-source warning
        foreach ($production->getLoopWarnings() as $warning) {
            $this->assertStringNotContainsString('Unsolvable', $warning);
            $this->assertStringNotContainsString('Auto-', $warning);
        }

        // solver gross: 20 net Iron Ore needs 120 gross (100 consumed internally by
        // the Sulfur recipe). x_I = 1 → 120/min Iron Ore, 240 Limestone, 40 Sulfur.
        $iron = $this->findStep($steps, 'Iron Ore');
        $this->assertEqualsWithDelta(120, $iron->getQty(), 0.001);
    }

    #[Test]
    public function unpackage_mode_consumes_packaged_form_and_emits_empty_container(): void
    {
        // V63(a): Water in `unpackage` mode is produced via the "Unpackage Water"
        // Packager recipe — it consumes Packaged Water (the Packaged input defaults
        // to import) and returns the Empty Canister container as a byproduct.
        // V79: no recipe in raw_sources — unpackage defaults the choice to the
        // Unpackage recipe (by name) when none is set.
        request()->merge(['raw_sources' => [
            'Water' => ['mode' => 'unpackage'],
        ]]);

        // Wet Concrete consumes Water (+ Limestone), so it pulls Water into the tree.
        $production = ProductionCalculator::make(product: 'Concrete', qty: 30, recipe: 'Wet Concrete');
        $steps = $production->getSteps();

        $water = $this->findStep($steps, 'Water');

        $this->assertNotNull($water, 'Water should be a step in the tree.');
        $this->assertTrue(optional($water->getRecipe())->is(r('Unpackage Water')),
            'Water should be produced via the Unpackage Water recipe.');

        // Packaged Water input is present and imported (V63 default)
        $packaged = $this->findStep($water, 'Packaged Water');
        $this->assertNotNull($packaged, 'Packaged Water should be an input of Unpackage Water.');
        $this->assertTrue($packaged->isImported(), 'Packaged Water defaults to import.');

        // Empty Canister emitted as a byproduct
        $byproducts = $water->getByproducts();
        $this->assertNotNull($byproducts);
        $this->assertArrayHasKey('Empty Canister', $byproducts->all());
    }

    #[Test]
    public function a_raw_output_is_produced_via_its_chosen_unpackage_recipe(): void
    {
        // V96 / B54: a raw (Water) chosen as the OUTPUT with the Unpackage Water recipe
        // is produced via that recipe — not forced to a raw leaf (which rendered blank).
        $production = ProductionCalculator::make(
            product: 'Water',
            qty: 60,
            recipe: 'Unpackage Water',
        );

        $root = $production->getSteps();

        $this->assertSame('Water', $root->getName());
        $this->assertNotNull($root->getRecipe(), 'A raw output must carry its chosen recipe, not be a leaf.');
        $this->assertTrue(optional($root->getRecipe())->is(r('Unpackage Water')));

        // expands into the Unpackage recipe inputs (Packaged Water) + byproduct (Empty Canister)
        $children = $root->getChildren()->map(fn ($c) => $c->getName())->all();
        $this->assertContains('Packaged Water', $children);
        $this->assertArrayHasKey('Empty Canister', $root->getByproducts()->all());
    }

    #[Test]
    public function a_raw_output_with_no_recipe_stays_a_leaf(): void
    {
        // V96: a raw output with NO chosen producing recipe still leafs (extract/import).
        $production = ProductionCalculator::make(product: 'Water', qty: 60);

        $root = $production->getSteps();

        $this->assertSame('Water', $root->getName());
        $this->assertNull($root->getRecipe(), 'A raw output with no chosen recipe stays a leaf.');
        $this->assertEmpty($root->getChildren() ?? []);
    }

    #[Test]
    public function import_mode_ignores_stale_convert_choice(): void
    {
        // V104 / B58: a stale choices[raw] entry (left over from a convert dalliance,
        // a saved factory, or a crafted URL) must NOT make an import-mode raw
        // recipe-bearing. Import mode = leaf, byte-identical to a fresh import.
        request()->merge(['raw_sources' => [
            'Coal' => ['mode' => 'import'],
        ]]);

        $production = ProductionCalculator::make(
            product: 'Steel Ingot',
            qty: 45,
            choices: ['Coal' => r('Coal (Iron)')],
        );

        $coal = $this->findStep($production->getSteps(), 'Coal');

        $this->assertNotNull($coal, 'Coal should be a step in the tree.');
        $this->assertNull($coal->getRecipe(), 'Import-mode Coal must be a leaf despite the stale choice.');
        $this->assertEmpty($coal->getChildren() ?? [], 'Import-mode Coal must not recurse.');
    }

    #[Test]
    public function absent_raw_source_entry_ignores_stale_convert_choice(): void
    {
        // V104 / V59: no raw_sources entry at all = default import → same leaf
        // behavior; the stale choice alone must not flip the raw to convert.
        $production = ProductionCalculator::make(
            product: 'Steel Ingot',
            qty: 45,
            choices: ['Coal' => r('Coal (Iron)')],
        );

        $coal = $this->findStep($production->getSteps(), 'Coal');

        $this->assertNotNull($coal, 'Coal should be a step in the tree.');
        $this->assertNull($coal->getRecipe(), 'Default-import Coal must be a leaf despite the stale choice.');
        $this->assertEmpty($coal->getChildren() ?? []);
    }

    #[Test]
    public function extract_mode_ignores_stale_convert_choice(): void
    {
        // V104: extract mode is also a leaf (tier-0 extractor, no recipe) — the
        // stale convert choice must not override it.
        request()->merge(['raw_sources' => [
            'Coal' => ['mode' => 'extract', 'purity' => 'normal', 'miner' => 'mk2', 'shards' => 0],
        ]]);

        $production = ProductionCalculator::make(
            product: 'Steel Ingot',
            qty: 45,
            choices: ['Coal' => r('Coal (Iron)')],
        );

        $coal = $this->findStep($production->getSteps(), 'Coal');

        $this->assertNotNull($coal, 'Coal should be a step in the tree.');
        $this->assertNull($coal->getRecipe(), 'Extract-mode Coal must be a leaf despite the stale choice.');
        $this->assertEmpty($coal->getChildren() ?? []);
    }

    #[Test]
    public function mode_round_trip_convert_to_import_restores_the_leaf(): void
    {
        // V104: convert run honors the choice; flipping back to import with the
        // same choice still present restores the plain leaf (mode gate, not
        // choice presence, decides).
        request()->merge(['raw_sources' => [
            'Coal' => ['mode' => 'convert'],
        ]]);

        $convert = ProductionCalculator::make(
            product: 'Steel Ingot',
            qty: 45,
            choices: ['Coal' => r('Coal (Iron)')],
        );

        $coal = $this->findStep($convert->getSteps(), 'Coal');
        $this->assertTrue(optional($coal->getRecipe())->is(r('Coal (Iron)')),
            'Convert-mode Coal honors the chosen converter recipe.');

        request()->merge(['raw_sources' => [
            'Coal' => ['mode' => 'import'],
        ]]);

        $import = ProductionCalculator::make(
            product: 'Steel Ingot',
            qty: 45,
            choices: ['Coal' => r('Coal (Iron)')],
        );

        $coal = $this->findStep($import->getSteps(), 'Coal');
        $this->assertNull($coal->getRecipe(), 'Flipping back to import restores the leaf.');
        $this->assertEmpty($coal->getChildren() ?? []);
    }

    #[Test]
    public function a_self_looping_unpackage_output_imports_the_packaged_input_not_the_output(): void
    {
        // V97 / B55: Unpackage Alumina Solution (non-raw fluid) self-loops with its
        // packaged form (Alumina Solution ⇄ Packaged Alumina Solution, no loop-free
        // alternate). The loop must break by importing the PACKAGED input — never the
        // requested output, which would mark it imported and blank the plan.
        $production = ProductionCalculator::make(
            product: 'Alumina Solution',
            qty: 60,
            recipe: 'Unpackage Alumina Solution',
        );

        $root = $production->getSteps();

        $this->assertSame('Alumina Solution', $root->getName());
        $this->assertFalse($root->isImported(), 'The requested output must never be imported.');
        $this->assertTrue(optional($root->getRecipe())->is(r('Unpackage Alumina Solution')));

        $packaged = $this->findStep($root, 'Packaged Alumina Solution');
        $this->assertNotNull($packaged, 'Packaged Alumina Solution should be an input.');
        $this->assertTrue($packaged->isImported(), 'The loop breaks by importing the packaged input.');
    }
}
