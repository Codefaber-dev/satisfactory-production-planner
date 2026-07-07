import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DiagramSvg from '../Pages/Production/DiagramSvg.vue';
import { groupedFootprint } from '../blueprintFootprint';

// same fixture as DiagramSvg.test: 2 rows × 8 buildings of 10×8 m, rows at
// y=16 and y=40, building centers at x = 21, 31, … 91
const baseFootprint = () => ({
    monogram: 'C',
    num_buildings: 16,
    rows: 2,
    buildings_per_row: 8,
    building_length: 8,
    building_length_foundations: 1,
    building_width: 10,
    length_m: 16,
    length_foundations: 8,
    width_m: 80,
    width_foundations: 14,
    height_m: 8,
    height_walls: 3,
    foundations: 112,
    walls: 132,
    foundation_border_y: true,
    row_spacing: 16,
    row_spacing_offset: 16,
    top_offset: 16,
    bottom_offset: 16,
    left_offset: 16,
    building_top_offset: 0,
    belt_speed: 780,
    belt_load: 480,
    belt_load_in: 240,
    belt_load_out: 120,
    belt_utilization_in: 31,
    belt_utilization_out: 15,
    power_shards: 0,
    somersloops: 0,
});

const mountSvg = (materials, footprint = baseFootprint()) =>
    mount(DiagramSvg, { props: { footprint, materials } });

const lineByMaterial = (wrapper, material) => wrapper.find(`[data-manifold-line="${material}"]`);

