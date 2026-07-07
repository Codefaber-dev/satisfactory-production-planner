import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import '../bootstrap';
import Show from '../Pages/Production/Show.vue';

const mockInertia = { get: vi.fn(), patch: vi.fn(), post: vi.fn() };
const mockBus = { on: vi.fn(), emit: vi.fn() };

const ironPlate = { id: 1, name: 'Iron Plate', recipes: [] };
const baseRecipe = { id: 10, description: null, alt_recipe: false, product: ironPlate, product_id: 1, favorite: false };

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
    recipes: { 'Iron Plate': [baseRecipe] },
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

const summary = (wrapper) => wrapper.find('production-summary-stub');
const toggle = (wrapper) => wrapper.find('[rel=toggle-summary]');
const storedCollapsed = () => JSON.parse(window.localStorage.getItem('satisfactory.summaryCollapsed'));

describe('Show — T169/V123: collapsible summary pane', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        window.localStorage.clear();
    });

    it('defaults to expanded with empty localStorage', () => {
        const wrapper = makeWrapper();

        expect(summary(wrapper).classes()).toContain('xl:block');
        expect(summary(wrapper).classes()).not.toContain('xl:hidden');
        expect(toggle(wrapper).exists()).toBe(true);
    });

    it('collapse toggle hides the pane at xl and persists to localStorage', async () => {
        const wrapper = makeWrapper();

        await toggle(wrapper).trigger('click');

        expect(summary(wrapper).classes()).toContain('xl:hidden');
        expect(summary(wrapper).classes()).not.toContain('xl:block');
        expect(storedCollapsed()).toBe(true);
    });

    it('remount restores the persisted collapsed state', () => {
        window.localStorage.setItem('satisfactory.summaryCollapsed', JSON.stringify(true));

        const wrapper = makeWrapper();

        expect(summary(wrapper).classes()).toContain('xl:hidden');
        expect(summary(wrapper).classes()).not.toContain('xl:block');
    });

    it('expanding again restores the pane and persists false', async () => {
        window.localStorage.setItem('satisfactory.summaryCollapsed', JSON.stringify(true));
        const wrapper = makeWrapper();

        await toggle(wrapper).trigger('click');

        expect(summary(wrapper).classes()).toContain('xl:block');
        expect(summary(wrapper).classes()).not.toContain('xl:hidden');
        expect(storedCollapsed()).toBe(false);
    });

    it('mobile tab visibility is independent of the collapse state', async () => {
        const wrapper = makeWrapper();

        // Default tab = productionSteps: summary hidden on mobile regardless of collapse.
        expect(summary(wrapper).classes()).toContain('hidden');

        wrapper.vm.selectedTab = 'productionSummary';
        await wrapper.vm.$nextTick();
        await toggle(wrapper).trigger('click');

        // Collapsed at xl, but the mobile tab still shows it (no `hidden`).
        expect(summary(wrapper).classes()).not.toContain('hidden');
        expect(summary(wrapper).classes()).toContain('xl:hidden');
    });
});
