import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { RocketLaunchIcon, QueueListIcon, BuildingOffice2Icon, PowerIcon } from '@heroicons/vue/24/outline';
import '../bootstrap';
import Show from '../Pages/Production/Show.vue';

const mockInertia = { get: vi.fn(), patch: vi.fn(), post: vi.fn() };
const mockBus = { on: vi.fn(), emit: vi.fn() };

const ironPlate = { id: 1, name: 'Iron Plate', recipes: [] };
const baseRecipe = { id: 10, description: null, alt_recipe: false, product: ironPlate, product_id: 1, favorite: false };
const altRecipe = {
    id: 11,
    description: 'Solid Steel Ingot',
    alt_recipe: true,
    product: ironPlate,
    product_id: 1,
    favorite: false,
};

const minimalProduction = {
    raw_materials: {},
    intermediate_materials: {},
    all_materials: {},
    results: {},
    byproducts_used: {},
    final: [],
    recipe: baseRecipe,
    overrides: {},
    byproducts: {},
    overviews: {},
};

const defaultProps = {
    products: [ironPlate],
    recipes: { 'Iron Plate': [baseRecipe, altRecipe] },
    favorites: [],
    production: minimalProduction,
    product: ironPlate,
    recipe: 'default',
    yield: 30,
    variant: 'mk1',
    belt_speed: 780,
    constraints: [],
    factory: null,
    multiFactory: null,
    imports: '',
    multi: null,
    choices: null,
    even: 0,
    speedLimit: 'both',
};

function makeWrapper(propsOverride = {}) {
    return shallowMount(Show, {
        global: {
            mocks: {
                $inertia: mockInertia,
                $page: { props: { user: { name: 'Test User' } } },
                Bus: mockBus,
            },
            stubs: {
                AppLayout: { template: '<div><slot /><slot id="header" /></div>' },
                ProductionSummary: true,
                ProductionSteps: true,
                BuildingSummary: true,
                ProductionWarning: true,
                ProductionQuickNav: true,
            },
        },
        props: { ...defaultProps, ...propsOverride },
    });
}

describe('Show — T73: saveMyFactory uses allChosenRecipes', () => {
    beforeEach(() => vi.clearAllMocks());

    it('saveMyFactory PATCH sends allChosenRecipes not just newChoices', () => {
        const wrapper = makeWrapper({
            factory: { id: 5, name: 'My Factory' },
            choices: { 'Iron Rod': 'Steel Rod' },
        });

        wrapper.vm.form.outputs[0].recipe = altRecipe;
        wrapper.vm.saveMyFactory();

        const patchCall = mockInertia.patch.mock.calls[0];
        expect(patchCall[0]).toBe('/factories/5');
        const sentChoices = patchCall[1].choices;
        expect(sentChoices['Iron Plate']).toBe('Solid Steel Ingot');
        expect(sentChoices['Iron Rod']).toBe('Steel Rod');
    });

    it('saveMyFactory POST (new factory) sends allChosenRecipes', () => {
        vi.spyOn(window, 'prompt').mockReturnValue('New Factory');
        const wrapper = makeWrapper({ factory: null });

        wrapper.vm.form.outputs[0].recipe = altRecipe;
        wrapper.vm.newChoices = { 'Iron Rod': 'Steel Rod' };
        wrapper.vm.saveMyFactory();

        const postCall = mockInertia.post.mock.calls[0];
        expect(postCall[0]).toBe('/factories');
        const sentChoices = postCall[1].choices;
        expect(sentChoices['Iron Plate']).toBe('Solid Steel Ingot');
        expect(sentChoices['Iron Rod']).toBe('Steel Rod');
    });

    it('saveMultiFactory PATCH sends allChosenRecipes', () => {
        const wire = { id: 2, name: 'Wire', recipes: [] };
        const wireRecipe = {
            id: 20,
            description: 'Fused Wire',
            alt_recipe: true,
            product: wire,
            product_id: 2,
            favorite: false,
        };

        const wrapper = makeWrapper({
            multiFactory: { id: 7, name: 'Multi' },
            multi: {
                products: [ironPlate, wire],
                yields: [30, 60],
                recipes: [baseRecipe, wireRecipe],
            },
            products: [ironPlate, wire],
            recipes: { 'Iron Plate': [baseRecipe], Wire: [wireRecipe] },
        });

        wrapper.vm.newChoices = { 'Iron Rod': 'Steel Rod' };
        wrapper.vm.saveMyFactory();

        const patchCall = mockInertia.patch.mock.calls[0];
        expect(patchCall[0]).toBe('/factories/multi/7');
        const sentChoices = patchCall[1].choices;
        expect(sentChoices['Iron Plate']).toBeDefined();
        expect(sentChoices['Iron Rod']).toBe('Steel Rod');
    });
});