describe('DiagramSvg — belt manifolds (V116)', () => {
    it('renders one splitter per building per input line, one merger per building per output line', () => {
        const wrapper = mountSvg({ inputs: ['Iron Ore'], outputs: ['Iron Rod'] });

        expect(wrapper.findAll('[data-kind="input"] [data-manifold-square]')).toHaveLength(16);
        expect(wrapper.findAll('[data-kind="output"] [data-manifold-square]')).toHaveLength(16);
    });

    it('renders one merger line per output: product plus each byproduct', () => {
        const wrapper = mountSvg({ inputs: ['Crude Oil'], outputs: ['Plastic', 'Heavy Oil Residue'] });

        const outputLines = wrapper.findAll('[data-kind="output"]');
        expect(outputLines).toHaveLength(2);
        expect(lineByMaterial(wrapper, 'Plastic').exists()).toBe(true);
        expect(lineByMaterial(wrapper, 'Heavy Oil Residue').exists()).toBe(true);
    });

    it('gives two inputs distinct colors, distinct dasharrays, and material labels', () => {
        const wrapper = mountSvg({ inputs: ['Iron Ore', 'Coal'], outputs: ['Steel Ingot'] });

        const trunkOf = (material) => lineByMaterial(wrapper, material).find('[data-trunk-segment]');
        const iron = trunkOf('Iron Ore');
        const coal = trunkOf('Coal');

        expect(iron.attributes('stroke')).not.toBe(coal.attributes('stroke'));
        expect(iron.attributes('stroke-dasharray')).not.toBe(coal.attributes('stroke-dasharray'));

        const labels = wrapper.findAll('[data-manifold-label]').map((l) => l.text());
        expect(labels).toContain('Iron Ore');
        expect(labels).toContain('Coal');
        expect(labels).toContain('Steel Ingot');
    });

    it('draws dashed trunk segments and stubs with directional arrow markers', () => {
        const wrapper = mountSvg({ inputs: ['Iron Ore'], outputs: ['Iron Rod'] });

        // 7 edge-to-edge segments per row × 2 rows per line
        const inputTrunks = wrapper.findAll('[data-kind="input"] [data-trunk-segment]');
        expect(inputTrunks).toHaveLength(14);

        for (const segment of inputTrunks) {
            expect(segment.attributes('stroke-dasharray')).toBeTruthy();
            expect(segment.attributes('marker-end')).toMatch(/^url\(#.*\)$/);
        }

        // input stubs point INTO the building: y2 (row top, 16) below y1 (band bottom, 14)
        const stub = wrapper.find('[data-kind="input"] [data-stub]');
        expect(Number(stub.attributes('y1'))).toBe(14);
        expect(Number(stub.attributes('y2'))).toBe(16);

        // output trunks flow TOWARD the label end: x2 < x1
        const outTrunk = wrapper.find('[data-kind="output"] [data-trunk-segment]');
        expect(Number(outTrunk.attributes('x2'))).toBeLessThan(Number(outTrunk.attributes('x1')));
    });

    it('centers a single square on the building axis, 2 m off the row edge, at wiki 4x4 m with rounded corners', () => {
        const wrapper = mountSvg({ inputs: ['Iron Ore'], outputs: ['Iron Rod'] });

        const square = wrapper.find('[data-kind="input"] [data-manifold-square]');
        // first building center x=21 → square x = 19; band 2 m above row top 16 → y = 10
        expect(Number(square.attributes('x'))).toBe(19);
        expect(Number(square.attributes('y'))).toBe(10);
        expect(Number(square.attributes('width'))).toBe(4);
        expect(Number(square.attributes('height'))).toBe(4);
        expect(square.attributes('rx')).toBe('1');

        const merger = wrapper.find('[data-kind="output"] [data-manifold-square]');
        // 2 m below row bottom 24
        expect(Number(merger.attributes('y'))).toBe(26);
    });

    it('centers a two-square group about the building axis with a 2 m in-group gap', () => {
        const wrapper = mountSvg({ inputs: ['Iron Ore', 'Coal'], outputs: ['Steel Ingot'] });

        const ironX = Number(lineByMaterial(wrapper, 'Iron Ore').find('[data-manifold-square]').attributes('x'));
        const coalX = Number(lineByMaterial(wrapper, 'Coal').find('[data-manifold-square]').attributes('x'));

        // span 10 centered on cx 21 → slots at x=16 and x=22 (centers 18/24, symmetric ±3)
        expect(ironX).toBe(16);
        expect(coalX).toBe(22);
        expect(coalX - (ironX + 4)).toBe(2);
    });

    it('staggers same-building squares vertically 4 m center-to-center (V118)', () => {
        const wrapper = mountSvg({ inputs: ['Iron Ore', 'Coal'], outputs: ['Steel Ingot', 'Slag'] });

        // inputs stack upward away from the row: slot 0 at y=10, slot 1 at y=6
        expect(Number(lineByMaterial(wrapper, 'Iron Ore').find('[data-manifold-square]').attributes('y'))).toBe(10);
        expect(Number(lineByMaterial(wrapper, 'Coal').find('[data-manifold-square]').attributes('y'))).toBe(6);

        // outputs stack downward: slot 0 at y=26, slot 1 at y=30
        expect(Number(lineByMaterial(wrapper, 'Steel Ingot').find('[data-manifold-square]').attributes('y'))).toBe(26);
        expect(Number(lineByMaterial(wrapper, 'Slag').find('[data-manifold-square]').attributes('y'))).toBe(30);

        // trunks ride their own slot's y level
        const ironTrunkY = Number(lineByMaterial(wrapper, 'Iron Ore').find('[data-trunk-segment]').attributes('y1'));
        const coalTrunkY = Number(lineByMaterial(wrapper, 'Coal').find('[data-trunk-segment]').attributes('y1'));
        expect(ironTrunkY).toBe(12);
        expect(coalTrunkY).toBe(8);
    });

    it('labels squares SP for splitters and MG for mergers', () => {
        const wrapper = mountSvg({ inputs: ['Iron Ore'], outputs: ['Iron Rod'] });

        expect(wrapper.find('[data-kind="input"]').text()).toContain('SP');
        expect(wrapper.find('[data-kind="output"]').text()).toContain('MG');
        expect(wrapper.find('[data-kind="input"]').text()).not.toContain('MG');
    });

    it('renders zero manifold elements in blueprint mode', () => {
        const wrapper = mountSvg(
            { inputs: ['Iron Ore'], outputs: ['Iron Rod'] },
            groupedFootprint(baseFootprint(), 8, 32),
        );

        expect(wrapper.findAll('[data-manifold-line]')).toHaveLength(0);
        expect(wrapper.findAll('[data-manifold-square]')).toHaveLength(0);
    });

    it('falls back to generic single input and output lines without a materials prop', () => {
        const wrapper = mount(DiagramSvg, { props: { footprint: baseFootprint() } });

        expect(wrapper.findAll('[data-kind="input"]')).toHaveLength(1);
        expect(wrapper.findAll('[data-kind="output"]')).toHaveLength(1);
        const labels = wrapper.findAll('[data-manifold-label]').map((l) => l.text());
        expect(labels).toEqual(['Input', 'Output']);
    });

    it('renders no output lines when outputs are empty (terminal sink step)', () => {
        const wrapper = mountSvg({ inputs: ['Iron Rod'], outputs: [] });

        expect(wrapper.findAll('[data-kind="input"]')).toHaveLength(1);
        expect(wrapper.findAll('[data-kind="output"]')).toHaveLength(0);
    });
});

describe('DiagramSvg — belt-only manifolds, liquids filtered (V117)', () => {
    const provideLiquids = (names) => ({ global: { provide: { liquidNames: new Set(names) } } });

    const mountWithLiquids = (materials, liquids) =>
        mount(DiagramSvg, { props: { footprint: baseFootprint(), materials }, ...provideLiquids(liquids) });

    it('renders no manifold line for a liquid input or output', () => {
        // Packager case: fluid arrives by pipe, canisters by belt, packaged form leaves by belt
        const wrapper = mountWithLiquids(
            { inputs: ['Heavy Oil Residue', 'Empty Canister'], outputs: ['Packaged Heavy Oil Residue'] },
            ['Heavy Oil Residue'],
        );

        expect(lineByMaterial(wrapper, 'Heavy Oil Residue').exists()).toBe(false);
        expect(lineByMaterial(wrapper, 'Empty Canister').exists()).toBe(true);
        expect(lineByMaterial(wrapper, 'Packaged Heavy Oil Residue').exists()).toBe(true);
    });

    it('keeps palette indices contiguous after filtering', () => {
        const filtered = mountWithLiquids({ inputs: ['Water', 'Coal'], outputs: [] }, ['Water']);
        const unfiltered = mountWithLiquids({ inputs: ['Coal'], outputs: [] }, []);

        // Coal takes slot/palette index 0 in both — the dropped liquid leaves no gap
        expect(lineByMaterial(filtered, 'Coal').find('[data-trunk-segment]').attributes('stroke')).toBe(
            lineByMaterial(unfiltered, 'Coal').find('[data-trunk-segment]').attributes('stroke'),
        );
        expect(Number(lineByMaterial(filtered, 'Coal').find('[data-manifold-square]').attributes('x'))).toBe(19);
    });

    it('renders zero lines on a side whose materials are all liquid', () => {
        const wrapper = mountWithLiquids({ inputs: ['Water', 'Crude Oil'], outputs: ['Fuel'] }, [
            'Water',
            'Crude Oil',
            'Fuel',
        ]);

        expect(wrapper.findAll('[data-manifold-line]')).toHaveLength(0);
    });

    it('does not filter without the inject, and leaves the generic fallback intact', () => {
        const bare = mountSvg({ inputs: ['Water'], outputs: [] });
        expect(lineByMaterial(bare, 'Water').exists()).toBe(true);

        const generic = mount(DiagramSvg, {
            props: { footprint: baseFootprint() },
            ...provideLiquids(['Water']),
        });
        expect(generic.findAll('[data-manifold-label]').map((l) => l.text())).toEqual(['Input', 'Output']);
    });
});
