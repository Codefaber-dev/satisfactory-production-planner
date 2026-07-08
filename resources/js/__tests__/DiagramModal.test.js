import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BuildDiagram from '../Pages/Production/BuildDiagram.vue';
import DiagramModal from '../Pages/Production/DiagramModal.vue';
import DiagramSvg from '../Pages/Production/DiagramSvg.vue';

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

// full extent = 14×8 by 8×8 m
const FULL_VIEWBOX = '0 0 112 64';

const mountModal = (options = {}) => mount(DiagramModal, { props: { footprint: baseFootprint() }, ...options });

const modalViewBox = (wrapper) => wrapper.findComponent(DiagramSvg).find('svg').attributes('viewBox');

const viewWidth = (wrapper) => Number(modalViewBox(wrapper).split(' ')[2]);

// jsdom has no PointerEvent and VTU trigger() can't assign the getter-only
// clientX/clientY on a constructed MouseEvent — dispatch a real MouseEvent
// under the pointer-event type name instead (Vue matches on the type string)
const firePointer = async (wrapper, type, { pointerId, clientX, clientY }) => {
    const event = new MouseEvent(type, { clientX, clientY, bubbles: true, cancelable: true });
    Object.defineProperty(event, 'pointerId', { value: pointerId });

    wrapper.find('.touch-none').element.dispatchEvent(event);
    await wrapper.vm.$nextTick();
};

describe('BuildDiagram — modal entry point (V114)', () => {
    it('opens the modal with the same footprint on diagram click, none before', async () => {
        const wrapper = mount(BuildDiagram, { props: { footprint: baseFootprint() } });

        expect(wrapper.findComponent(DiagramModal).exists()).toBe(false);

        await wrapper.find('[aria-label="Expand diagram"]').trigger('click');

        const modal = wrapper.findComponent(DiagramModal);
        expect(modal.exists()).toBe(true);
        expect(modal.props('footprint')).toEqual(baseFootprint());
        expect(modalViewBox(modal)).toBe(FULL_VIEWBOX);
    });

    it('closes on modal close event', async () => {
        const wrapper = mount(BuildDiagram, { props: { footprint: baseFootprint() } });
        await wrapper.find('[aria-label="Expand diagram"]').trigger('click');

        await wrapper.findComponent(DiagramModal).find('[aria-label="Close diagram"]').trigger('click');

        expect(wrapper.findComponent(DiagramModal).exists()).toBe(false);
    });
});

describe('DiagramModal — pan/zoom (V114)', () => {
    it('wheel-up zooms in: viewBox window shrinks', async () => {
        const wrapper = mountModal();

        await wrapper.find('.touch-none').trigger('wheel', { deltaY: -100 });

        expect(viewWidth(wrapper)).toBeLessThan(112);
    });

    it('clamps zoom-in at 8x (window never below fullWidth / 8)', async () => {
        const wrapper = mountModal();
        const stage = wrapper.find('.touch-none');

        for (let i = 0; i < 40; i++) {
            await stage.trigger('wheel', { deltaY: -100 });
        }

        expect(viewWidth(wrapper)).toBeCloseTo(112 / 8, 5);
    });

    it('clamps zoom-out at 0.25x (window never above 4 x fullWidth)', async () => {
        const wrapper = mountModal();
        const stage = wrapper.find('.touch-none');

        for (let i = 0; i < 40; i++) {
            await stage.trigger('wheel', { deltaY: 100 });
        }

        expect(viewWidth(wrapper)).toBeCloseTo(112 * 4, 5);
    });

    it('pointer drag pans the viewBox opposite the drag direction', async () => {
        const wrapper = mountModal();

        await firePointer(wrapper, 'pointerdown', { pointerId: 1, clientX: 100, clientY: 100 });
        await firePointer(wrapper, 'pointermove', { pointerId: 1, clientX: 120, clientY: 90 });

        // jsdom clientWidth 0 → 1 px = 1 m fallback: drag right/up moves window left/down
        const [x, y] = modalViewBox(wrapper).split(' ').map(Number);
        expect(x).toBe(-20);
        expect(y).toBe(10);
    });

    it('two-pointer pinch apart zooms in', async () => {
        const wrapper = mountModal();

        await firePointer(wrapper, 'pointerdown', { pointerId: 1, clientX: 100, clientY: 100 });
        await firePointer(wrapper, 'pointerdown', { pointerId: 2, clientX: 120, clientY: 100 });
        await firePointer(wrapper, 'pointermove', { pointerId: 2, clientX: 160, clientY: 100 });

        expect(viewWidth(wrapper)).toBeLessThan(112);
    });

    it('reset view restores the fit-all viewBox after zoom and pan', async () => {
        const wrapper = mountModal();
        const stage = wrapper.find('.touch-none');

        await stage.trigger('wheel', { deltaY: -100 });
        await firePointer(wrapper, 'pointerdown', { pointerId: 1, clientX: 0, clientY: 0 });
        await firePointer(wrapper, 'pointermove', { pointerId: 1, clientX: 30, clientY: 30 });
        expect(modalViewBox(wrapper)).not.toBe(FULL_VIEWBOX);

        await wrapper.find('[data-reset-view]').trigger('click');

        expect(modalViewBox(wrapper)).toBe(FULL_VIEWBOX);
    });

    it('emits close on Escape keydown', () => {
        const wrapper = mountModal();

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));

        expect(wrapper.emitted('close')).toHaveLength(1);
    });

    it('emits close on backdrop click, not on panel click', async () => {
        const wrapper = mountModal();

        await wrapper.find('.touch-none').trigger('click');
        expect(wrapper.emitted('close')).toBeUndefined();

        await wrapper.find('[data-diagram-modal]').trigger('click');
        expect(wrapper.emitted('close')).toHaveLength(1);
    });

    it('zoom in/out buttons zoom the viewBox within the wheel clamps', async () => {
        const wrapper = mountModal();

        await wrapper.find('[data-zoom-in]').trigger('click');
        expect(viewWidth(wrapper)).toBeCloseTo(112 / 1.2, 5);

        await wrapper.find('[data-zoom-out]').trigger('click');
        expect(viewWidth(wrapper)).toBeCloseTo(112, 5);

        for (let i = 0; i < 40; i++) {
            await wrapper.find('[data-zoom-out]').trigger('click');
        }
        expect(viewWidth(wrapper)).toBeCloseTo(112 * 4, 5);
    });
});

