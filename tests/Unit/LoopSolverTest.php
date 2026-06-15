<?php

namespace Tests\Unit;

use App\Production\LoopSolver;
use PHPUnit\Framework\TestCase;

class LoopSolverTest extends TestCase
{
    public function test_solves_2x2_plastic_rubber_run_rates(): void
    {
        $members = ['Plastic', 'Rubber'];
        $recipes = [
            'Plastic' => ['base_per_min' => 60, 'inputs' => ['Rubber' => 30, 'Heavy Oil Residue' => 30]],
            'Rubber' => ['base_per_min' => 60, 'inputs' => ['Plastic' => 30, 'Heavy Oil Residue' => 30]],
        ];
        $demand = ['Plastic' => 40, 'Rubber' => 40];

        $x = LoopSolver::solve($members, $recipes, $demand);

        // gross 80 each → run-rate (machine-equivalents) = 80/60 = 4/3
        $this->assertEqualsWithDelta(4 / 3, $x['Plastic'], 1e-9);
        $this->assertEqualsWithDelta(4 / 3, $x['Rubber'], 1e-9);
    }

    public function test_asymmetric_demand_solves_correctly(): void
    {
        $members = ['Plastic', 'Rubber'];
        $recipes = [
            'Plastic' => ['base_per_min' => 60, 'inputs' => ['Rubber' => 30]],
            'Rubber' => ['base_per_min' => 60, 'inputs' => ['Plastic' => 30]],
        ];
        // demand 30/30 → symmetric: gross 60 each → run-rate 1.0
        $x = LoopSolver::solve($members, $recipes, ['Plastic' => 30, 'Rubber' => 30]);

        $this->assertEqualsWithDelta(1.0, $x['Plastic'], 1e-9);
        $this->assertEqualsWithDelta(1.0, $x['Rubber'], 1e-9);
    }

    public function test_singular_matrix_returns_null(): void
    {
        $members = ['Plastic', 'Rubber'];
        $recipes = [
            'Plastic' => ['base_per_min' => 30, 'inputs' => ['Rubber' => 30]],
            'Rubber' => ['base_per_min' => 30, 'inputs' => ['Plastic' => 30]],
        ];

        $this->assertNull(LoopSolver::solve($members, $recipes, ['Plastic' => 40, 'Rubber' => 40]));
    }

    public function test_non_productive_loop_returns_null(): void
    {
        // each recipe eats more of the other than it makes → loop can't net-produce
        $members = ['Plastic', 'Rubber'];
        $recipes = [
            'Plastic' => ['base_per_min' => 30, 'inputs' => ['Rubber' => 60]],
            'Rubber' => ['base_per_min' => 30, 'inputs' => ['Plastic' => 60]],
        ];

        $this->assertNull(LoopSolver::solve($members, $recipes, ['Plastic' => 40, 'Rubber' => 40]));
    }

    /**
     * Mass-balance / conservation: the solved run-rates must produce EXACTLY the
     * net demand for every member (production − internal consumption = demand).
     * This is the ground-truth-independent correctness proof — no external planner
     * solves recycled loops (they force a base recipe), so conservation is the check.
     */
    public function test_solution_conserves_mass_and_meets_net_demand(): void
    {
        $members = ['Plastic', 'Rubber'];
        $recipes = [
            'Plastic' => ['base_per_min' => 60, 'inputs' => ['Rubber' => 30, 'Heavy Oil Residue' => 30]],
            'Rubber' => ['base_per_min' => 60, 'inputs' => ['Plastic' => 30, 'Heavy Oil Residue' => 30]],
        ];

        foreach ([['Plastic' => 0, 'Rubber' => 100], ['Plastic' => 20, 'Rubber' => 50], ['Plastic' => 75, 'Rubber' => 75]] as $demand) {
            $x = LoopSolver::solve($members, $recipes, $demand);

            $this->assertNotNull($x, 'expected a solution');

            $net = $this->netProduction($members, $recipes, $x);
            foreach ($demand as $member => $wanted) {
                $this->assertEqualsWithDelta($wanted, $net[$member], 1e-6, "net {$member} must equal demand");
            }
        }
    }

    /**
     * Net production per member from run-rates: base_per_min·x − Σ (internal
     * consumption by every member's recipe).
     */
    private function netProduction(array $members, array $recipes, array $x): array
    {
        $net = [];
        foreach ($members as $member) {
            $net[$member] = $recipes[$member]['base_per_min'] * $x[$member];
        }

        foreach ($members as $producer) {
            foreach ($recipes[$producer]['inputs'] as $ingredient => $rate) {
                if (isset($net[$ingredient])) {
                    $net[$ingredient] -= $rate * $x[$producer];
                }
            }
        }

        return $net;
    }
}
