import { describe, it, expect } from 'vitest';
import { gcd, lcm, floatToFraction, computePerfectRatio } from '../perfectRatio';

describe('perfectRatio — gcd/lcm', () => {
    it('gcd', () => {
        expect(gcd(12, 8)).toBe(4);
        expect(gcd(7, 13)).toBe(1);
        expect(gcd(0, 5)).toBe(5);
    });

    it('lcm', () => {
        expect(lcm(2, 3)).toBe(6);
        expect(lcm(4, 6)).toBe(12);
        expect(lcm(0, 5)).toBe(0);
    });
});

describe('perfectRatio — floatToFraction', () => {
    it('exact integers', () => {
        expect(floatToFraction(2)).toEqual({ num: 2, den: 1 });
    });

    it('simple fractions', () => {
        expect(floatToFraction(1.5)).toEqual({ num: 3, den: 2 });
        expect(floatToFraction(0.25)).toEqual({ num: 1, den: 4 });
    });

    it('repeating decimals rationalize within denom cap', () => {
        expect(floatToFraction(1 / 3)).toEqual({ num: 1, den: 3 });
        expect(floatToFraction(2 / 3)).toEqual({ num: 2, den: 3 });
        expect(floatToFraction(4 + 1 / 6)).toEqual({ num: 25, den: 6 });
    });
});

describe('perfectRatio — computePerfectRatio (V56)', () => {
    it('every step becomes a whole machine count after scaling', () => {
        const counts = [1.5, 0.75, 2];
        const { ratio, blocked } = computePerfectRatio(counts);
        expect(blocked).toBe(false);
        // S must clear every denominator (2, 4, 1) → 4
        expect(ratio).toBe(4);
        for (const c of counts) {
            expect(Math.abs(c * ratio - Math.round(c * ratio))).toBeLessThan(1e-6);
        }
    });

    it('mutual-loop Plastic/Rubber fractional run-rates resolve to whole machines', () => {
        // representative solver output: thirds + halves
        const counts = [1 / 3, 1.5];
        const { ratio, blocked } = computePerfectRatio(counts);
        expect(blocked).toBe(false);
        expect(ratio).toBe(6); // lcm(3, 2)
        for (const c of counts) {
            expect(Number.isInteger(Math.round(c * ratio))).toBe(true);
            expect(Math.abs(c * ratio - Math.round(c * ratio))).toBeLessThan(1e-6);
        }
    });

    it('imported / zero-building steps are ignored', () => {
        const { ratio, blocked } = computePerfectRatio([0, 2, 1.5, 0]);
        expect(blocked).toBe(false);
        expect(ratio).toBe(2);
    });

    it('no producing steps → ratio 1, not blocked', () => {
        expect(computePerfectRatio([])).toEqual({ ratio: 1, blocked: false, reason: null, maxBuildings: 0 });
    });

    it('blocks when S exceeds the ratio cap (no apply)', () => {
        const res = computePerfectRatio([1 / 7, 1 / 11, 1 / 13], { ratioCap: 100 });
        expect(res.blocked).toBe(true);
        expect(res.reason).toBe('ratio');
    });

    it('blocks when resulting building count exceeds the cap (no apply)', () => {
        const res = computePerfectRatio([1.5, 2000], { buildingCap: 3000 });
        expect(res.blocked).toBe(true);
        expect(res.reason).toBe('buildings');
        expect(res.maxBuildings).toBeGreaterThan(3000);
    });
});

describe('perfectRatio — computePerfectRatio (V81 blueprint-aware)', () => {
    it('grouped step (X=4) scales to a whole stamp (num_buildings multiple of X) @100%', () => {
        // c=2.5, X=4 → c/X=0.625=5/8 → S clears den 8
        const { ratio, blocked } = computePerfectRatio([{ count: 2.5, multiple: 4 }]);
        expect(blocked).toBe(false);
        expect(ratio).toBe(8);
        const num_buildings = 2.5 * ratio; // 20
        expect(num_buildings % 4).toBe(0); // whole stamp
        expect(Number.isInteger(num_buildings)).toBe(true);
    });

    it('mixed grouped + ungrouped: grouped→multiple of X, ungrouped→integer', () => {
        const steps = [
            { count: 2, multiple: 4 }, // c/X=0.5 → den 2
            { count: 1.5, multiple: 1 }, // den 2
        ];
        const { ratio, blocked } = computePerfectRatio(steps);
        expect(blocked).toBe(false);
        expect(ratio).toBe(2);
        expect((2 * ratio) % 4).toBe(0); // 4 → 1 stamp
        expect(Number.isInteger(1.5 * ratio)).toBe(true); // 3
    });

    it('all X=1 (object form) equals the V56 number-array result', () => {
        const plain = computePerfectRatio([1.5, 0.75, 2]);
        const objects = computePerfectRatio([
            { count: 1.5, multiple: 1 },
            { count: 0.75, multiple: 1 },
            { count: 2, multiple: 1 },
        ]);
        expect(objects).toEqual(plain);
        expect(objects.ratio).toBe(4);
    });

    it('grouping that needs a larger S blocks at the ratio cap (no apply)', () => {
        // ungrouped these are whole (count=1 → S=1); grouping forces c/X = 1/7,1/11,1/13
        // → S = lcm(7,11,13) = 1001 > cap
        const steps = [
            { count: 1, multiple: 7 },
            { count: 1, multiple: 11 },
            { count: 1, multiple: 13 },
        ];
        expect(computePerfectRatio(steps.map((s) => s.count))).toMatchObject({ ratio: 1, blocked: false });
        const res = computePerfectRatio(steps, { ratioCap: 100 });
        expect(res.blocked).toBe(true);
        expect(res.reason).toBe('ratio');
    });

    it('mutual-loop grouped run-rates resolve to whole stamps', () => {
        // solver thirds + halves, both grouped X=2
        const steps = [
            { count: 1 / 3, multiple: 2 }, // (1/3)/2 = 1/6 → den 6
            { count: 1.5, multiple: 2 }, // 0.75 → den 4
        ];
        const { ratio, blocked } = computePerfectRatio(steps);
        expect(blocked).toBe(false);
        expect(ratio).toBe(12); // lcm(6, 4)
        expect(((1 / 3) * ratio) % 2).toBe(0); // 4
        expect((1.5 * ratio) % 2).toBe(0); // 18
    });
});
