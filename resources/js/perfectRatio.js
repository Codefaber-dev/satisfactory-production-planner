// Perfect Ratio Factory mode (§V56 / T96).
//
// Scale every output by the smallest whole factor S that makes every production
// step's machine count a whole integer at 100% clock. S = LCM of the
// denominators of each step's exact fractional machine demand.
//
// Pure functions, no DOM/Vue — unit-testable in isolation.

const EPSILON = 1e-6;

export function gcd(a, b) {
    let x = Math.abs(a);
    let y = Math.abs(b);
    while (y) {
        [x, y] = [y, x % y];
    }
    return x;
}

export function lcm(a, b) {
    if (a === 0 || b === 0) {
        return 0;
    }
    return Math.abs((a / gcd(a, b)) * b);
}

// Rationalize a float to { num, den } in lowest terms using a continued-fraction
// expansion, capped at maxDenom. Avoids a fraction.js dependency. The cap also
// bounds the error: a value that needs a larger denominator is approximated by
// its best convergent at/under the cap.
export function floatToFraction(x, maxDenom = 1000) {
    if (!Number.isFinite(x)) {
        return { num: 0, den: 1 };
    }

    const sign = x < 0 ? -1 : 1;
    const value = Math.abs(x);

    // h/k are successive convergents; track the previous pair.
    let h0 = 0;
    let h1 = 1;
    let k0 = 1;
    let k1 = 0;
    let b = value;

    for (let i = 0; i < 64; i++) {
        const a = Math.floor(b);
        const h2 = a * h1 + h0;
        const k2 = a * k1 + k0;

        if (k2 > maxDenom) {
            break;
        }

        h0 = h1;
        h1 = h2;
        k0 = k1;
        k1 = k2;

        const frac = b - a;
        if (frac < EPSILON) {
            break;
        }
        b = 1 / frac;
    }

    const num = sign * h1;
    const den = k1 || 1;
    const g = gcd(num, den) || 1;
    return { num: num / g, den: den / g };
}

// Given each producing step's exact fractional machine count (at 100% clock),
// compute the Perfect Ratio scale factor S and whether it is safe to apply.
//
// Each step may be a bare number (ungrouped, X=1) or { count, multiple } where
// multiple = the step's effective building_multiple X (applied/snapshot, T88).
// Blueprint-aware (V81): S clears the denominator of count/X for every step, so a
// grouped step's scaled num_buildings (count·S) lands on a whole STAMP (multiple
// of X); X=1 reduces to the V56 whole-building result (byte-identical).
//
// caps: { ratioCap, buildingCap } — block (warn, do not apply) when S exceeds
// ratioCap or any resulting machine count exceeds buildingCap (V56 guard).
//
// returns { ratio, blocked, reason, maxBuildings }
export function computePerfectRatio(steps, { ratioCap = 1000, buildingCap = 5000 } = {}) {
    const normalized = (steps || [])
        .map((s) => (typeof s === 'number' ? { count: s, multiple: 1 } : s))
        .filter((s) => s && Number.isFinite(s.count) && s.count > EPSILON);

    if (normalized.length === 0) {
        return { ratio: 1, blocked: false, reason: null, maxBuildings: 0 };
    }

    let ratio = 1;
    for (const { count, multiple } of normalized) {
        const X = Math.max(1, multiple || 1);
        const { den } = floatToFraction(count / X, ratioCap);
        ratio = lcm(ratio, den);

        if (ratio > ratioCap || ratio === 0) {
            return { ratio, blocked: true, reason: 'ratio', maxBuildings: Number.POSITIVE_INFINITY };
        }
    }

    const maxBuildings = Math.max(...normalized.map(({ count }) => Math.round(count * ratio)));
    if (maxBuildings > buildingCap) {
        return { ratio, blocked: true, reason: 'buildings', maxBuildings };
    }

    return { ratio, blocked: false, reason: null, maxBuildings };
}
