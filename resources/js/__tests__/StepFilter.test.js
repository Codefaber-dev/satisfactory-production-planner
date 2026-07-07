import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import StepFilter from '../Pages/Production/StepFilter.vue';

const OPTIONS = ['Empty Canister', 'Iron Ingot', 'Iron Ore', 'Packaged Water'];

function mountFilter(props = {}) {
    return mount(StepFilter, {
        props: { options: OPTIONS, ...props },
    });
}

describe('StepFilter — type-ahead control (V110)', () => {
    it('shows every option when the query is empty', async () => {
        const wrapper = mountFilter();
        await wrapper.find('[data-test="step-filter-input"]').trigger('focus');

        const options = wrapper.findAll('[data-test="step-filter-option"]');
        expect(options.map((o) => o.text())).toEqual(OPTIONS);
    });

    it('narrows options case-insensitively as the user types', async () => {
        const wrapper = mountFilter();
        const input = wrapper.find('[data-test="step-filter-input"]');
        await input.trigger('focus');
        await input.setValue('iron');

        const options = wrapper.findAll('[data-test="step-filter-option"]');
        expect(options.map((o) => o.text())).toEqual(['Iron Ingot', 'Iron Ore']);
    });

    it('emits the selected item on option click', async () => {
        const wrapper = mountFilter();
        const input = wrapper.find('[data-test="step-filter-input"]');
        await input.trigger('focus');
        await input.setValue('packaged');

        await wrapper.find('[data-test="step-filter-option"]').trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([['Packaged Water']]);
    });

    it('selects the single remaining option on enter', async () => {
        const wrapper = mountFilter();
        const input = wrapper.find('[data-test="step-filter-input"]');
        await input.setValue('canister');
        await input.trigger('keyup.enter');

        expect(wrapper.emitted('update:modelValue')).toEqual([['Empty Canister']]);
    });

    it('clears the selection back to null', async () => {
        const wrapper = mountFilter({ modelValue: 'Iron Ore' });

        await wrapper.find('[data-test="step-filter-clear"]').trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([[null]]);
        expect(wrapper.find('[data-test="step-filter-input"]').element.value).toBe('');
    });

    it('renders no clear button without a selection', () => {
        const wrapper = mountFilter();
        expect(wrapper.find('[data-test="step-filter-clear"]').exists()).toBe(false);
    });
});
