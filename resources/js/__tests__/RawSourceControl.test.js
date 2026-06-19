import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import RawSourceControl from '../Pages/Production/RawSourceControl.vue';

// recipes[name]-shaped option lists
const minerRecipes = []; // an ore with no convert/unpackage recipes
const coalRecipes = [
    { id: 1, description: 'Coal (Iron)', ingredients: [{ name: 'Iron Ore' }, { name: 'Reanimated SAM' }] },
    { id: 2, description: 'Coal (Limestone)', ingredients: [{ name: 'Limestone' }, { name: 'Reanimated SAM' }] },
];
const waterRecipes = [
    { id: 3, description: 'Unpackage Water', ingredients: [{ name: 'Packaged Water' }] },
];

function mountControl(props) {
    return mount(RawSourceControl, {
        props: {
            name: 'Iron Ore',
            config: {},
            recipeOptions: minerRecipes,
            ...props,
        },
    });
}

describe('RawSourceControl', () => {
    it('offers import + extract for a plain ore (no convert/unpackage)', () => {
        const wrapper = mountControl({ name: 'Iron Ore', recipeOptions: minerRecipes });
        const labels = wrapper.findAll('button').map((b) => b.text());

        expect(labels).toEqual(['import', 'extract']);
    });

    it('offers convert when the raw has converter recipes', () => {
        const wrapper = mountControl({ name: 'Coal', recipeOptions: coalRecipes });
        const labels = wrapper.findAll('button').map((b) => b.text());

        expect(labels).toContain('convert');
        expect(labels).not.toContain('unpackage');
    });

    it('offers unpackage only for raws with an Unpackage recipe', () => {
        const wrapper = mountControl({ name: 'Water', recipeOptions: waterRecipes });
        const labels = wrapper.findAll('button').map((b) => b.text());

        expect(labels).toContain('unpackage');
    });

    it('extract mode on a miner shows purity + tier + shards', () => {
        const wrapper = mountControl({ name: 'Iron Ore', config: { mode: 'extract' } });

        expect(wrapper.find('[aria-label="purity"]').exists()).toBe(true);
        expect(wrapper.find('[aria-label="miner tier"]').exists()).toBe(true);
        expect(wrapper.find('[aria-label="power shards"]').exists()).toBe(true);
    });

    it('extract mode on water hides purity + tier (shards only)', () => {
        const wrapper = mountControl({ name: 'Water', config: { mode: 'extract' }, recipeOptions: waterRecipes });

        expect(wrapper.find('[aria-label="purity"]').exists()).toBe(false);
        expect(wrapper.find('[aria-label="miner tier"]').exists()).toBe(false);
        expect(wrapper.find('[aria-label="power shards"]').exists()).toBe(true);
    });

    it('oil shows purity but no tier', () => {
        const wrapper = mountControl({ name: 'Crude Oil', config: { mode: 'extract' } });

        expect(wrapper.find('[aria-label="purity"]').exists()).toBe(true);
        expect(wrapper.find('[aria-label="miner tier"]').exists()).toBe(false);
    });

    it('V79: emits the mode only on convert — no recipe key (recipe lives in choices)', async () => {
        const wrapper = mountControl({ name: 'Coal', recipeOptions: coalRecipes });

        const convertBtn = wrapper.findAll('button').find((b) => b.text() === 'convert');
        await convertBtn.trigger('click');

        const payload = wrapper.emitted('update').at(-1)[0];
        expect(payload).toEqual({
            name: 'Coal',
            config: { mode: 'convert' },
        });
        expect(payload.config).not.toHaveProperty('recipe');
    });

    it('V79: no recipe <select> renders in convert or unpackage mode', () => {
        const convert = mountControl({ name: 'Coal', config: { mode: 'convert' }, recipeOptions: coalRecipes });
        expect(convert.findAll('select')).toHaveLength(0);

        const unpackage = mountControl({ name: 'Water', config: { mode: 'unpackage' }, recipeOptions: waterRecipes });
        expect(unpackage.findAll('select')).toHaveLength(0);
    });

    it('V75: extract controls are styled buttons, not select menus', () => {
        const wrapper = mountControl({ name: 'Iron Ore', config: { mode: 'extract' } });

        // no <select> anywhere in extract mode for a miner
        expect(wrapper.findAll('select')).toHaveLength(0);
        // purity buttons present
        const purityBtns = wrapper.find('[aria-label="purity"]').findAll('button').map((b) => b.text());
        expect(purityBtns).toEqual(['impure', 'normal', 'pure']);
        // shard buttons 0–3
        const shardBtns = wrapper.find('[aria-label="power shards"]').findAll('button').map((b) => b.text());
        expect(shardBtns).toEqual(['0', '1', '2', '3']);
    });

    it('emits extract params via a button click, preserving mode', async () => {
        const wrapper = mountControl({ name: 'Iron Ore', config: { mode: 'extract' } });

        const pureBtn = wrapper.find('[aria-label="purity"]').findAll('button').find((b) => b.text() === 'pure');
        await pureBtn.trigger('click');

        const payload = wrapper.emitted('update').at(-1)[0];
        expect(payload.config.mode).toBe('extract');
        expect(payload.config.purity).toBe('pure');
    });

    it('V74: a biomass/organic raw offers no extract mode', () => {
        const wrapper = mountControl({ name: 'Wood', recipeOptions: [] });
        const labels = wrapper.findAll('button').map((b) => b.text());

        expect(labels).toEqual(['import']);
        expect(labels).not.toContain('extract');
    });

    it('V74: Alien Protein is non-extractable too', () => {
        const wrapper = mountControl({ name: 'Alien Protein', recipeOptions: [] });
        const labels = wrapper.findAll('button').map((b) => b.text());

        expect(labels).not.toContain('extract');
    });

    it('V79: unpackage mode still gated by an Unpackage recipe being available', () => {
        // mode shown only when the raw has an Unpackage recipe (recipeOptions-derived gate)
        const withRecipe = mountControl({ name: 'Water', recipeOptions: waterRecipes });
        expect(withRecipe.findAll('button').map((b) => b.text())).toContain('unpackage');

        const withoutRecipe = mountControl({ name: 'Water', recipeOptions: [] });
        expect(withoutRecipe.findAll('button').map((b) => b.text())).not.toContain('unpackage');
    });

    describe('extractor info line (V82)', () => {
        it('shows count/tier/clock/purity/power for a miner', () => {
            const wrapper = mountControl({
                name: 'Iron Ore',
                config: { mode: 'extract', miner: 'mk1', purity: 'pure' },
                extractor: { count: 6, clock: 100, power: 24 },
            });

            expect(wrapper.find('[aria-label="extractor summary"]').text()).toBe(
                '6x Miner (mk1) @100% [Pure] [24 MW]'
            );
        });

        it('omits the purity tag for water (no purity semantics)', () => {
            const wrapper = mountControl({
                name: 'Water',
                config: { mode: 'extract' },
                recipeOptions: waterRecipes,
                extractor: { count: 5, clock: 100, power: 20 },
            });

            const text = wrapper.find('[aria-label="extractor summary"]').text();
            expect(text).toBe('5x Water Extractor @100% [20 MW]');
            expect(text).not.toContain('[Normal]');
        });

        it('renders no info line when there is no extractor row', () => {
            const wrapper = mountControl({ name: 'Iron Ore', config: { mode: 'extract' } });

            expect(wrapper.find('[aria-label="extractor summary"]').exists()).toBe(false);
        });

        it('defaults purity to Normal and tier to mk2 when unset', () => {
            const wrapper = mountControl({
                name: 'Iron Ore',
                config: { mode: 'extract' },
                extractor: { count: 2, clock: 250, power: 90 },
            });

            expect(wrapper.find('[aria-label="extractor summary"]').text()).toBe(
                '2x Miner (mk2) @250% [Normal] [90 MW]'
            );
        });
    });
});
