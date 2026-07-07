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

    it('renders grouped blueprint tiles with the monogram x size label (V41)', () => {
        const footprint = groupedFootprint(baseFootprint(), 8, 32);
        const wrapper = mountSvg(footprint);

        expect(wrapper.findAll('[data-tile]')).toHaveLength(footprint.blueprints);
        expect(wrapper.text()).toContain('Cx8');
    });
});

describe('BuildDiagram — composes DiagramSvg (V113)', () => {
    it('renders the stats list and the svg diagram, without an overflow-x scroll wrapper', () => {
        const wrapper = mount(BuildDiagram, { props: { footprint: baseFootprint() } });

        expect(wrapper.findComponent(DiagramSvg).exists()).toBe(true);
        expect(wrapper.text()).toContain('Foundations');
        expect(wrapper.text()).toContain('Belt Speed');
        expect(wrapper.find('.overflow-x-auto').exists()).toBe(false);
    });
});
