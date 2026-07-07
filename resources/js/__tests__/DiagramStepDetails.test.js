import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import '../bootstrap';
import DiagramModal from '../Pages/Production/DiagramModal.vue';
import ProductionStep from '../Pages/Production/ProductionStep.vue';
import RecycleStep from '../Pages/Production/RecycleStep.vue';

const mockBus = { on: vi.fn(), emit: vi.fn() };

const footprint = () => ({
    monogram: 'C',
    num_buildings: 2,
    rows: 1,
    buildings_per_row: 2,
    building_length: 8,
    building_width: 10,
    length_foundations: 4,
    width_foundations: 6,
    foundations: 24,
    walls: 20,
    height_walls: 3,
    row_spacing: 0,
    row_spacing_offset: 16,
    top_offset: 12,
    bottom_offset: 12,
    left_offset: 14,
    building_top_offset: 0,
    belt_speed: 780,
    belt_load: 120,
    belt_load_in: 60,
    belt_load_out: 30,
    belt_utilization_in: 8,
    belt_utilization_out: 4,
    power_shards: 0,
    somersloops: 0,
});

const detailsPayload = () => ({
    output: { name: 'Reinforced Iron Plate', qty: 10 },
    byproducts: { Slag: 2.5 },
    inputs: { 'Iron Plate': 60, Screw: 120 },
    building: 'Assembler',
    variant: 'mk1',
    num_buildings: 2,
    clock_speed: 150,
    power_usage: 40,
    energy_per_item: 90,
    total_energy: 900,
    build_cost: { 'Reinforced Iron Plate': 8, Cable: 20 },
    slots: 1,
    max_slots: 2,
});

const mountModal = (details = null) => mount(DiagramModal, { props: { footprint: footprint(), details } });

describe('DiagramModal — step-details panel (V126)', () => {
    it('renders output, inputs, byproducts, machine line, energy, and build cost from the payload', () => {
        const wrapper = mountModal(detailsPayload());

        const panel = wrapper.find('[data-step-details]');
        expect(panel.exists()).toBe(true);

        expect(panel.find('[data-details-output]').text()).toContain('10 per min — Reinforced Iron Plate');
        expect(panel.find('[data-details-byproducts]').text()).toContain('2.5 per min — Slag');

        const inputs = panel.find('[data-details-inputs]').text();
        expect(inputs).toContain('60 per min — Iron Plate');
        expect(inputs).toContain('120 per min — Screw');

        const machines = panel.find('[data-details-building]').text();
        expect(machines).toContain('2× Assembler (mk1) @150% [40 MW]');
        expect(panel.find('[data-details-energy]').text()).toContain('90 MJ per item — 900 MJ total');

        const buildCost = panel.find('[data-details-build-cost]').text();
        expect(buildCost).toContain('8× Reinforced Iron Plate');
        expect(buildCost).toContain('20× Cable');
    });

    it('gates the somersloop line on max_slots', () => {
        const withSlots = mountModal(detailsPayload());
        expect(withSlots.find('[data-details-sloops]').text()).toContain('1/2 per machine');

        const noSlots = mountModal({ ...detailsPayload(), slots: 0, max_slots: 0 });
        expect(noSlots.find('[data-details-sloops]').exists()).toBe(false);
    });

    it('renders no details section without the prop (standalone mounts unchanged)', () => {
        const wrapper = mountModal();

        expect(wrapper.find('[data-step-details]').exists()).toBe(false);
    });
});

describe('ProductionStep — threads the details payload to the diagram (V126)', () => {
    const overviews = {
        'Iron Plate|Iron Plate': {
            clock: 'c100',
            selected_variant_name: 'mk1',
            overviews: {
                c100: {
                    building: 'Smelter',
                    qty: 30,
                    details: {
                        mk1: {
                            footprint: footprint(),
                            num_buildings: 1,
                            clock_speed: 100,
                            power_usage: 4,
                            energy_per_item: 8,
                            total_energy: 240,
                            build_cost: { 'Iron Rod': 5 },
                            slots: 0,
                            max_slots: 1,
                        },
                    },
                },
            },
        },
    };

    it('builds the payload from the selected overview variant and the step materials', () => {
        const wrapper = mount(ProductionStep, {
            global: {
                mocks: { Bus: mockBus },
                stubs: { CloudImage: true, BuildDiagram: true, RecipePicker: true },
            },
            props: {
                name: 'Iron Plate',
                production: {
                    name: 'Iron Plate',
                    recipe: { product: { name: 'Iron Plate' }, description: null, base_per_min: 30 },
                    qty: 30,
                    overridden: false,
                    byproducts: {},
                    ingredients: { 'Iron Ore': 30 },
                    imported: false,
                },
                material: { outputs: {} },
                allMaterials: { 'Iron Ore': 30 },
                byproductsUsed: {},
                newImports: {},
                importNotes: {},
                recipes: {},
                overviews,
                buildingMultiples: {},
                levelIndex: 1,
                stepIndex: 0,
                levelStepMap: {},
                diagrams: false,
            },
        });

        const details = wrapper.findComponent({ name: 'build-diagram' }).props('details');

        expect(details.output).toEqual({ name: 'Iron Plate', qty: 30 });
        expect(details.inputs).toEqual({ 'Iron Ore': 30 });
        expect(details.building).toBe('Smelter');
        expect(details.variant).toBe('mk1');
        expect(details.num_buildings).toBe(1);
        expect(details.power_usage).toBe(4);
        expect(details.build_cost).toEqual({ 'Iron Rod': 5 });
        expect(details.max_slots).toBe(1);
    });
});

describe('RecycleStep — threads the sink details payload (V126)', () => {
    it('builds the payload from the sink step fields with a points/min output', () => {
        const wrapper = mount(RecycleStep, {
            global: {
                mocks: { Bus: mockBus },
                stubs: { CloudImage: true, BuildDiagram: true },
            },
            props: {
                step: {
                    type: 'sink',
                    name: 'AWESOME Sink',
                    building: 'AWESOME Sink',
                    num_buildings: 3,
                    power: 90,
                    build_cost: { Cable: 90 },
                    inputs: [{ item: 'Iron Rod', qty: 45 }],
                    output: { points: 1000 },
                    footprint: footprint(),
                },
                identifier: 'R.1',
                diagrams: false,
            },
        });

        const details = wrapper.findComponent({ name: 'build-diagram' }).props('details');

        expect(details.output).toEqual({ name: 'AWESOME Sink', qty: 1000, unit: 'points/min' });
        expect(details.inputs).toEqual({ 'Iron Rod': 45 });
        expect(details.num_buildings).toBe(3);
        expect(details.power_usage).toBe(90);
        expect(details.build_cost).toEqual({ Cable: 90 });
        expect(details.max_slots).toBe(0);
    });
});