describe('Show — T101: raw_sources plan param (V59)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('params() includes raw_sources for the fetch round-trip', () => {
        const wrapper = makeWrapper({ raw_sources: { 'Iron Ore': { mode: 'extract', purity: 'normal' } } });
        expect(wrapper.vm.params.raw_sources).toEqual({ 'Iron Ore': { mode: 'extract', purity: 'normal' } });
    });

    it('defaults to empty (all import) when no raw_sources prop', () => {
        const wrapper = makeWrapper();
        expect(wrapper.vm.params.raw_sources).toEqual({});
    });

    it('saveMyFactory persists raw_sources', () => {
        const wrapper = makeWrapper({
            factory: { id: 5, name: 'My Factory' },
            raw_sources: { 'Iron Ore': { mode: 'extract' } },
        });
        wrapper.vm.saveMyFactory();
        expect(mockInertia.patch.mock.calls[0][1].raw_sources).toEqual({ 'Iron Ore': { mode: 'extract' } });
    });
});

describe('Show — T107: import_notes plan param (V64)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('params() includes import_notes for the fetch round-trip', () => {
        const wrapper = makeWrapper({ import_notes: { 'Caterium Ingot': 'From NW factory' } });
        expect(wrapper.vm.params.import_notes).toEqual({ 'Caterium Ingot': 'From NW factory' });
    });

    it('defaults to empty when no import_notes prop', () => {
        const wrapper = makeWrapper();
        expect(wrapper.vm.params.import_notes).toEqual({});
    });

    it('updateImportNote sets a note, and clears it when emptied', () => {
        const wrapper = makeWrapper();
        wrapper.vm.updateImportNote({ name: 'Copper Ingot', note: 'imported' });
        expect(wrapper.vm.newImportNotes).toEqual({ 'Copper Ingot': 'imported' });

        // empty note removes the entry (V64: empty = no display)
        wrapper.vm.updateImportNote({ name: 'Copper Ingot', note: '' });
        expect(wrapper.vm.newImportNotes).toEqual({});
    });

    it('saveMyFactory persists import_notes', () => {
        const wrapper = makeWrapper({
            factory: { id: 5, name: 'My Factory' },
            import_notes: { 'Caterium Ingot': 'From NW factory' },
        });
        wrapper.vm.saveMyFactory();
        expect(mockInertia.patch.mock.calls[0][1].import_notes).toEqual({ 'Caterium Ingot': 'From NW factory' });
    });
});

describe('Show — T110: auto_package_recycle plan param (V67)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('defaults to 0 (OFF) in params', () => {
        const wrapper = makeWrapper();
        expect(wrapper.vm.params.auto_package_recycle).toBe(0);
    });

    it('params reflects the prop when ON', () => {
        const wrapper = makeWrapper({ auto_package_recycle: true });
        expect(wrapper.vm.params.auto_package_recycle).toBe(1);
    });

    it('toggleAutoPackageRecycle flips the flag and refetches', () => {
        const wrapper = makeWrapper();
        wrapper.vm.toggleAutoPackageRecycle();
        expect(wrapper.vm.newAutoPackageRecycle).toBe(true);
        expect(mockInertia.get).toHaveBeenCalled();
    });

    it('saveMyFactory persists auto_package_recycle', () => {
        const wrapper = makeWrapper({
            factory: { id: 5, name: 'My Factory' },
            auto_package_recycle: true,
        });
        wrapper.vm.saveMyFactory();
        expect(mockInertia.patch.mock.calls[0][1].auto_package_recycle).toBe(true);
    });
});

