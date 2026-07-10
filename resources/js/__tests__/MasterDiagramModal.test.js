import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { registerDiagram, resetDiagramRegistry } from '../diagramRegistry';
import MasterDiagram from '../Pages/Production/MasterDiagram.vue';
import DiagramSvg from '../Pages/Production/DiagramSvg.vue';

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

const makeProps = () => ({
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
                outputs: { final: 30 },
                production: [
                    {
                        name: 'Iron Ingot',
                        recipe: { product: { name: 'Iron Ingot' }, description: 'Iron Ingot' },
                        qty: 30,
                        ingredients: { 'Iron Ore': 30 },
                        byproducts: {},
                    },
                ],
            },
        },
    ],
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

const mountMaster = (props = {}) => mount(MasterDiagram, { props: { ...makeProps(), ...props } });

const zoomIn = async (wrapper, times) => {
    for (let i = 0; i < times; i++) {
        await wrapper.find('[data-zoom-in]').trigger('click');
    }
};

afterEach(() => {
    resetDiagramRegistry();
});

describe('MasterDiagram — nodes + bounds (V139/V141a/V122)', () => {
    it('renders a node group per step with faint dashed static-weight bounds', () => {
        const wrapper = mountMaster();
        const bounds = wrapper.findAll('[data-node-bounds]');

        expect(wrapper.findAll('[data-node]')).toHaveLength(2);
        for (const rect of bounds) {
            expect(rect.attributes('stroke-dasharray')).toBe('3 2');
            expect(rect.attributes('vector-effect')).toBe('non-scaling-stroke');
            expect(Number(rect.attributes('stroke-opacity'))).toBeLessThan(0.5);
        }
    });

    it('renders the flow edge with material label and qty', () => {
        const wrapper = mountMaster();

        expect(wrapper.find('[data-edge="Iron Ore"]').exists()).toBe(true);
        expect(wrapper.find('[data-edge-label]').text()).toBe('Iron Ore 30/min');
    });

    it('renders a final boundary marker for the requested output', () => {
        const wrapper = mountMaster();

        expect(wrapper.find('[data-final-marker="Iron Ingot"]').exists()).toBe(true);
    });
});

describe('MasterDiagram — hover highlight (V141b)', () => {
    it('lifts the hovered node bounds and restores on leave', async () => {
        const wrapper = mountMaster();
        const node = wrapper.find('[data-node="1.A"]');

        await node.trigger('mouseenter');

        expect(node.attributes('data-hovered')).toBe('');
        expect(Number(node.find('[data-node-bounds]').attributes('stroke-opacity'))).toBeGreaterThan(0.5);

        await node.trigger('mouseleave');

        expect(node.attributes('data-hovered')).toBeUndefined();
        expect(Number(node.find('[data-node-bounds]').attributes('stroke-opacity'))).toBeLessThan(0.5);
    });
});

