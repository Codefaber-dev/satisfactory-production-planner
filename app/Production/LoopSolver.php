<?php

namespace App\Production;

/**
 * Stage 2 of circular-dependency resolution (V58): given the precomputed
 * catalog (LoopCatalog) and the plan's recipe selection, find the active loops
 * and solve each as an exact linear system instead of forcing a substitute
 * recipe. Pure math — unit-testable without a database.
 */
class LoopSolver
{
    /**
     * Solve the balance system A·x = demand for the cluster's recipe run-rates
     * (machine-equivalents at 100%). Each member is produced by exactly one
     * selected recipe. Returns product => run-rate, or null when the loop is
     * unsolvable (singular matrix) or non-productive (any run-rate ≤ 0).
     *
     * @param  array<int, string>  $members
     * @param  array<string, array{base_per_min: float, inputs: array<string, float>}>  $recipes
     * @param  array<string, float>  $demand
     * @return array<string, float>|null
     */
    public static function solve(array $members, array $recipes, array $demand): ?array
    {
        $n = count($members);
        $idx = array_flip($members);

        $a = array_fill(0, $n, array_fill(0, $n, 0.0));
        $b = array_fill(0, $n, 0.0);

        foreach ($members as $i => $product) {
            // own recipe produces the member at its base rate
            $a[$i][$i] += (float) $recipes[$product]['base_per_min'];
            $b[$i] = (float) ($demand[$product] ?? 0);
        }

        // subtract cross-consumption: recipe j consumes member i
        foreach ($members as $j => $producer) {
            foreach ($recipes[$producer]['inputs'] ?? [] as $ingredient => $rate) {
                if (isset($idx[$ingredient])) {
                    $a[$idx[$ingredient]][$j] -= (float) $rate;
                }
            }
        }

        $x = static::gaussianSolve($a, $b);

        if ($x === null) {
            return null;
        }

        $result = [];
        foreach ($members as $i => $product) {
            if ($x[$i] <= 1e-9) {
                return null; // non-productive loop
            }
            $result[$product] = $x[$i];
        }

        return $result;
    }

    /**
     * Gaussian elimination with partial pivoting. Returns null if singular.
     *
     * @param  array<int, array<int, float>>  $a
     * @param  array<int, float>  $b
     * @return array<int, float>|null
     */
    protected static function gaussianSolve(array $a, array $b): ?array
    {
        $n = count($b);

        for ($col = 0; $col < $n; $col++) {
            // partial pivot: largest magnitude in this column at/below the diagonal
            $pivot = $col;
            for ($row = $col + 1; $row < $n; $row++) {
                if (abs($a[$row][$col]) > abs($a[$pivot][$col])) {
                    $pivot = $row;
                }
            }

            if (abs($a[$pivot][$col]) < 1e-12) {
                return null; // singular
            }

            if ($pivot !== $col) {
                [$a[$col], $a[$pivot]] = [$a[$pivot], $a[$col]];
                [$b[$col], $b[$pivot]] = [$b[$pivot], $b[$col]];
            }

            for ($row = $col + 1; $row < $n; $row++) {
                $factor = $a[$row][$col] / $a[$col][$col];
                for ($k = $col; $k < $n; $k++) {
                    $a[$row][$k] -= $factor * $a[$col][$k];
                }
                $b[$row] -= $factor * $b[$col];
            }
        }

        // back-substitution
        $x = array_fill(0, $n, 0.0);
        for ($row = $n - 1; $row >= 0; $row--) {
            $sum = $b[$row];
            for ($k = $row + 1; $k < $n; $k++) {
                $sum -= $a[$row][$k] * $x[$k];
            }
            $x[$row] = $sum / $a[$row][$row];
        }

        return $x;
    }
}
