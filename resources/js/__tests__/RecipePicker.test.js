import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import RecipePicker from '../Components/RecipePicker.vue';

const RecipeDetailStub = {
    name: 'RecipeDetail',
    template: '<div @click="$emit(\'select\', { recipe })" data-testid="recipe-detail">{{ recipe.description }}</div>',
    props: ['recipe', 'slim'],
    emits: ['select'],
};

const makeRecipe = (overrides = {}) => ({
    id: 1,
    description: null,
    base_per_min: 30,
    base_yield: 1,
    favorite: false,
    alt_recipe: false,
    energy_efficient: false,
    resource_efficient: false,
    ingredients: [],
    byproducts: [],
    product: { name: 'Iron Plate' },
    ...overrides,
});

const selectedRecipe = makeRecipe({ id: 99, description: 'My Recipe', product: { name: 'Iron Plate' } });

function factory(overrides = {}) {
    return mount(RecipePicker, {
        global: {
            components: { RecipeDetail: RecipeDetailStub },
        },
        props: {
            recipes: [makeRecipe()],
            selected: selectedRecipe,
            choices: {},
            ...overrides,
        },
    });
}

describe('RecipePicker', () => {
    it('renders without error', () => {
        const wrapper = factory();
        expect(wrapper.exists()).toBe(true);
    });

    it('menu hidden by default', () => {
        const wrapper = factory();
        expect(wrapper.vm.showMenu).toBe(false);
    });

    it('defaultRecipe returns favorite when present', () => {
        const fav = makeRecipe({ id: 2, favorite: true });
        const other = makeRecipe({ id: 3 });
        const wrapper = factory({ recipes: [other, fav] });
        expect(wrapper.vm.defaultRecipe.id).toBe(2);
    });

    it('defaultRecipe returns first recipe with no description when no favorite', () => {
        const r1 = makeRecipe({ id: 1, description: null });
        const r2 = makeRecipe({ id: 2, description: 'Alt' });
        const wrapper = factory({ recipes: [r1, r2] });
        expect(wrapper.vm.defaultRecipe.id).toBe(1);
    });

    it('selectedChosen is true when choices include selected description', () => {
        const wrapper = factory({ choices: { someKey: 'My Recipe' } });
        expect(wrapper.vm.selectedChosen).toBe(true);
    });

    it('selectedChosen is false when choices do not match', () => {
        const wrapper = factory({ choices: { someKey: 'Other Recipe' } });
        expect(wrapper.vm.selectedChosen).toBe(false);
    });

    it('select emits select event with recipe', () => {
        const wrapper = factory();
        const recipe = makeRecipe({ id: 5 });
        wrapper.vm.select({ recipe });
        expect(wrapper.emitted('select')).toBeTruthy();
        expect(wrapper.emitted('select')[0][0]).toEqual({ recipe });
    });

    it('hide sets showMenu false', () => {
        const wrapper = factory();
        wrapper.vm.showMenu = true;
        wrapper.vm.hide();
        expect(wrapper.vm.showMenu).toBe(false);
    });

    it('clicking root toggles showMenu', async () => {
        const wrapper = factory();
        expect(wrapper.vm.showMenu).toBe(false);
        await wrapper.trigger('click');
        expect(wrapper.vm.showMenu).toBe(true);
        await wrapper.trigger('click');
        expect(wrapper.vm.showMenu).toBe(false);
    });
});
