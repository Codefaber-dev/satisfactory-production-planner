import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import '../bootstrap';
import ProductionStep from '../Pages/Production/ProductionStep.vue';

const mockBus = { on: vi.fn(), emit: vi.fn() };

const overviews = {
    'Iron Plate|Iron Plate': {
        clock: 'c100',
        selected_variant_name: 'mk1',
        overviews: {
            c100: {
                building: 'smelter',
                details: { mk1: { footprint: [], num_buildings: 1, clock_speed: 100, power_usage: 4 } },
            },
        },
    },
};

function makeProduction(imported) {
    return {
        recipe: { product: { name: 'Iron Plate' }, description: null, base_per_min: 30 },
        qty: 30,
        overridden: false,
        byproducts: {},
        ingredients: {},
        imported,
    };
}

function mountStep(imported) {
    return mount(ProductionStep, {
        global: {
            mocks: { Bus: mockBus },
            stubs: { CloudImage: true, BuildDiagram: true, RecipePicker: true },
        },
        props: {
            name: 'Iron Plate',
            production: makeProduction(imported),
            material: { outputs: {} },
            recipes: {},
            overviews,
            buildingMultiples: {},
            levelIndex: 1,
            stepIndex: 0,
            levelStepMap: {},
            diagrams: false,
        },
    });
}

function mountWith(extraProps) {
    return mount(ProductionStep, {
        global: {
            mocks: { Bus: mockBus },
            stubs: { CloudImage: true, BuildDiagram: true, RecipePicker: true },
        },
        props: {
            name: 'Iron Plate',
            production: makeProduction(false),
            material: { outputs: {} },
            recipes: {},
            overviews,
            buildingMultiples: {},
            levelIndex: 1,
            stepIndex: 0,
            levelStepMap: {},
            diagrams: false,
            ...extraProps,
        },
    });
}

describe('ProductionStep — usagePercent (B47 / V71)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('computes the share of total produced material a step consumes', () => {
        const vm = mountWith({ allMaterials: { Rubber: 101.3333 }, byproductsUsed: {} }).vm;
        expect(vm.usagePercent(25.3333, 'Rubber')).toBe(25);
    });

    it('returns null (never Infinity) when the denominator is 0', () => {
        const vm = mountWith({ allMaterials: {}, byproductsUsed: {} }).vm;
        expect(vm.usagePercent(25.3333, 'Rubber')).toBeNull();
    });
});

function makeRawProduction() {
    // import/extract leaf raw: no recipe, no ingredients (V72)
    return {
        recipe: null,
        qty: 120,
        overridden: false,
        byproducts: {},
        ingredients: {},
        imported: false,
    };
}

function mountRawStep(extraProps = {}) {
    return mount(ProductionStep, {
        global: {
            mocks: { Bus: mockBus },
            stubs: { CloudImage: true, BuildDiagram: true, RecipePicker: true },
        },
        props: {
            name: 'Iron Ore',
            production: makeRawProduction(),
            material: { raw: true, outputs: {} },
            recipes: {},
            overviews: {},
            levelIndex: 0,
            stepIndex: 0,
            levelStepMap: {},
            diagrams: false,
            ...extraProps,
        },
    });
}

describe('ProductionStep — recipe-less raw (V72)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('renders a recipe-less raw step without crashing', () => {
        const wrapper = mountRawStep();
        expect(wrapper.exists()).toBe(true);
        expect(wrapper.vm.recipe).toBeNull();
        // keyed by name when there is no recipe
        expect(wrapper.vm.key).toBe('Iron Ore');
    });

    it('does not emit overview updates for a recipe-less raw', () => {
        mountRawStep();
        expect(mockBus.emit).not.toHaveBeenCalledWith('UpdateOverviews', expect.anything());
    });
});

// A raw that became recipe-bearing (V79 convert/unpackage) but whose overview entry
// is not (yet) in the overviews map — must not crash the diagram render (B49).
function makeRawViaRecipeProduction() {
    return {
        recipe: { product: { name: 'Water' }, description: 'Unpackage Water', base_per_min: 120 },
        qty: 120,
        overridden: false,
        byproducts: {},
        ingredients: {},
        imported: false,
    };
}

function mountRawViaRecipe(extraProps = {}) {
    return mount(ProductionStep, {
        global: {
            mocks: { Bus: mockBus },
            stubs: { CloudImage: true, BuildDiagram: true, RecipePicker: true },
        },
        props: {
            name: 'Water',
            production: makeRawViaRecipeProduction(),
            material: { raw: true, outputs: {} },
            recipes: { Water: [{ id: 1, description: 'Unpackage Water' }] },
            overviews: {}, // key 'Water|Unpackage Water' absent (transient on toggle)
            levelIndex: 0,
            stepIndex: 0,
            levelStepMap: {},
            diagrams: true,
            ...extraProps,
        },
    });
}

