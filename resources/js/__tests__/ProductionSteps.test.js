import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import '../bootstrap';
import ProductionSteps from '../Pages/Production/ProductionSteps.vue';

const mockBus = { on: vi.fn(), emit: vi.fn() };

// results: tier 1 holds a raw (Iron Ore), tier 2 a recipe step (Iron Ingot)
function makeProduction() {
    return {
        all_materials: {},
        byproducts_used: {},
        results: {
            1: {
                'Iron Ore': {
                    raw: true,
                    imported: false,
                    total: 120,
                    outputs: {},
                    production: [{ name: 'Iron Ore', recipe: null, qty: 120, ingredients: {}, byproducts: {}, outputs: {} }],
                },
            },
            2: {
                'Iron Ingot': {
                    raw: false,
                    imported: false,
                    total: 120,
                    outputs: {},
                    production: [
                        {
                            name: 'Iron Ingot',
                            recipe: { product: { name: 'Iron Ingot' }, description: null, base_per_min: 30 },
                            qty: 120,
                            ingredients: { 'Iron Ore': 120 },
                            byproducts: {},
                            outputs: {},
                            imported: false,
                        },
                    ],
                },
            },
        },
    };
}

function mountSteps(extraProps = {}) {
    return mount(ProductionSteps, {
        global: {
            mocks: { Bus: mockBus },
            stubs: {
                ProductionStep: {
                    name: 'ProductionStep',
                    props: ['name', 'levelIndex', 'material'],
                    template: '<tbody class="stub-step" :data-name="name" :data-level="levelIndex" />',
                },
                RecycleStep: {
                    name: 'RecycleStep',
                    props: ['step', 'identifier', 'stepMap', 'sinkIdentifier'],
                    template: '<tbody class="stub-recycle" :data-test="\'recycle-step\'" :data-name="step.name" :data-id="identifier" :data-sink="sinkIdentifier" />',
                },
            },
        },
        props: {
            production: makeProduction(),
            productionChecks: {},
            recipes: {},
            choices: {},
            overviews: {},
            newImports: {},
            rawSources: {},
            diagrams: false,
            ...extraProps,
        },
    });
}

describe('ProductionSteps — Level 0 raws (V72)', () => {
    it('collects raw materials into rawSteps', () => {
        const vm = mountSteps().vm;
        expect(Object.keys(vm.rawSteps)).toEqual(['Iron Ore']);
    });

    it('renders the raw as a Level-0 step', () => {
        const wrapper = mountSteps();
        const steps = wrapper.findAll('.stub-step');
        const ironOre = steps.find((s) => s.attributes('data-name') === 'Iron Ore');

        expect(ironOre).toBeTruthy();
        expect(ironOre.attributes('data-level')).toBe('0');
    });

    it('does not render a raw in the normal (non-zero) levels', () => {
        const wrapper = mountSteps();
        const ironOreSteps = wrapper
            .findAll('.stub-step')
            .filter((s) => s.attributes('data-name') === 'Iron Ore');

        // exactly one Iron Ore step, at Level 0 (not duplicated in tier-1 level)
        expect(ironOreSteps).toHaveLength(1);
    });

    it('maps a raw into levelStepMap at level 0', () => {
        const vm = mountSteps().vm;
        expect(vm.levelStepMap['Iron Ore']).toBe('0.A');
    });
});

describe('ProductionSteps — Recycling steps (T127 / V88)', () => {
    function withSteps() {
        return mountSteps({
            production: {
                ...makeProduction(),
                recycling_steps: [
                    { type: 'package', name: 'Packaged Water', building: 'Packager', num_buildings: 1, power: 10, footprint: {}, inputs: [{ item: 'Water', qty: 60 }], output: { item: 'Packaged Water', qty: 60, to_sink: true } },
                    { type: 'sink', name: 'AWESOME Sink', building: 'AWESOME Sink', num_buildings: 1, power: 30, footprint: {}, inputs: [{ item: 'Packaged Water', qty: 60 }], output: { points: 7800 } },
                ],
            },
        });
    }

    it('renders a RecycleStep per backend recycling step', () => {
        const steps = withSteps().findAll('[data-test="recycle-step"]');
        expect(steps).toHaveLength(2);
        expect(steps[0].attributes('data-name')).toBe('Packaged Water');
        expect(steps[1].attributes('data-name')).toBe('AWESOME Sink');
    });

    it('assigns terminal R.* identifiers and a sink identifier', () => {
        const wrapper = withSteps();
        expect(wrapper.vm.recycleIdentifier(0)).toBe('R.A');
        expect(wrapper.vm.sinkIdentifier).toBe('R.B');
        // the packaging step is reachable in the map so the sink's input links to it
        expect(wrapper.vm.recycleStepMap['Packaged Water']).toBe('R.A');
    });

    it('renders no Recycling level when there are no recycling steps', () => {
        const wrapper = mountSteps({ production: { ...makeProduction(), recycling_steps: [] } });
        expect(wrapper.findAll('[data-test="recycle-step"]')).toHaveLength(0);
    });
});
