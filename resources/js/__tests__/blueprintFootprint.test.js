import { describe, expect, it } from 'vitest';
import { DESIGNER_DIMS, groupedFootprint, loadDesignerMk, saveDesignerMk } from '@/blueprintFootprint';

const makeStore = () => {
    const data = {};

    return {
        getItem: (key, def = null) => (key in data ? data[key] : def),
        setItem: (key, value) => {
            data[key] = value;
        },
        _data: data,
    };
};

const baseFootprint = () => ({
    monogram: 'C',
    num_buildings: 16,
    rows: 2,
    buildings_per_row: 8,
    building_length: 8,
    building_width: 10,
    belt_speed: 780,
    belt_load: 480,
    belt_load_in: 240,
    belt_load_out: 120,
    belt_utilization_in: 31,
    belt_utilization_out: 15,
    power_shards: 0,
    somersloops: 0,
});

describe('designer mark persistence (V43)', () => {
    it('exposes the three in-game designer sizes', () => {
        expect(DESIGNER_DIMS).toEqual({ mk1: 32, mk2: 40, mk3: 48 });
    });

    it('defaults to mk1 on fresh store', () => {
        expect(loadDesignerMk(makeStore())).toBe('mk1');
    });

    it('round-trips a valid mark', () => {
        const store = makeStore();

        saveDesignerMk(store, 'mk3');

        expect(loadDesignerMk(store)).toBe('mk3');
    });

    it('falls back to mk1 on garbage values', () => {
        const store = makeStore();

        store.setItem('bp_designer', 'mk9');

        expect(loadDesignerMk(store)).toBe('mk1');
        expect(loadDesignerMk({ getItem: () => null })).toBe('mk1');
    });
});

describe('groupedFootprint (V41 — blueprint tiles replace building outlines)', () => {
    it('renders count/X tiles', () => {
        const fp = groupedFootprint(baseFootprint(), 8, 32);

        expect(fp.blueprints).toBe(2);
        expect(fp.num_buildings).toBe(2);
        expect(fp.grouped).toBe(true);
        expect(fp.group_size).toBe(8);
    });

    it('labels tiles monogram x size', () => {
        expect(groupedFootprint(baseFootprint(), 8, 32).monogram).toBe('Cx8');
        expect(groupedFootprint({ ...baseFootprint(), monogram: 'S' }, 10, 32).monogram).toBe('Sx10');
    });

    it('rounds tile count up when count is not an exact multiple', () => {
        expect(groupedFootprint({ ...baseFootprint(), num_buildings: 17 }, 8, 32).blueprints).toBe(3);
    });

    it('lays tiles out near-square', () => {
        const fp = groupedFootprint(baseFootprint(), 2, 32); // 8 tiles

        expect(fp.buildings_per_row).toBe(3);
        expect(fp.rows).toBe(3);
    });

    it('does not mutate the input footprint', () => {
        const base = baseFootprint();
        const snapshot = { ...base };

        groupedFootprint(base, 8, 32);

        expect(base).toEqual(snapshot);
    });
});

describe('groupedFootprint dimensions (V43 — designer dims)', () => {
    it.each([
        ['mk1', 32, 4],
        ['mk2', 40, 5],
        ['mk3', 48, 6],
    ])('sizes tiles at %s designer dims', (mk, dim, tileFoundations) => {
        const fp = groupedFootprint(baseFootprint(), 8, DESIGNER_DIMS[mk]);

        expect(fp.building_length).toBe(dim);
        expect(fp.building_width).toBe(dim);
        expect(fp.height_m).toBe(dim);
        expect(fp.building_length_foundations).toBe(tileFoundations);
    });

    it('recomputes foundations, walls and offsets against tile dims (mk1, 2 tiles, 1 row)', () => {
        const fp = groupedFootprint(baseFootprint(), 8, 32);

        expect(fp.rows).toBe(1);
        expect(fp.buildings_per_row).toBe(2);
        expect(fp.length_foundations).toBe(6); // no border, 1 row × (4 + 2)
        expect(fp.width_foundations).toBe(12); // ceil(64/8) + 4
        expect(fp.foundations).toBe(72);
        expect(fp.height_walls).toBe(9); // ceil(32/4) + 1
        expect(fp.walls).toBe(9 * 2 * (6 + 12));
        expect(fp.row_spacing).toBe(0);
        expect(fp.top_offset).toBe(8); // ceil((48 - 32) / 2)
        expect(fp.left_offset).toBe(16); // 16 + (64 - 64) / 2
        expect(fp.building_top_offset).toBe(0);
    });

    it('recomputes per-row belt stats against the new row count', () => {
        const fp = groupedFootprint(baseFootprint(), 8, 32);

        // 1 row: full input load 480, full output 120 × 2 original rows = 240
        expect(fp.belt_load_in).toBe(480);
        expect(fp.belt_load_out).toBe(240);
        expect(fp.belt_utilization_in).toBe(Math.round(48000 / 780));
        expect(fp.belt_utilization_out).toBe(Math.round(24000 / 780));
    });

    it('keeps power shard and somersloop totals untouched', () => {
        const fp = groupedFootprint({ ...baseFootprint(), power_shards: 12, somersloops: 16 }, 8, 32);

        expect(fp.power_shards).toBe(12);
        expect(fp.somersloops).toBe(16);
    });
});
