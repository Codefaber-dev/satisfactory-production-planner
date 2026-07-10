import { describe, expect, it } from 'vitest';
import { COL_GAP, MARGIN, NODE_H, NODE_W, masterLayout } from '../masterDiagram';

const footprint = () => ({
    monogram: 'S',
    num_buildings: 2,
    rows: 1,
    buildings_per_row: 2,
    building_length: 9,
    building_width: 6,
    length_foundations: 3,
    width_foundations: 3,
    row_spacing: 0,
    row_spacing_offset: 0,
    top_offset: 4,
    bottom_offset: 4,
    left_offset: 6,
    building_top_offset: 0,
});

// Iron Ore (raw) → Iron Ingot → Iron Plate → sink; Screw imported; Iron Ingot
// emits Water byproduct consumed by Iron Plate; part of Iron Ingot is final
const makePlan = () => ({
    rawSteps: {
        'Iron Ore': {
            raw: true,
            outputs: { 'Iron Ingot': 30 },
            production: [{ name: 'Iron Ore', recipe: null, qty: 30, ingredients: {}, byproducts: {} }],
        },
    },
    levels: [
        {
            'Iron Ingot': {
                imported: false,
                outputs: { 'Iron Plate': 20, final: 10 },
                production: [
                    {
                        name: 'Iron Ingot',
                        recipe: { product: { name: 'Iron Ingot' }, description: 'Iron Ingot' },
                        qty: 30,
                        ingredients: { 'Iron Ore': 30 },
                        byproducts: { Water: 5 },
                    },
                ],
            },
        },
        {
            'Iron Plate': {
                imported: false,
                outputs: { final: 20 },
                production: [
                    {
                        name: 'Iron Plate',
                        recipe: { product: { name: 'Iron Plate' }, description: 'Iron Plate' },
                        qty: 20,
                        ingredients: { 'Iron Ingot': 30, Screw: 60 },
                        byproducts: {},
                    },
                ],
            },
        },
    ],
    recycleSteps: [
        {
            type: 'sink',
            name: 'AWESOME Sink',
            building: 'AWESOME Sink',
            num_buildings: 1,
            power: 30,
            inputs: [{ item: 'Iron Plate', qty: 5 }],
            output: { points: 600 },
            footprint: { ...footprint(), monogram: 'AS' },
        },
    ],
    byproductsUsed: { Water: { 'Iron Plate': 5 } },
    overviews: {
        'Iron Ingot|Iron Ingot': {
            clock: 'c100',
            selected_variant_name: 'Smelter (mk1)',
            overviews: {
                c100: {
                    qty: 30,
                    building: 'Smelter',
                    details: { 'Smelter (mk1)': { num_buildings: 2, footprint: footprint() } },
                },
            },
        },
    },
});

const findNode = (layout, id) => layout.nodes.find((node) => node.id === id);

const findEdge = (layout, from, to, material) =>
    layout.edges.find((edge) => edge.from.id === from && edge.to.id === to && edge.material === material);

describe('masterLayout — nodes (V139)', () => {
    it('creates one node per rendered step: raws, level steps, sink', () => {
        const layout = masterLayout(makePlan());

        expect(layout.nodes.map((node) => node.id)).toEqual(['0.A', '1.A', '2.A', 'R.A']);
        expect(findNode(layout, '0.A').name).toBe('Iron Ore');
        expect(findNode(layout, 'R.A').name).toBe('AWESOME Sink');
    });

    it('ranks columns by build level: x grows monotonically Level 0 → sink', () => {
        const layout = masterLayout(makePlan());

        for (const node of layout.nodes) {
            expect(node.x).toBe(MARGIN + node.col * (NODE_W + COL_GAP));
            expect(node.w).toBe(NODE_W);
            expect(node.h).toBe(NODE_H);
        }

        expect(layout.nodes.map((node) => node.col)).toEqual([0, 1, 2, 3]);
    });

    it('skips imported and recipe-less level materials', () => {
        const plan = makePlan();
        plan.levels[0]['Copper Ingot'] = {
            imported: true,
            outputs: {},
            production: [{ name: 'Copper Ingot', recipe: { product: { name: 'Copper Ingot' } }, ingredients: {} }],
        };

        const layout = masterLayout(plan);

        expect(layout.nodes.some((node) => node.name === 'Copper Ingot')).toBe(false);
    });

    it('resolves footprint + building count from the overview (V40 key)', () => {
        const layout = masterLayout(makePlan());
        const ingot = findNode(layout, '1.A');

        expect(ingot.footprint).toEqual(footprint());
        expect(ingot.numBuildings).toBe(2);
        expect(ingot.monogram).toBe('S');
        // no overview entry ⇒ black box only, never a crash
        expect(findNode(layout, '2.A').footprint).toBeNull();
    });
});

