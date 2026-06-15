<?php

namespace Tests\Unit;

use App\Production\LoopSolver;
use PHPUnit\Framework\TestCase;

class LoopSolverTest extends TestCase
{
    private array $catalog = [
        [
            'members' => ['Plastic', 'Rubber'],
            'enabledBy' => [
                ['product' => 'Plastic', 'recipe' => 'Recycled Plastic'],
                ['product' => 'Rubber', 'recipe' => 'Recycled Rubber'],
            ],
        ],
    ];

    public function test_loop_active_when_selection_covers_enabling_set(): void
    {
        $selection = ['Plastic' => 'Recycled Plastic', 'Rubber' => 'Recycled Rubber'];

        $active = LoopSolver::activeLoops($this->catalog, $selection);

        $this->assertCount(1, $active);
        $this->assertEqualsCanonicalizing(['Plastic', 'Rubber'], $active[0]['members']);
    }

    public function test_loop_inactive_when_only_one_alt_selected(): void
    {
        $selection = ['Plastic' => 'Recycled Plastic', 'Rubber' => 'Rubber'];

        $this->assertSame([], LoopSolver::activeLoops($this->catalog, $selection));
    }

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
}
