import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import BuildDiagram from '../Pages/Production/BuildDiagram.vue';
import DiagramModal from '../Pages/Production/DiagramModal.vue';
import DiagramStepDetails from '../Pages/Production/DiagramStepDetails.vue';
import { diagramRegistry, registerDiagram, resetDiagramRegistry } from '../diagramRegistry';

const baseFootprint = () => ({
    monogram: 'C',
    num_buildings: 4,
    rows: 1,
    buildings_per_row: 4,
    building_length: 8,
    building_length_foundations: 1,
    building_width: 10,
    length_m: 8,
    length_foundations: 4,
    width_m: 40,
    width_foundations: 9,
    height_m: 8,
    height_walls: 3,
    foundations: 36,
    walls: 78,
    foundation_border_y: false,
    row_spacing: 0,
    row_spacing_offset: 16,
    top_offset: 8,
    bottom_offset: 8,
    left_offset: 16,
    building_top_offset: 0,
    belt_speed: 780,
    belt_load_in: 240,
    belt_load_out: 120,
    belt_utilization_in: 31,
    belt_utilization_out: 15,
    power_shards: 0,
    somersloops: 0,
});

const baseDetails = () => ({
    output: { name: 'Copper Ingot', qty: 120 },
    byproducts: {},
    inputs: { 'Copper Ore': 120 },
    building: 'Smelter',
    variant: 'mk1',
    num_buildings: 4,
    clock_speed: 100,
    power_usage: 16,
    energy_per_item: 8,
    total_energy: 960,
    build_cost: {},
    slots: 0,
    max_slots: 1,
});

beforeEach(() => resetDiagramRegistry());

describe('diagramRegistry (V130)', () => {
    it('registers entries and removes them via the returned closure', () => {
        const unregister = registerDiagram({ identifier: '1.A', name: 'Copper Ingot', open: () => {} });

        expect(diagramRegistry).toHaveLength(1);
        expect(diagramRegistry[0].identifier).toBe('1.A');

        unregister();
        expect(diagramRegistry).toHaveLength(0);

        // second call is a no-op, not an error
        unregister();
        expect(diagramRegistry).toHaveLength(0);
    });
});

describe('BuildDiagram — registry integration (V130)', () => {
    it('registers on mount with its identifier, title, and check key, unregisters on unmount', () => {
        const wrapper = mount(BuildDiagram, {
            props: {
                footprint: baseFootprint(),
                identifier: '2.B',
                title: 'Copper Ingot',
                checkKey: 'Copper Ingot-base',
            },
        });

        expect(diagramRegistry).toHaveLength(1);
        // checkKey rides along for nav ✅ markers (V144/V146)
        expect(diagramRegistry[0]).toMatchObject({
            identifier: '2.B',
            name: 'Copper Ingot',
            checkKey: 'Copper Ingot-base',
        });

        wrapper.unmount();
        expect(diagramRegistry).toHaveLength(0);
    });

    it('does not register without an identifier (standalone mounts)', () => {
        mount(BuildDiagram, { props: { footprint: baseFootprint() } });

        expect(diagramRegistry).toHaveLength(0);
    });

    it('opens its modal when the registered open() fires', async () => {
        const wrapper = mount(BuildDiagram, {
            props: { footprint: baseFootprint(), identifier: '2.B', title: 'Copper Ingot' },
        });

        expect(wrapper.findComponent(DiagramModal).exists()).toBe(false);

        diagramRegistry[0].open();
        await wrapper.vm.$nextTick();

        expect(wrapper.findComponent(DiagramModal).exists()).toBe(true);
    });
});

