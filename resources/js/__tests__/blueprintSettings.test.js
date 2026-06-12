import { beforeEach, describe, expect, it } from 'vitest';
import {
    SIZE_MAX,
    SIZE_MIN,
    clampSize,
    effectiveMultiples,
    loadEnabled,
    loadSizes,
    saveEnabled,
    saveSizes,
} from '@/blueprintSettings';

const makeStore = () => {
    const data = {};

    return {
        getItem: (key, def = null) => (key in data ? data[key] : def),
        setItem: (key, value) => {
            data[key] = JSON.parse(JSON.stringify(value));
        },
        _data: data,
    };
};

describe('clampSize (V43)', () => {
    it('clamps below minimum to 1', () => {
        expect(clampSize(0)).toBe(SIZE_MIN);
        expect(clampSize(-5)).toBe(SIZE_MIN);
    });

    it('clamps above maximum to 40', () => {
        expect(SIZE_MAX).toBe(40);
        expect(clampSize(41)).toBe(40);
        expect(clampSize(999)).toBe(40);
    });

    it('passes valid sizes through as integers', () => {
        expect(clampSize(8)).toBe(8);
        expect(clampSize('10')).toBe(10);
        expect(clampSize(40)).toBe(40);
    });

    it('falls back to 1 on garbage input', () => {
        expect(clampSize('abc')).toBe(SIZE_MIN);
        expect(clampSize(undefined)).toBe(SIZE_MIN);
        expect(clampSize(null)).toBe(SIZE_MIN);
    });
});

describe('sizes persistence (V42 — global localStorage)', () => {
    let store;

    beforeEach(() => {
        store = makeStore();
    });

    it('round-trips sizes through the store', () => {
        saveSizes(store, { Constructor: 8, Smelter: 10 });

        expect(loadSizes(store)).toEqual({ Constructor: 8, Smelter: 10 });
    });

    it('defaults to empty object on fresh store', () => {
        expect(loadSizes(store)).toEqual({});
    });

    it('clamps out-of-range sizes on read', () => {
        saveSizes(store, { Constructor: 99, Smelter: 0 });

        expect(loadSizes(store)).toEqual({ Constructor: 40, Smelter: 1 });
    });

    it('uses a factory-independent key', () => {
        saveSizes(store, { Constructor: 8 });

        expect(store._data).toHaveProperty('bp_sizes');
    });
});

describe('enabled persistence (V42 — per-factory localStorage)', () => {
    let store;

    beforeEach(() => {
        store = makeStore();
    });

    it('round-trips toggles through the store', () => {
        saveEnabled(store, 7, { Constructor: true, Smelter: false });

        expect(loadEnabled(store, 7)).toEqual({ Constructor: true, Smelter: false });
    });

    it('keys toggles by factory id — factories do not share toggles', () => {
        saveEnabled(store, 7, { Constructor: true });

        expect(loadEnabled(store, 8)).toEqual({});
        expect(loadEnabled(store, 7)).toEqual({ Constructor: true });
    });

    it('falls back to a "new" bucket without a factory id', () => {
        saveEnabled(store, undefined, { Smelter: true });

        expect(loadEnabled(store, undefined)).toEqual({ Smelter: true });
        expect(store._data).toHaveProperty('bp_enabled:new');
    });

    it('defaults to all-off on fresh store', () => {
        expect(loadEnabled(store, 7)).toEqual({});
    });
});

describe('effectiveMultiples (V41 — enabled → size, else 1)', () => {
    it('maps enabled types to their blueprint size', () => {
        const multiples = effectiveMultiples({ Constructor: 8, Smelter: 10 }, { Constructor: true, Smelter: true });

        expect(multiples).toEqual({ Constructor: 8, Smelter: 10 });
    });

    it('omits disabled types (backend defaults them to 1)', () => {
        const multiples = effectiveMultiples({ Constructor: 8, Smelter: 10 }, { Constructor: true, Smelter: false });

        expect(multiples).toEqual({ Constructor: 8 });
    });

    it('returns empty map when nothing is enabled (fresh browser, V42)', () => {
        expect(effectiveMultiples({}, {})).toEqual({});
    });

    it('uses size 1 for enabled types without a stored size', () => {
        expect(effectiveMultiples({}, { Foundry: true })).toEqual({ Foundry: 1 });
    });

    it('clamps stored sizes when deriving', () => {
        expect(effectiveMultiples({ Constructor: 99 }, { Constructor: true })).toEqual({ Constructor: 40 });
    });

    it('does not mutate its inputs', () => {
        const sizes = { Constructor: 8 };
        const enabled = { Constructor: true };

        effectiveMultiples(sizes, enabled);

        expect(sizes).toEqual({ Constructor: 8 });
        expect(enabled).toEqual({ Constructor: true });
    });
});
