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
}