describe('MasterDiagram — open affordance (V141c)', () => {
    it('shows the affordance only for registry-backed nodes; click opens + closes', async () => {
        const open = vi.fn();
        registerDiagram({ identifier: '1.A', name: 'Iron Ingot', open });

        const wrapper = mountMaster();

        expect(wrapper.find('[data-node="1.A"] [data-open-step]').exists()).toBe(true);
        expect(wrapper.find('[data-node="0.A"] [data-open-step]').exists()).toBe(false);

        await wrapper.find('[data-node="1.A"] [data-open-step]').trigger('click');

        expect(open).toHaveBeenCalled();
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('node region outside the affordance is click-inert', async () => {
        registerDiagram({ identifier: '1.A', name: 'Iron Ingot', open: vi.fn() });
        const wrapper = mountMaster();

        await wrapper.find('[data-node="1.A"] [data-node-bounds]').trigger('click');

        expect(wrapper.emitted('close')).toBeFalsy();
    });
});

describe('MasterDiagram — nav panel (V141d/V130)', () => {
    it('lists registry entries sorted; click navigates and closes', async () => {
        const open = vi.fn();
        registerDiagram({ identifier: '1.A', name: 'Iron Ingot', open: vi.fn() });
        registerDiagram({ identifier: '0.A', name: 'Iron Ore', open });

        const wrapper = mountMaster();
        const items = wrapper.findAll('[data-modal-nav-item]');

        expect(items.map((item) => item.text())).toEqual(['0.A Iron Ore', '1.A Iron Ingot']);

        await items[0].trigger('click');

        expect(open).toHaveBeenCalled();
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('collapses on toggle', async () => {
        registerDiagram({ identifier: '1.A', name: 'Iron Ingot', open: vi.fn() });
        const wrapper = mountMaster();

        await wrapper.find('[data-modal-nav-toggle]').trigger('click');

        expect(wrapper.find('[data-modal-nav-item]').exists()).toBe(false);
    });
});

describe('MasterDiagram — legend toggles (V140)', () => {
    it('unchecking a material hides its edges and labels only', async () => {
        const wrapper = mountMaster();
        const toggle = wrapper.find('[data-legend-toggle][value="Iron Ore"]');

        await toggle.setValue(false);

        expect(wrapper.find('[data-edge="Iron Ore"]').exists()).toBe(false);
        // the other material's final marker survives
        expect(wrapper.find('[data-final-marker="Iron Ingot"]').exists()).toBe(true);
    });

    it('hide all / show all flip every flow at once; state is modal-local', async () => {
        const wrapper = mountMaster();

        await wrapper.find('[data-legend-hide-all]').trigger('click');

        expect(wrapper.find('[data-edge="Iron Ore"]').exists()).toBe(false);
        expect(wrapper.find('[data-final-marker="Iron Ingot"]').exists()).toBe(false);

        await wrapper.find('[data-legend-show-all]').trigger('click');

        expect(wrapper.find('[data-edge="Iron Ore"]').exists()).toBe(true);

        // fresh mount ⇒ all ON again (no persistence)
        const reopened = mountMaster();
        expect(reopened.find('[data-edge="Iron Ore"]').exists()).toBe(true);
    });

    it('legend swatch color matches the edge stroke', () => {
        const wrapper = mountMaster();
        const stroke = wrapper.find('[data-edge="Iron Ore"] line').attributes('stroke');
        // jsdom serializes inline styles as rgb(...) — normalize the hex stroke
        const [r, g, b] = [1, 3, 5].map((i) => Number.parseInt(stroke.slice(i, i + 2), 16));
        const swatches = wrapper.findAll('[data-legend-swatch]');
        const labels = wrapper.findAll('[data-master-legend] li label');
        const index = labels.findIndex((label) => label.text() === 'Iron Ore');

        expect(swatches[index].attributes('style')).toContain(`background-color: rgb(${r}, ${g}, ${b})`);
    });
});

describe('MasterDiagram — semantic zoom (V142)', () => {
    it('renders black boxes at fit view, footprint interiors once zoomed past the threshold', async () => {
        const wrapper = mountMaster();

        // jsdom fit view: node width = NODE_W px < threshold ⇒ black box
        expect(wrapper.findAll('[data-node-box]')).toHaveLength(2);
        expect(wrapper.findComponent(DiagramSvg).exists()).toBe(false);

        // black-box label = step number + name — no monogram, no ×N count
        const ingot = wrapper.find('[data-node="1.A"]');
        expect(ingot.find('[data-node-box-id]').text()).toBe('1.A');
        expect(ingot.find('[data-node-box-name]').text()).toBe('Iron Ingot');
        expect(ingot.text()).not.toContain('S');
        expect(ingot.text()).not.toContain('×');

        await zoomIn(wrapper, 5);

        // footprint-bearing node switches to its nested buildings svg
        const detail = wrapper.find('[data-node="1.A"]').findComponent(DiagramSvg);
        expect(detail.exists()).toBe(true);
        expect(detail.props('nested')).toBe(true);
        expect(detail.props('blueprint')).toBe(true);
        expect(detail.props('footprint')).toEqual(footprint());
        // footprint-less node stays a black box
        expect(wrapper.find('[data-node="0.A"] [data-node-box]').exists()).toBe(true);
    });

    it('layout is stable across the detail switch: bounds coordinates unchanged', async () => {
        const wrapper = mountMaster();
        const before = wrapper
            .findAll('[data-node-bounds]')
            .map((rect) => [rect.attributes('x'), rect.attributes('y')]);

        await zoomIn(wrapper, 5);

        expect(
            wrapper.findAll('[data-node-bounds]').map((rect) => [rect.attributes('x'), rect.attributes('y')])
        ).toEqual(before);
    });
});

describe('MasterDiagram — modal chrome (V128/V114)', () => {
    it('renders the drawing sheet with a MASTER DIAGRAM title', () => {
        const wrapper = mountMaster();

        expect(wrapper.find('[data-drawing-sheet]').exists()).toBe(true);
        expect(wrapper.find('h2').text()).toBe('Full Production Diagram');
    });

    it('wheel zoom shrinks the viewBox window; Escape closes', async () => {
        const wrapper = mountMaster();
        // the header Heroicons are svgs too — target the stage svg explicitly
        const widthOf = () =>
            Number(wrapper.find('[aria-label="Full production diagram"]').attributes('viewBox').split(' ')[2]);
        const before = widthOf();

        await wrapper.find('.touch-none').trigger('wheel', { deltaY: -100 });

        expect(widthOf()).toBeLessThan(before);

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('close')).toBeTruthy();
    });
});
