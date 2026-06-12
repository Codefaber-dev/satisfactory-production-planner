import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
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