describe('Show — T125: recycled Packager folds into the build (V87)', () => {
    beforeEach(() => vi.clearAllMocks());

    function withPackaged() {
        return makeWrapper({
            production: {
                ...minimalProduction,
                recycling: {
                    points: 7800,
                    recycled: {},
                    packaged: [
                        { fluid: 'Water', product: 'Packaged Water', qty: 60, buildings: 1, power: 10, container: 'Empty Canister', container_qty: 60, points: 7800, building: 'Packager', build_cost: { 'Steel Beam': 20, Rubber: 10, Plastic: 10 } },
                    ],
                    waste: {},
                },
            },
        });
    }

    it('appends a Packager building-detail row', () => {
        const rows = withPackaged().vm.production__building_details;
        const pkg = rows.find((r) => r.variant_name === 'Packager');
        expect(pkg).toBeTruthy();
        expect(pkg.num_buildings).toBe(1);
        expect(pkg.power_usage).toBe(10);
        expect(pkg.build_cost['Steel Beam']).toBe(20);
    });

    it('folds Packager power into the plan total power', () => {
        expect(withPackaged().vm.production__total_power).toBe(10);
    });

    it('folds Packager build cost into total_build_cost', () => {
        const summary = withPackaged().vm.production__building_summary;
        expect(summary.total_build_cost['Steel Beam']).toBe(20);
    });
});

describe('Show — T74: setDefaultRecipe assigns row.recipe directly', () => {
    beforeEach(() => vi.clearAllMocks());

    it('assigns base recipe to row when no favorite', () => {
        const wrapper = makeWrapper();
        const row = { yield: 10, product: ironPlate, recipe: null };

        wrapper.vm.setDefaultRecipe(row);

        expect(row.recipe).toBeTruthy();
        expect(row.recipe.alt_recipe).toBe(false);
    });

    it('assigns favorite recipe to row when one exists', () => {
        const favoriteRecipe = { ...altRecipe, favorite: true };
        const wrapper = makeWrapper({
            recipes: { 'Iron Plate': [baseRecipe, favoriteRecipe] },
        });
        const row = { yield: 10, product: ironPlate, recipe: null };

        wrapper.vm.setDefaultRecipe(row);

        expect(row.recipe).toEqual(favoriteRecipe);
    });

    it('assigns only recipe when single option', () => {
        const wrapper = makeWrapper({
            recipes: { 'Iron Plate': [baseRecipe] },
        });
        const row = { yield: 10, product: ironPlate, recipe: null };

        wrapper.vm.setDefaultRecipe(row);

        expect(row.recipe).toEqual(baseRecipe);
    });

    it('does not throw (setRecipe is not called as undefined)', () => {
        const wrapper = makeWrapper();
        const row = { yield: 10, product: ironPlate, recipe: null };

        expect(() => wrapper.vm.setDefaultRecipe(row)).not.toThrow();
        expect(row.recipe).not.toBeNull();
    });
});

describe('Show — T88: building settings deferred apply', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        window.localStorage.clear();
    });

    it('panel edits do not trigger a fetch', () => {
        const wrapper = makeWrapper();

        wrapper.vm.toggleBlueprintEnabled('Constructor');
        wrapper.vm.setBlueprintSize('Constructor', 8);

        expect(mockInertia.get).not.toHaveBeenCalled();
    });

    it('panel edits persist to localStorage immediately', () => {
        const wrapper = makeWrapper();

        wrapper.vm.toggleBlueprintEnabled('Constructor');
        wrapper.vm.setBlueprintSize('Constructor', 8);

        expect(JSON.parse(window.localStorage.getItem('satisfactory.bp_sizes'))).toEqual({ Constructor: 8 });
        expect(JSON.parse(window.localStorage.getItem('satisfactory.bp_enabled:new'))).toEqual({ Constructor: true });
    });

    it('diagrams keep last-applied multiples until Update is clicked', () => {
        const wrapper = makeWrapper();

        wrapper.vm.toggleBlueprintEnabled('Constructor');
        wrapper.vm.setBlueprintSize('Constructor', 8);

        expect(wrapper.vm.appliedMultiples).toEqual({});

        wrapper.vm.fetch();

        expect(wrapper.vm.appliedMultiples).toEqual({ Constructor: 8 });
        expect(mockInertia.get.mock.calls[0][1].building_multiples).toEqual({ Constructor: 8 });
    });
});

