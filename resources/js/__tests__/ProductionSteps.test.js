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
                    production: [
                        { name: 'Iron Ore', recipe: null, qty: 120, ingredients: {}, byproducts: {}, outputs: {} },
                    ],
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
                    template:
                        '<tbody class="stub-recycle" :data-test="\'recycle-step\'" :data-name="step.name" :data-id="identifier" :data-sink="sinkIdentifier" />',
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

describe('ProductionSteps — Level 0 raws (V100)', () => {
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
        const ironOreSteps = wrapper.findAll('.stub-step').filter((s) => s.attributes('data-name') === 'Iron Ore');

        // exactly one Iron Ore step, at Level 0 (not duplicated in tier-1 level)
        expect(ironOreSteps).toHaveLength(1);
    });

    it('maps a raw into levelStepMap at level 0', () => {
        const vm = mountSteps().vm;
        expect(vm.levelStepMap['Iron Ore']).toBe('0.A');
    });
});

describe('ProductionSteps — Recycling steps (T184 / V137)', () => {
    // V137: packaging + container-chain production render as normal plan steps
    // inside results — the backend recycling_steps carries ONLY the sink.
    function withSteps() {
        return mountSteps({
            production: {
                ...makeProduction(),
                recycling_steps: [
                    {
                        type: 'sink',
                        name: 'AWESOME Sink',
                        building: 'AWESOME Sink',
                        num_buildings: 1,
                        power: 30,
                        footprint: {},
                        inputs: [{ item: 'Packaged Water', qty: 60 }],
                        output: { points: 7800 },
                    },
                ],
            },
        });
    }

    it('renders only the sink RecycleStep in the Recycling group (V137)', () => {
        const wrapper = withSteps();
        const steps = wrapper.findAll('[data-test="recycle-step"]');
        expect(steps).toHaveLength(1);

        // the terminal AWESOME Sink is a RecycleStep
        const sink = wrapper.find('.stub-recycle[data-name="AWESOME Sink"]');
        expect(sink.exists()).toBe(true);

        // no ProductionStep is rendered from recycling_steps — packaging/chain
        // steps arrive as normal results steps instead
        const stubNames = wrapper.findAll('.stub-step').map((s) => s.attributes('data-name'));
        expect(stubNames).not.toContain('Packaged Water');
    });

    it('assigns the terminal R.* identifier and maps the sink into levelStepMap', () => {
        const wrapper = withSteps();
        expect(wrapper.vm.recycleIdentifier(0)).toBe('R.A');
        // V137: plan steps outputting to the AWESOME Sink link to the sink step
        expect(wrapper.vm.levelStepMap['AWESOME Sink']).toBe('R.A');
    });

    it('renders no Recycling level when there are no recycling steps', () => {
        const wrapper = mountSteps({ production: { ...makeProduction(), recycling_steps: [] } });
        expect(wrapper.findAll('[data-test="recycle-step"]')).toHaveLength(0);
    });
});

describe('ProductionSteps — header controls (T157 / V112)', () => {
    it('no longer renders Force Even Rows or Limit Belt Rates in the header', () => {
        const wrapper = mountSteps();
        const buttonText = wrapper
            .findAll('button')
            .map((b) => b.text())
            .join(' ');

        expect(buttonText).not.toContain('Toggle Diagrams'); // modal-only diagrams (V134)
        expect(buttonText).not.toContain('Force Even Rows');
        expect(buttonText).not.toContain('Limit Belt Rates');
    });
});

