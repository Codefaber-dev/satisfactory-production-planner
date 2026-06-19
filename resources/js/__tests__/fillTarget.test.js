import { describe, it, expect } from 'vitest';
import { fillTargetQty } from '../Pages/Production/fillTarget';

// T122/V80: blueprint-aware Fill to 100%. The fill yield must round the selected
// output's own machine count up to a whole STAMP (multiple of X) at 100% clock —
// ratio = ceil(machines/X)·X / machines (X=1 ⇒ V55's ceil(machines)/machines).
describe('fillTargetQty — T122/V80', () => {
    it('grouped (X=4) fills to a multiple-of-4 building count @100%', () => {
        // 5.2 exact machines, qty 30 → target 8 buildings (ceil(5.2/4)*4)
        const qty = fillTargetQty({ qty: 30, exactBuildings: 5.2, multiple: 4 });
        // newQty must back-solve to exactly 8 whole machines @100%
        expect(qty / (30 / 5.2)).toBeCloseTo(8, 6);
    });

    it('grouped already-full stamp (8 machines, X=4) is a no-op', () => {
        expect(fillTargetQty({ qty: 240, exactBuildings: 8, multiple: 4 })).toBeCloseTo(240, 6);
    });

    it('ungrouped (X=1) reduces to whole building count @100% (V55)', () => {
        // 5.2 machines → ceil 6; qty 30 → 30 * 6/5.2
        expect(fillTargetQty({ qty: 30, exactBuildings: 5.2, multiple: 1 })).toBeCloseTo(30 * 6 / 5.2, 6);
    });

    it('default multiple is 1 (ungrouped)', () => {
        expect(fillTargetQty({ qty: 30, exactBuildings: 5.2 })).toBeCloseTo(30 * 6 / 5.2, 6);
    });

    it('whole-machine ungrouped step is a no-op', () => {
        expect(fillTargetQty({ qty: 30, exactBuildings: 3, multiple: 1 })).toBeCloseTo(30, 6);
    });

    it('zero/absent machine count returns qty unchanged (safety)', () => {
        expect(fillTargetQty({ qty: 30, exactBuildings: 0, multiple: 4 })).toBe(30);
        expect(fillTargetQty({ qty: 30, exactBuildings: undefined, multiple: 4 })).toBe(30);
    });
});