describe('Show — T91/V51: plan settings live in Building Settings panel', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        window.localStorage.clear();
    });

    function makeHeaderWrapper(propsOverride = {}) {
        return shallowMount(Show, {
            global: {
                mocks: {
                    $inertia: mockInertia,
                    $page: { props: { user: { name: 'Test User' } } },
                    Bus: mockBus,
                },
                stubs: {
                    AppLayout: { template: '<div><slot name="header" /><slot /></div>' },
                    ProductionSummary: true,
                    ProductionSteps: true,
                    BuildingSummary: true,
                    ProductionWarning: true,
                    ProductionQuickNav: true,
                },
            },
            props: { ...defaultProps, ...propsOverride },
        });
    }

    const beltSelect = (wrapper) => wrapper.findAll('select').filter((s) => s.html().includes('1200 per min (mk6)'));
    const multiplierInputs = (wrapper) =>
        [
            'Recipe cost multiplier (1.0 = default)',
            'Building cost multiplier (1.0 = default)',
            'Power cost multiplier (1.0 = default)',
        ].map((title) => wrapper.find(`input[title="${title}"]`));

    it('Settings button renders even with no buildings (uniqueBuildings empty)', () => {
        const wrapper = makeHeaderWrapper();

        expect(wrapper.vm.uniqueBuildings).toEqual([]);
        const settingsBtn = wrapper.findAll('button').filter((b) => b.text() === 'Settings');
        expect(settingsBtn.length).toBe(1);
    });

    it('belt/cost/building-cost/power controls absent from header row, present only in panel', async () => {
        const wrapper = makeHeaderWrapper();

        expect(beltSelect(wrapper).length).toBe(0);
        for (const input of multiplierInputs(wrapper)) expect(input.exists()).toBe(false);

        wrapper.vm.showBuildingSettings = true;
        await wrapper.vm.$nextTick();

        expect(beltSelect(wrapper).length).toBe(1);
        for (const input of multiplierInputs(wrapper)) expect(input.exists()).toBe(true);
    });

    it('panel reachable pre-buildings: plan settings + Update shown, blueprint rows gated off', async () => {
        const wrapper = makeHeaderWrapper();
        wrapper.vm.showBuildingSettings = true;
        await wrapper.vm.$nextTick();

        expect(wrapper.html()).toContain('Plan Settings');
        expect(wrapper.html()).not.toContain('Blueprint Mode');
        expect(beltSelect(wrapper).length).toBe(1);
    });

    it('T92/V52: each plan setting renders as card with icon + label + control', async () => {
        const wrapper = makeHeaderWrapper();
        wrapper.vm.showBuildingSettings = true;
        await wrapper.vm.$nextTick();

        const cards = wrapper.findAll('[data-test="plan-setting-card"]');
        expect(cards.length).toBe(4);

        const expected = [
            { icon: RocketLaunchIcon, label: 'Belt Speed', control: 'select' },
            { icon: QueueListIcon, label: 'Recipe Cost ×', control: 'input[type="number"]' },
            { icon: BuildingOffice2Icon, label: 'Building Cost ×', control: 'input[type="number"]' },
            { icon: PowerIcon, label: 'Power Cost ×', control: 'input[type="number"]' },
        ];

        cards.forEach((card, i) => {
            expect(card.findComponent(expected[i].icon).exists()).toBe(true);
            expect(card.find('label').text()).toBe(expected[i].label);
            expect(card.find(expected[i].control).exists()).toBe(true);
        });
    });

    it('fetch payload unchanged: panel-bound values sent as same params', () => {
        const wrapper = makeHeaderWrapper();

        wrapper.vm.form.belt_speed = 1200;
        wrapper.vm.costMultiplier = 2;
        wrapper.vm.buildingCostMultiplier = 3;
        wrapper.vm.powerMultiplier = 0.5;
        wrapper.vm.fetch();

        const params = mockInertia.get.mock.calls[0][1];
        expect(params.belt_speed).toBe(1200);
        expect(params.cost_multiplier).toBe(2);
        expect(params.building_cost_multiplier).toBe(3);
        expect(params.power_multiplier).toBe(0.5);
    });
});