describe('ProductionStep — recipe-bearing raw with absent overview (B49)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('does not crash when a recipe-bearing raw step has no overview entry', () => {
        const wrapper = mountRawViaRecipe();
        expect(wrapper.exists()).toBe(true);
        expect(wrapper.vm.overview).toBeNull();
        // footprint must not throw on null overview
        expect(() => wrapper.vm.footprint).not.toThrow();
        // diagram is gated off while there is no overview
        expect(wrapper.findComponent({ name: 'build-diagram' }).exists()).toBe(false);
    });

    it('renders the diagram once the overview entry is present', () => {
        const wrapper = mountRawViaRecipe({
            overviews: {
                'Water|Unpackage Water': {
                    clock: 'c100',
                    selected_variant_name: 'mk1',
                    overviews: { c100: { building: 'packager', details: { mk1: { footprint: [], num_buildings: 1, clock_speed: 100, power_usage: 30 } } } },
                },
            },
        });

        expect(wrapper.vm.overview).not.toBeNull();
        expect(wrapper.findComponent({ name: 'build-diagram' }).exists()).toBe(true);
    });
});

describe('ProductionStep — raw source controls on the step (V73)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('renders RawSourceControl on a raw step', () => {
        const wrapper = mountRawStep();
        expect(wrapper.findComponent({ name: 'RawSourceControl' }).exists()).toBe(true);
    });

    it('does NOT render RawSourceControl on a non-raw recipe step', () => {
        const wrapper = mountStep(false);
        expect(wrapper.findComponent({ name: 'RawSourceControl' }).exists()).toBe(false);
    });

    it('re-emits updateRawSource from the control', async () => {
        const wrapper = mountRawStep();
        const control = wrapper.findComponent({ name: 'RawSourceControl' });

        control.vm.$emit('update', { name: 'Iron Ore', config: { mode: 'extract' } });
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('updateRawSource')).toBeTruthy();
        expect(wrapper.emitted('updateRawSource').at(-1)[0]).toEqual({
            name: 'Iron Ore',
            config: { mode: 'extract' },
        });
    });
});

describe('ProductionStep — extractor info to RawSourceControl (V82)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('maps the matched ExtractorSummary row to { count, clock, power }', () => {
        const wrapper = mountRawStep({
            extractors: [{ product: 'Iron Ore', building: 'Miner Mk.2', num_buildings: 3, clock_speed: 100, power_usage: 45 }],
        });

        expect(wrapper.vm.extractor).toEqual({ count: 3, clock: 100, power: 45 });
    });

    it('is null for an import raw (no extractors row)', () => {
        const wrapper = mountRawStep({ extractors: [] });

        expect(wrapper.vm.extractor).toBeNull();
    });

    it('matches the extractor row by raw name', () => {
        const wrapper = mountRawStep({
            name: 'Water',
            extractors: [
                { product: 'Iron Ore', building: 'Miner Mk.2', num_buildings: 3, clock_speed: 100, power_usage: 45 },
                { product: 'Water', building: 'Water Extractor', num_buildings: 5, clock_speed: 100, power_usage: 20 },
            ],
        });

        expect(wrapper.vm.extractor).toEqual({ count: 5, clock: 100, power: 20 });
    });
});

describe('ProductionStep — import note (V64)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('shows the import note on the step when present', () => {
        const wrapper = mountRawStep({ importNotes: { 'Iron Ore': 'From NW factory' } });
        const note = wrapper.find('.import-note');
        expect(note.exists()).toBe(true);
        expect(note.text()).toContain('From NW factory');
    });

    it('hides the note when empty (V64: empty = no display)', () => {
        const wrapper = mountRawStep({ importNotes: {} });
        expect(wrapper.find('.import-note').exists()).toBe(false);
    });

    it('hides the note for a non-imported (extract-mode) raw even if a note exists', () => {
        const wrapper = mountRawStep({
            importNotes: { 'Iron Ore': 'stale note' },
            rawSources: { 'Iron Ore': { mode: 'extract' } },
        });
        expect(wrapper.find('.import-note').exists()).toBe(false);
    });
});

function makeByproductProduction() {
    return {
        recipe: { product: { name: 'Iron Plate' }, description: null, base_per_min: 30 },
        qty: 30,
        overridden: false,
        byproducts: { 'Heavy Oil Residue': 10, Concrete: 20 },
        ingredients: {},
        imported: false,
    };
}

describe('ProductionStep — recycled byproduct indicator (V66)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('shows a recycled badge with points for a recycled byproduct', () => {
        const wrapper = mountWith({
            production: makeByproductProduction(),
            byproductsUsed: {},
            recycling: { recycled: { Concrete: { qty: 20, points: 240 } }, packaged: [], waste: {} },
        });
        const badge = wrapper.find('[data-test="recycled-badge"]');
        expect(badge.exists()).toBe(true);
        expect(badge.text()).toContain('240');
    });

    it('shows no badge when the byproduct is not recycled', () => {
        const wrapper = mountWith({
            production: makeByproductProduction(),
            byproductsUsed: {},
            recycling: { recycled: {}, packaged: [], waste: { 'Heavy Oil Residue': 10 } },
        });
        expect(wrapper.find('[data-test="recycled-badge"]').exists()).toBe(false);
    });
});

describe('ProductionStep — QuickNav registration', () => {
    beforeEach(() => vi.clearAllMocks());

    it('registers a non-imported step in QuickNav', () => {
        mountStep(false);
        expect(mockBus.emit).toHaveBeenCalledWith('RegisterQuickNav', {
            name: 'Iron Plate',
            identifier: '1.A',
        });
    });

    it('V70: does NOT register an imported step in QuickNav (B46)', () => {
        mountStep(true);
        expect(mockBus.emit).not.toHaveBeenCalledWith('RegisterQuickNav', expect.anything());
    });
});