describe('ProductionSteps — type-ahead step filter (T156 / V110)', () => {
    // Iron Ingot gains a byproduct so options cover product / input / byproduct kinds
    function makeFilterProduction() {
        const production = makeProduction();
        production.results[2]['Iron Ingot'].production[0].byproducts = { Slag: 10 };
        return production;
    }

    function findStep(wrapper, name) {
        return wrapper.findAll('.stub-step').find((s) => s.attributes('data-name') === name);
    }

    function findHeader(wrapper, text) {
        return wrapper.findAll('tr').find((tr) => tr.text() === text);
    }

    // V111/B62: assert the inline v-show style, NOT isVisible() — VTU isVisible()
    // reads jsdom's per-element getComputedStyle cache, which off-document mounts
    // never invalidate after a reactive toggle (false-red on hide, false-green on show)
    function isShown(elementWrapper) {
        return elementWrapper.element.style.display !== 'none';
    }

    async function setFilter(wrapper, item) {
        wrapper.vm.filterItem = item;
        await wrapper.vm.$nextTick();
    }

    it('offers every product, input ingredient, and byproduct of the plan, sorted', () => {
        const wrapper = mountSteps({ production: makeFilterProduction() });
        expect(wrapper.vm.filterOptions).toEqual(['Iron Ingot', 'Iron Ore', 'Slag']);
    });

    it('shows only steps containing the selected item as input or output', async () => {
        const wrapper = mountSteps({ production: makeFilterProduction() });

        // Iron Ore: raw step matches by name, Iron Ingot matches by ingredient input
        await setFilter(wrapper, 'Iron Ore');
        expect(isShown(findStep(wrapper, 'Iron Ore'))).toBe(true);
        expect(isShown(findStep(wrapper, 'Iron Ingot'))).toBe(true);

        // Slag: only the Iron Ingot step (byproduct output) matches
        await setFilter(wrapper, 'Slag');
        expect(isShown(findStep(wrapper, 'Iron Ore'))).toBe(false);
        expect(isShown(findStep(wrapper, 'Iron Ingot'))).toBe(true);

        // Iron Ingot: only its own step (product output) matches
        await setFilter(wrapper, 'Iron Ingot');
        expect(isShown(findStep(wrapper, 'Iron Ore'))).toBe(false);
        expect(isShown(findStep(wrapper, 'Iron Ingot'))).toBe(true);
    });

    it('hides a level header when the filter leaves it no visible steps', async () => {
        const wrapper = mountSteps({ production: makeFilterProduction() });

        await setFilter(wrapper, 'Slag');
        expect(isShown(findHeader(wrapper, 'Level 0'))).toBe(false);
        expect(isShown(findHeader(wrapper, 'Level 1'))).toBe(true);
    });

    it('clearing the filter restores every step and header', async () => {
        const wrapper = mountSteps({ production: makeFilterProduction() });

        await setFilter(wrapper, 'Slag');
        await setFilter(wrapper, null);

        expect(isShown(findStep(wrapper, 'Iron Ore'))).toBe(true);
        expect(isShown(findStep(wrapper, 'Iron Ingot'))).toBe(true);
        expect(isShown(findHeader(wrapper, 'Level 0'))).toBe(true);
    });

    it('never reveals a step hidden by hideCompleted', async () => {
        const wrapper = mountSteps({
            production: makeFilterProduction(),
            hideCompleted: true,
            productionChecks: { 'Iron Ingot-base': true },
        });

        await setFilter(wrapper, 'Iron Ore');
        expect(isShown(findStep(wrapper, 'Iron Ingot'))).toBe(false);
    });

    it('never reveals an imported step', async () => {
        const production = makeFilterProduction();
        production.results[2]['Iron Ingot'].production[0].imported = true;
        const wrapper = mountSteps({ production });

        await setFilter(wrapper, 'Iron Ore');
        expect(isShown(findStep(wrapper, 'Iron Ingot'))).toBe(false);
    });

    it('filters the sink step on its inputs', async () => {
        // V137: recycling_steps carries only the sink; its inputs still join
        // the filter options and drive its visibility
        const wrapper = mountSteps({
            production: {
                ...makeFilterProduction(),
                recycling_steps: [
                    {
                        type: 'sink',
                        name: 'AWESOME Sink',
                        building: 'AWESOME Sink',
                        num_buildings: 1,
                        power: 30,
                        footprint: {},
                        inputs: [{ item: 'Packaged Water', qty: 60 }],
                        output: { points: 7800 },
                    },
                ],
            },
        });

        // sink inputs join the options
        expect(wrapper.vm.filterOptions).toContain('Packaged Water');

        // Packaged Water: the sink (input) matches
        await setFilter(wrapper, 'Packaged Water');
        expect(isShown(wrapper.find('.stub-recycle[data-name="AWESOME Sink"]'))).toBe(true);

        // no recycle step matches → Recycling header hides
        await setFilter(wrapper, 'Iron Ingot');
        expect(isShown(findHeader(wrapper, 'Recycling (AWESOME Sink)'))).toBe(false);
    });
});

describe('ProductionSteps — master diagram entry (V139)', () => {
    it('header button opens the master diagram modal; close event removes it', async () => {
        const wrapper = mountSteps();

        expect(wrapper.findComponent({ name: 'master-diagram' }).exists()).toBe(false);

        await wrapper.find('[data-master-diagram-button]').trigger('click');

        const master = wrapper.findComponent({ name: 'master-diagram' });
        expect(master.exists()).toBe(true);
        // ProductionSteps' own computed shapes flow in as props
        expect(Object.keys(master.props('rawSteps'))).toEqual(['Iron Ore']);
        expect(master.props('levels')).toHaveLength(1);

        await master.vm.$emit('close');

        expect(wrapper.findComponent({ name: 'master-diagram' }).exists()).toBe(false);
    });
});

describe('ProductionSteps — raw-row check parity (V147, B71)', () => {
    const findIronOre = (wrapper) =>
        wrapper.findAll('.stub-step').find((s) => s.attributes('data-name') === 'Iron Ore');

    it('passes :finished and dims a checked raw row', () => {
        const wrapper = mountSteps({
            hideCompleted: false,
            productionChecks: { 'Iron Ore-base': true },
        });
        const ironOre = findIronOre(wrapper);

        expect(ironOre.classes()).toContain('opacity-25');
        expect(ironOre.attributes('finished')).toBe('true');
        expect(ironOre.attributes('style') || '').not.toContain('display: none');
    });

    it('hideCompleted hides a checked raw row', () => {
        const wrapper = mountSteps({
            hideCompleted: true,
            productionChecks: { 'Iron Ore-base': true },
        });

        expect(findIronOre(wrapper).attributes('style')).toContain('display: none');
    });

    it('leaves an unchecked raw row visible and undimmed under hideCompleted', () => {
        const wrapper = mountSteps({ hideCompleted: true, productionChecks: {} });
        const ironOre = findIronOre(wrapper);

        expect(ironOre.attributes('style') || '').not.toContain('display: none');
        expect(ironOre.classes()).toContain('opacity-100');
    });
});