describe('Show — T95/V55: per-output Fill to 100%', () => {
    beforeEach(() => vi.clearAllMocks());

    const wire = { id: 2, name: 'Wire', recipes: [] };
    const wireRecipe = { id: 20, description: null, alt_recipe: false, product: wire, product_id: 2, favorite: false };

    function makeMultiWrapper() {
        return makeWrapper({
            products: [ironPlate, wire],
            recipes: { 'Iron Plate': [baseRecipe], Wire: [wireRecipe] },
            multi: {
                products: [ironPlate, wire],
                yields: [25, 20],
                recipes: [baseRecipe, wireRecipe],
            },
        });
    }

    it('fillOutput sets only the targeted output yield; other outputs untouched', () => {
        const wrapper = makeMultiWrapper();

        wrapper.vm.fillOutput('Iron Plate', 40);

        const yieldOf = (name) => wrapper.vm.form.outputs.find((o) => o.product.name === name).yield;
        expect(yieldOf('Iron Plate')).toBe(40);
        expect(yieldOf('Wire')).toBe(20);
    });

    it('fillOutput triggers a fetch', () => {
        const wrapper = makeMultiWrapper();

        wrapper.vm.fillOutput('Iron Plate', 40);

        expect(mockInertia.get).toHaveBeenCalledTimes(1);
    });

    it('fillOutput on an unknown product is a no-op (no throw, no fetch)', () => {
        const wrapper = makeMultiWrapper();

        expect(() => wrapper.vm.fillOutput('Nonexistent', 99)).not.toThrow();
        expect(mockInertia.get).not.toHaveBeenCalled();
    });
});

describe('Show — T97/V57: allChosenRecipes captures in-force sub-recipes', () => {
    beforeEach(() => vi.clearAllMocks());

    const ov = (product, recipe, building) => ({
        clock: 'c100',
        selected_variant_name: 'mk1',
        overviews: { c100: { building, details: { mk1: {} } } },
        overview: { product, recipe, building },
    });

    const overviewsProp = {
        'Iron Plate|Iron Plate': ov('Iron Plate', 'Iron Plate', 'Constructor'),
        'Circuit Board|Caterium Circuit Board': ov('Circuit Board', 'Caterium Circuit Board', 'Assembler'),
        'Screw|Cast Screw': ov('Screw', 'Cast Screw', 'Constructor'),
    };

    it('includes effective tree sub-recipes, not just newChoices + outputs', () => {
        const wrapper = makeWrapper({
            production: { ...minimalProduction, overviews: overviewsProp },
        });

        const chosen = wrapper.vm.allChosenRecipes;
        // sub-recipes in force in the tree are captured even though the user
        // never touched them via newChoices
        expect(chosen['Circuit Board']).toBe('Caterium Circuit Board');
        expect(chosen.Screw).toBe('Cast Screw');
    });

    it('newChoices override tree recipes for the same product', () => {
        const wrapper = makeWrapper({
            production: { ...minimalProduction, overviews: overviewsProp },
        });

        wrapper.vm.newChoices = { 'Circuit Board': 'Circuit Board' };

        expect(wrapper.vm.allChosenRecipes['Circuit Board']).toBe('Circuit Board');
    });
});

describe('Show — extractors in building summary (V76)', () => {
    beforeEach(() => vi.clearAllMocks());

    const extractor = {
        product: 'Iron Ore',
        building: 'Miner Mk.2',
        variant_name: 'Miner Mk.2',
        num_buildings: 3,
        exact_buildings: 3,
        power_usage: 45,
        build_cost: [],
        clock_speed: 100,
        footprint: { power_shards: 0, somersloops: 0 },
    };

    it('merges extract-mode extractors into building_details', () => {
        const wrapper = makeWrapper({
            production: { ...minimalProduction, overviews: {}, extractors: [extractor] },
        });

        const details = wrapper.vm.production__building_details;
        expect(details).toHaveLength(1);
        expect(details[0].building).toBe('Miner Mk.2');
        expect(details[0].num_buildings).toBe(3);
    });

    it('shows the extractor in the building summary and total power', () => {
        const wrapper = makeWrapper({
            production: { ...minimalProduction, overviews: {}, extractors: [extractor] },
        });

        const summary = wrapper.vm.production__building_summary;
        expect(summary.variants['Miner Mk.2'].num_buildings).toBe(3);
        expect(summary.variants['Miner Mk.2'].power_usage).toBe(45);
        expect(wrapper.vm.production__total_power).toBe(45);
    });

    it('adds nothing when there are no extractors (byte-identical)', () => {
        const wrapper = makeWrapper({
            production: { ...minimalProduction, overviews: {} },
        });

        expect(wrapper.vm.production__building_details).toHaveLength(0);
        expect(wrapper.vm.production__total_power).toBe(0);
    });
});