describe('DiagramModal — step cell + nav panel (V130)', () => {
    const mountModal = () =>
        mount(DiagramModal, {
            props: { footprint: baseFootprint(), identifier: '2.B', title: 'Copper Ingot' },
        });

    it('shows the step identifier in its own header compartment before the title', () => {
        const wrapper = mountModal();

        const cell = wrapper.find('[data-step-cell]');
        expect(cell.exists()).toBe(true);
        expect(cell.text()).toContain('2.B');
    });

    it('hides the step compartment without an identifier', () => {
        const wrapper = mount(DiagramModal, { props: { footprint: baseFootprint() } });

        expect(wrapper.find('[data-step-cell]').exists()).toBe(false);
    });

    it('lists registry entries sorted by identifier, highlighting the current step', () => {
        registerDiagram({ identifier: '3.A', name: 'Iron Plate', open: () => {} });
        registerDiagram({ identifier: '1.A', name: 'Iron Ingot', open: () => {} });
        registerDiagram({ identifier: '2.B', name: 'Copper Ingot', open: () => {} });

        const wrapper = mountModal();

        const items = wrapper.findAll('[data-modal-nav-item]');
        expect(items.map((i) => i.text())).toEqual(['1.A Iron Ingot', '2.B Copper Ingot', '3.A Iron Plate']);
        expect(items[1].attributes('data-current')).toBeDefined();
        expect(items[0].attributes('data-current')).toBeUndefined();
    });

    it('clicking another step closes this modal and opens the target', async () => {
        const open = vi.fn();
        registerDiagram({ identifier: '1.A', name: 'Iron Ingot', open });

        const wrapper = mountModal();
        await wrapper.find('[data-modal-nav-item]').trigger('click');

        expect(open).toHaveBeenCalledOnce();
        expect(wrapper.emitted('close')).toHaveLength(1);
    });

    it('collapses the nav panel body behind the toggle', async () => {
        registerDiagram({ identifier: '1.A', name: 'Iron Ingot', open: () => {} });

        const wrapper = mountModal();
        expect(wrapper.find('[data-modal-nav-item]').exists()).toBe(true);

        await wrapper.find('[data-modal-nav-toggle]').trigger('click');
        expect(wrapper.find('[data-modal-nav-item]').exists()).toBe(false);

        await wrapper.find('[data-modal-nav-toggle]').trigger('click');
        expect(wrapper.find('[data-modal-nav-item]').exists()).toBe(true);
    });

    // V141d: nav prepends a Master Diagram jump when the opener is provided
    it('shows the Master Diagram entry only with an injected opener; click opens + closes', async () => {
        expect(mountModal().find('[data-modal-nav-master]').exists()).toBe(false);

        const openMasterDiagram = vi.fn();
        const wrapper = mount(DiagramModal, {
            props: { footprint: baseFootprint(), identifier: '2.B', title: 'Copper Ingot' },
            global: { provide: { openMasterDiagram } },
        });

        await wrapper.find('[data-modal-nav-master]').trigger('click');

        expect(openMasterDiagram).toHaveBeenCalledOnce();
        expect(wrapper.emitted('close')).toHaveLength(1);
    });
});

describe('DiagramStepDetails — material jump links (V130)', () => {
    const mountDetails = () => mount(DiagramStepDetails, { props: { details: baseDetails(), identifier: '2.B' } });

    it('links an input to its producing step and emits navigate on click', async () => {
        registerDiagram({ identifier: '1.A', name: 'Copper Ore', open: () => {} });

        const wrapper = mountDetails();
        const link = wrapper.find('[data-details-inputs] [data-material-link]');

        expect(link.exists()).toBe(true);
        expect(link.text()).toContain('Copper Ore');

        await link.trigger('click');
        expect(wrapper.emitted('navigate')).toHaveLength(1);
        expect(wrapper.emitted('navigate')[0][0]).toMatchObject({ identifier: '1.A' });
    });

    it('renders plain text when no producing step is registered (imports)', () => {
        const wrapper = mountDetails();

        expect(wrapper.find('[data-details-inputs] [data-material-link]').exists()).toBe(false);
        expect(wrapper.find('[data-details-inputs]').text()).toContain('Copper Ore');
    });

    it('never links the output to itself', () => {
        registerDiagram({ identifier: '2.B', name: 'Copper Ingot', open: () => {} });

        const wrapper = mountDetails();

        expect(wrapper.find('[data-details-output] [data-material-link]').exists()).toBe(false);
        expect(wrapper.find('[data-details-output]').text()).toContain('Copper Ingot');
    });
});
