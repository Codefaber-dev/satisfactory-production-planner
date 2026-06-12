import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, shallowMount } from '@vue/test-utils';
import '../bootstrap';
import BuildDiagram from '../Pages/Production/BuildDiagram.vue';
import ProductionStep from '../Pages/Production/ProductionStep.vue';
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

describe('BuildDiagram — grouped render (V41)', () => {
    it('shows blueprint tiles labeled monogram x size and blueprint stat rows', () => {
        const footprint = groupedFootprint(baseFootprint(), 8, 32);
        const wrapper = mount(BuildDiagram, { props: { footprint } });

        expect(wrapper.text()).toContain('Cx8');
        expect(wrapper.text()).toContain('Blueprints');
        expect(wrapper.text()).toContain('2 (8 ea)'.replace(/\s+/g, ' ').trim().split(' ')[0]);
        expect(wrapper.text()).toContain('Blueprint Rows');
        expect(wrapper.text()).toContain('Blueprints Per Row');
        expect(wrapper.text()).not.toContain('Building Rows');
    });

    it('keeps individual-building render for ungrouped footprints', () => {
        const wrapper = mount(BuildDiagram, { props: { footprint: baseFootprint() } });

        expect(wrapper.text()).toContain('Building Rows');
        expect(wrapper.text()).toContain('Buildings Per Row');
        expect(wrapper.text()).not.toContain('Blueprints');
        expect(wrapper.text()).not.toContain('Cx');
    });
});

const stepKey = 'Iron Rod|Iron Rod';

const makeStepWrapper = (props = {}) => {
    const footprint = baseFootprint();
    const details = {
        mk1: {
            variant: 'mk1',
            num_buildings: 16,
            clock_speed: 100,
            power_usage: 64,
            max_clock_speed: 100,
            max_slots: 0,
            slots: 0,
            footprint,
        },
    };

    const overviews = {
        [stepKey]: {
            clock: 'c100',
            selected_variant_name: 'mk1',
            overviews: {
                c100: {
                    building: 'Constructor',
                    qty: 240,
                    details,
                    selected_variant: details.mk1,
                },
            },
        },
    };

    return shallowMount(ProductionStep, {
        global: {
            mocks: {
                Bus: { on: vi.fn(), emit: vi.fn() },
            },
            stubs: {
                'cloud-image': true,
            },
        },
        props: {
            name: 'Iron Rod',
            production: {
                recipe: { product: { name: 'Iron Rod' }, description: null },
                qty: 240,
                ingredients: {},
                byproducts: {},
                overridden: false,
            },
            material: { outputs: {} },
            allMaterials: {},
            recipes: {},
            choices: {},
            diagrams: true,
            newImports: {},
            overviews,
            byproductsUsed: {},
            stepIndex: 0,
            levelIndex: 0,
            levelStepMap: {},
            finished: false,
            somersloopSlots: {},
            ...props,
        },
    });
};

describe('ProductionStep — grouped footprint hand-off (V41, V43)', () => {
    beforeEach(() => window.localStorage.clear());

    it('passes a grouped footprint to the diagram when the building type is enabled', () => {
        const wrapper = makeStepWrapper({ buildingMultiples: { Constructor: 8 }, designerMk: 'mk1' });

        const fp = wrapper.findComponent(BuildDiagram).props('footprint');

        expect(fp.grouped).toBe(true);
        expect(fp.monogram).toBe('Cx8');
        expect(fp.num_buildings).toBe(2);
        expect(fp.building_length).toBe(32);
    });

    it('sizes tiles from the designer mark prop', () => {
        const wrapper = makeStepWrapper({ buildingMultiples: { Constructor: 8 }, designerMk: 'mk3' });

        expect(wrapper.findComponent(BuildDiagram).props('footprint').building_length).toBe(48);
    });

    it('falls back to mk1 dims on unknown designer mark', () => {
        const wrapper = makeStepWrapper({ buildingMultiples: { Constructor: 8 }, designerMk: 'mk9' });

        expect(wrapper.findComponent(BuildDiagram).props('footprint').building_length).toBe(32);
    });

    it('passes the backend footprint through untouched when grouping is off', () => {
        const wrapper = makeStepWrapper({ buildingMultiples: {} });

        const fp = wrapper.findComponent(BuildDiagram).props('footprint');

        expect(fp).toEqual(baseFootprint());
        expect(fp.grouped).toBeUndefined();
    });

    it('ignores multiples for other building types', () => {
        const wrapper = makeStepWrapper({ buildingMultiples: { Smelter: 10 } });

        expect(wrapper.findComponent(BuildDiagram).props('footprint')).toEqual(baseFootprint());
    });
});
