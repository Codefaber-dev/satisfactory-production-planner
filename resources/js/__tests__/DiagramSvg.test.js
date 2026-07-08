import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DiagramSvg from '../Pages/Production/DiagramSvg.vue';
import BuildDiagram from '../Pages/Production/BuildDiagram.vue';
import { groupedFootprint } from '../blueprintFootprint';

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

const mountSvg = (footprint = baseFootprint()) => mount(DiagramSvg, { props: { footprint } });

describe('DiagramSvg — SVG build diagram (V113)', () => {
    it('renders a single svg whose viewBox matches the footprint foundation dims in meters', () => {
        const wrapper = mountSvg();

        const svgs = wrapper.findAll('svg');
        expect(svgs).toHaveLength(1);
        // 14 foundations × 8 m wide, 8 foundations × 8 m long
        expect(svgs[0].attributes('viewBox')).toBe('0 0 112 64');
    });

    it('scales to fit its container: no fixed px/rem width or height attributes', () => {
        const svg = mountSvg().find('svg');

        expect(svg.attributes('width')).toBeUndefined();
        expect(svg.attributes('height')).toBeUndefined();
        expect(svg.attributes('preserveAspectRatio')).toBe('xMidYMid meet');
        expect(svg.classes()).toContain('w-full');
    });

    it('renders one tile per building labeled with the monogram', () => {
        const wrapper = mountSvg();

        expect(wrapper.findAll('[data-tile]')).toHaveLength(16);
        const labels = wrapper.findAll('text').filter((t) => t.text() === 'C');
        expect(labels).toHaveLength(16);
    });

    it('caps tiles at num_buildings when the grid has spare cells', () => {
        const wrapper = mountSvg({ ...baseFootprint(), num_buildings: 13 });

        expect(wrapper.findAll('[data-tile]')).toHaveLength(13);
    });

    it('honors footprint.rows: tiles land on rows distinct y positions', () => {
        const wrapper = mountSvg();

        const ys = new Set(wrapper.findAll('[data-tile]').map((r) => r.attributes('y')));
        expect(ys.size).toBe(2);
        // row pitch = building_length + row_spacing = 24 m from top_offset 16
        expect(ys.has('16')).toBe(true);
        expect(ys.has('40')).toBe(true);
    });

    it('renders offset and row-spacing dimension markers with meter labels', () => {
        const wrapper = mountSvg();

        for (const marker of ['top-offset', 'bottom-offset', 'left-offset', 'right-offset', 'row-spacing']) {
            const group = wrapper.find(`[data-marker="${marker}"]`);
            expect(group.exists()).toBe(true);
            expect(group.text()).toContain('16 m');
        }
    });

    it('hides the row-spacing marker for single-row footprints', () => {
        const wrapper = mountSvg({ ...baseFootprint(), rows: 1, buildings_per_row: 16 });

        expect(wrapper.find('[data-marker="row-spacing"]').exists()).toBe(false);
    });

    // V129/B67: dimension text small (material-label size), haloed in the slab
    // color, and set beside the vertical marker line instead of across it
    it('renders dimension text at label size with a slab halo, beside the line', () => {
        const wrapper = mountSvg();

        for (const marker of ['top-offset', 'bottom-offset', 'row-spacing']) {
            const text = wrapper.find(`[data-marker="${marker}"] text`);
            expect(text.attributes('font-size')).toBe('2.5');
            expect(text.attributes('paint-order')).toBe('stroke');
            expect(text.attributes('stroke')).toBe('#93c5fd');
            expect(text.attributes('text-anchor')).toBe('start');
            // markerX = 40% of the 112 m canvas, text nudged 1 m right of the line
            expect(Number(text.attributes('x'))).toBeCloseTo(112 * 0.4 + 1, 5);
        }

        for (const marker of ['left-offset', 'right-offset']) {
            const text = wrapper.find(`[data-marker="${marker}"] text`);
            expect(text.attributes('font-size')).toBe('2.5');
            expect(text.attributes('paint-order')).toBe('stroke');
            // horizontal markers: text lifted off the line
            expect(Number(text.attributes('y'))).toBeCloseTo(64 / 2 - 2, 5);
        }
    });

    // V129: marker lines touch the rendered building edges — building_top_offset
    // shifts the row down, so the top line extends and the bottom line starts later
    it('offset marker lines include the building_top_offset shift', () => {
        const footprint = { ...baseFootprint(), rows: 1, buildings_per_row: 16, building_top_offset: 0.5 };
        const wrapper = mountSvg(footprint);

        const top = wrapper.find('[data-marker="top-offset"] line');
        expect(Number(top.attributes('y2'))).toBeCloseTo(16.5, 5);

        const bottom = wrapper.find('[data-marker="bottom-offset"] line');
        expect(Number(bottom.attributes('y1'))).toBeCloseTo(64 - 16 + 0.5, 5);

        // text rides the line's outer end — canvas edges, clear of the manifold bands
        expect(Number(wrapper.find('[data-marker="top-offset"] text').attributes('y'))).toBe(2);
        expect(Number(wrapper.find('[data-marker="bottom-offset"] text').attributes('y'))).toBe(62);
    });

    it('renders grouped blueprint tiles with the monogram x size label (V41)', () => {
        const footprint = groupedFootprint(baseFootprint(), 8, 32);
        const wrapper = mountSvg(footprint);

        expect(wrapper.findAll('[data-tile]')).toHaveLength(footprint.blueprints);
        expect(wrapper.text()).toContain('Cx8');
    });
});

describe('BuildDiagram — modal-only composition (V134)', () => {
    it('renders no inline diagram or stats, just the View Diagram trigger', () => {
        const wrapper = mount(BuildDiagram, { props: { footprint: baseFootprint() } });

        expect(wrapper.findComponent(DiagramSvg).exists()).toBe(false);
        expect(wrapper.text()).not.toContain('Foundations');
        expect(wrapper.find('[aria-label="Expand diagram"]').exists()).toBe(true);
    });

    it('renders the svg and footprint stats inside the opened modal', async () => {
        const wrapper = mount(BuildDiagram, { props: { footprint: baseFootprint() } });

        await wrapper.find('[aria-label="Expand diagram"]').trigger('click');

        expect(wrapper.findComponent(DiagramSvg).exists()).toBe(true);
        expect(wrapper.text()).toContain('Foundations');
        expect(wrapper.text()).toContain('Belt Speed');
    });
});