describe('masterLayout — edges (V139/V138)', () => {
    it('routes an edge per outputs-map flow with its qty', () => {
        const layout = masterLayout(makePlan());

        expect(findEdge(layout, '0.A', '1.A', 'Iron Ore')).toMatchObject({ qty: 30 });
        expect(findEdge(layout, '1.A', '2.A', 'Iron Ingot')).toMatchObject({ qty: 20 });
    });

    it('routes byproduct flows via byproducts_used', () => {
        const layout = masterLayout(makePlan());

        expect(findEdge(layout, '1.A', '2.A', 'Water')).toMatchObject({ qty: 5 });
    });

    it('routes sink inputs the outputs maps did not carry', () => {
        const layout = masterLayout(makePlan());

        expect(findEdge(layout, '2.A', 'R.A', 'Iron Plate')).toMatchObject({ qty: 5 });
    });

    it('attaches edges to node borders: source right edge, dest left edge', () => {
        const layout = masterLayout(makePlan());
        const edge = findEdge(layout, '0.A', '1.A', 'Iron Ore');

        expect(edge.x1).toBe(findNode(layout, '0.A').x + NODE_W);
        expect(edge.x2).toBe(findNode(layout, '1.A').x);
    });

    it('drops zero-qty flows (V138 guard)', () => {
        const plan = makePlan();
        plan.rawSteps['Iron Ore'].outputs['Iron Ingot'] = 0;

        expect(masterLayout(plan).edges.some((edge) => edge.material === 'Iron Ore')).toBe(false);
    });
});

describe('masterLayout — boundary markers (V139)', () => {
    it('final outputs become final markers, never edges', () => {
        const layout = masterLayout(makePlan());
        const finals = layout.finals.filter((final) => final.kind === 'final');

        expect(finals).toHaveLength(2);
        expect(finals.map((final) => `${final.node.id}:${final.material}:${final.qty}`).sort()).toEqual([
            '1.A:Iron Ingot:10',
            '2.A:Iron Plate:20',
        ]);
    });

    it('unresolvable destinations become external markers, no dangling edge', () => {
        const plan = makePlan();
        plan.levels[1]['Iron Plate'].outputs.Elsewhere = 4;

        const layout = masterLayout(plan);
        const external = layout.finals.find((final) => final.kind === 'external');

        expect(external).toMatchObject({ material: 'Iron Plate', dest: 'Elsewhere', qty: 4 });
        expect(layout.edges.some((edge) => edge.to?.id === undefined)).toBe(false);
    });

    it('unproduced ingredients become import markers on their consumer', () => {
        const layout = masterLayout(makePlan());

        expect(layout.imports).toHaveLength(1);
        expect(layout.imports[0]).toMatchObject({ material: 'Screw', qty: 60 });
        expect(layout.imports[0].node.id).toBe('2.A');
        expect(layout.imports[0].x).toBe(findNode(layout, '2.A').x);
    });
});

describe('masterLayout — legend materials + extent', () => {
    it('lists each edge/marker material once, sorted', () => {
        const layout = masterLayout(makePlan());

        expect(layout.materials).toEqual(['Iron Ingot', 'Iron Ore', 'Iron Plate', 'Screw', 'Water']);
    });

    it('extent covers every node; empty plan yields a zero extent', () => {
        const layout = masterLayout(makePlan());

        for (const node of layout.nodes) {
            expect(node.x + node.w).toBeLessThanOrEqual(layout.extent.w);
            expect(node.y + node.h).toBeLessThanOrEqual(layout.extent.h);
        }

        expect(masterLayout({}).extent).toEqual({ x: 0, y: 0, w: 0, h: 0 });
    });
});
