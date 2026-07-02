<?php

namespace Tests\Feature;

use App\Production\ProductionCalculator;
use App\Production\Step;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * T106 / V63(b) / V58: non-raw fluid/gas unpackage path.
 *
 * The 9 non-raw intermediates that have a packaged form (Alumina Solution, Fuel,
 * Heavy Oil Residue, Ionized Fuel, Liquid Biofuel, Nitric Acid, Rocket Fuel,
 * Sulfuric Acid, Turbofuel) source themselves via their Unpackage recipe chosen
 * through the NORMAL recipe picker (`choices`), not `raw_sources`. Selecting it
 * consumes the Packaged form (imported, or produced) and emits the empty
 * container byproduct; the X ⇄ Packaged X loop is resolved by the V58
 * solver/source-injection (V69) — never by silently swapping the user's
 * deliberate Unpackage pick.
 */
class IntermediateUnpackageTest extends TestCase
{
    use RefreshDatabase;

    /** The 9 non-raw intermediates with a packaged form (V63 list, raws excluded). */
    private const INTERMEDIATES = [
        'Alumina Solution',
        'Fuel',
        'Heavy Oil Residue',
        'Ionized Fuel',
        'Liquid Biofuel',
        'Nitric Acid',
        'Rocket Fuel',
        'Sulfuric Acid',
        'Turbofuel',
    ];

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
    public function every_intermediate_exposes_a_selectable_unpackage_recipe(): void
    {
        // V63(b): each of the 9 intermediates has an "Unpackage <X>" recipe attached
        // to the product, so it shows up as a normal selectable recipe choice.
        foreach (self::INTERMEDIATES as $name) {
            $unpackage = i($name)->recipes
                ->first(fn ($recipe) => $recipe->description === "Unpackage {$name}");

            $this->assertNotNull($unpackage, "{$name} should expose an Unpackage {$name} recipe.");
            $this->assertSame($name, $unpackage->product->name);
        }
    }

    #[Test]
    public function selecting_unpackage_turbofuel_imports_packaged_input_and_emits_empty_canister(): void
    {
        // Rocket Fuel (base) consumes Turbofuel, pulling it into the tree. Choosing
        // Unpackage Turbofuel for it sources Turbofuel from Packaged Turbofuel.
        // Packaged Turbofuel has no loop-free alternate, so the degenerate
        // Turbofuel ⇄ Packaged Turbofuel loop is broken by auto-IMPORTING the
        // packaged member (V69) — the user's Unpackage pick is preserved.
        $production = ProductionCalculator::make(
            product: 'Rocket Fuel',
            qty: 100,
            choices: ['Turbofuel' => r('Unpackage Turbofuel')],
        );
        $steps = $production->getSteps();

        $turbofuel = $this->findStep($steps, 'Turbofuel');
        $this->assertNotNull($turbofuel, 'Turbofuel should be a step in the tree.');
        $this->assertTrue(optional($turbofuel->getRecipe())->is(r('Unpackage Turbofuel')),
            'Turbofuel must keep the user-selected Unpackage Turbofuel recipe (not be swapped).');

        // Packaged Turbofuel input present + imported (loop broken on the packaged side)
        $packaged = $this->findStep($turbofuel, 'Packaged Turbofuel');
        $this->assertNotNull($packaged, 'Packaged Turbofuel should be an input of Unpackage Turbofuel.');
        $this->assertTrue($packaged->isImported(), 'Packaged Turbofuel should be auto-imported (V69).');

        // Empty Canister emitted as a byproduct
        $byproducts = $turbofuel->getByproducts();
        $this->assertNotNull($byproducts);
        $this->assertArrayHasKey('Empty Canister', $byproducts->all());

        // informational auto-source note, not the circular-dependency alarm
        $this->assertNotEmpty($production->getLoopWarnings());
    }

    #[Test]
    public function selecting_unpackage_rocket_fuel_imports_packaged_input_and_emits_empty_fluid_tank(): void
    {
        // Ionized Fuel (base) consumes Rocket Fuel. Unpackage Rocket Fuel sources it
        // from Packaged Rocket Fuel (Empty Fluid Tank container). Packaged Rocket Fuel
        // has no loop-free alternate → auto-import; Unpackage pick preserved.
        $production = ProductionCalculator::make(
            product: 'Ionized Fuel',
            qty: 40,
            choices: ['Rocket Fuel' => r('Unpackage Rocket Fuel')],
        );
        $steps = $production->getSteps();

        $rocket = $this->findStep($steps, 'Rocket Fuel');
        $this->assertNotNull($rocket, 'Rocket Fuel should be a step in the tree.');
        $this->assertTrue(optional($rocket->getRecipe())->is(r('Unpackage Rocket Fuel')),
            'Rocket Fuel must keep the user-selected Unpackage Rocket Fuel recipe.');

        $packaged = $this->findStep($rocket, 'Packaged Rocket Fuel');
        $this->assertNotNull($packaged, 'Packaged Rocket Fuel should be an input of Unpackage Rocket Fuel.');
        $this->assertTrue($packaged->isImported(), 'Packaged Rocket Fuel should be auto-imported (V69).');

        $byproducts = $rocket->getByproducts();
        $this->assertNotNull($byproducts);
        $this->assertArrayHasKey('Empty Fluid Tank', $byproducts->all());

        // the user's explicit Unpackage pick must never be swapped to a fallback recipe
        $overrides = $steps->getOverrides()->keys()->all();
        $this->assertNotContains('Rocket Fuel', $overrides);
    }
}