describe('DiagramModal — composition + theming (V115)', () => {
    it('shows the step title as the heading, monogram fallback without one', () => {
        const titled = mountModal({ props: { footprint: baseFootprint(), title: 'Iron Rod' } });
        expect(titled.find('h2').text()).toBe('Iron Rod');

        const untitled = mountModal();
        expect(untitled.find('h2').text()).toBe('C Build Diagram');
    });

    it('renders the FootprintSummary stats panel beside the stage (V119)', () => {
        const wrapper = mountModal();

        const panel = wrapper.findComponent({ name: 'footprint-summary' });
        expect(panel.exists()).toBe(true);
        expect(panel.text()).toContain('Foundations');
        expect(panel.text()).toContain('Belt Speed');
        expect(panel.props('footprint')).toEqual(baseFootprint());
    });

    it('renders in place (no body teleport) as a blueprint engineering-drawing sheet (V128)', () => {
        const wrapper = mountModal();

        // in-place render: the overlay is inside the component tree, not document.body
        expect(wrapper.find('[data-diagram-modal]').exists()).toBe(true);

        const sheet = wrapper.find('[data-drawing-sheet]');
        expect(sheet.exists()).toBe(true);
        expect(sheet.classes()).toContain('bg-blue-950');
        expect(sheet.classes()).toContain('text-blue-100');
    });

    it('renders its diagram in blueprint mode (V120/V128)', () => {
        const modal = mountModal();
        expect(modal.findComponent(DiagramSvg).props('blueprint')).toBe(true);
    });

    it('renders the footprint stats as title-block compartments with a live scale cell (V128)', async () => {
        const wrapper = mountModal();

        expect(wrapper.find('[data-title-block]').exists()).toBe(true);
        expect(wrapper.findAll('[data-title-block-cell]').length).toBeGreaterThan(5);

        const scale = wrapper.find('[data-scale-cell]');
        expect(scale.exists()).toBe(true);
        expect(scale.text()).toMatch(/1 m = [\d.]+ px/);

        // zooming in shrinks the view window ⇒ more pixels per meter
        const before = Number(scale.text().match(/= ([\d.]+) px/)[1]);
        await wrapper.find('[data-zoom-in]').trigger('click');
        const after = Number(
            wrapper
                .find('[data-scale-cell]')
                .text()
                .match(/= ([\d.]+) px/)[1]
        );
        expect(after).toBeGreaterThan(before);
    });

    it('lets the svg fill the stage (V121)', () => {
        const modalSvg = mountModal().findComponent(DiagramSvg).find('svg');
        expect(modalSvg.classes()).toContain('h-full');
    });

    it('fits the view window to the measured stage aspect, centered on the diagram (V121)', async () => {
        const wrapper = mountModal();
        const stage = wrapper.find('.touch-none').element;

        // square stage: window = 112x112 (diagram is 112x64), centered vertically
        Object.defineProperty(stage, 'clientWidth', { value: 800 });
        Object.defineProperty(stage, 'clientHeight', { value: 800 });
        await wrapper.find('[data-reset-view]').trigger('click');

        expect(modalViewBox(wrapper)).toBe('0 -24 112 112');

        // zoom preserves the stage-matched aspect
        await wrapper.find('[data-zoom-in]').trigger('click');
        const [, , w, h] = modalViewBox(wrapper).split(' ').map(Number);
        expect(h / w).toBeCloseTo(1, 5);
    });

    it('fit view derives from the content extent, not the slab, when bands overflow (V124)', () => {
        // single row of 4 buildings 18×20 m: 4 staggered input bands need 15 m
        // above the row but top_offset is 10 → extent extends 5 m past the slab
        const wrapper = mountModal({
            props: {
                footprint: {
                    ...baseFootprint(),
                    rows: 1,
                    buildings_per_row: 4,
                    num_buildings: 4,
                    building_width: 18,
                    building_length: 20,
                    length_foundations: 5,
                    width_foundations: 13,
                    row_spacing: 0,
                    top_offset: 10,
                    bottom_offset: 10,
                    left_offset: 16,
                },
                materials: { inputs: ['A', 'B', 'C', 'D'], outputs: ['O'] },
            },
        });

        expect(modalViewBox(wrapper)).toBe('0 -5 104 45');
    });
});
