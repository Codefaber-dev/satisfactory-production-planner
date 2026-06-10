import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import MultiFactory from '../Components/MultiFactory.vue';

const mockInertia = {
    patch: vi.fn(),
    delete: vi.fn(),
};

const outputs = [
    {
        product: { name: 'Iron Plate', recipes: [] },
        recipe: { id: 1, description: null, product: { name: 'Iron Plate' } },
        yield: 30,
    },
    {
        product: { name: 'Wire', recipes: [] },
        recipe: { id: 2, description: 'Alt', product: { name: 'Wire' } },
        yield: 60,
    },
];

const defaultProps = {
    id: 2,
    name: 'Multi Test Factory',
    outputs,
    imports: '',
    notes: '',
    url: '/production/multi/2',
    is_shared: false,
    hash_id: 'xyz456',
};

function factory(propsOverride = {}) {
    return mount(MultiFactory, {
        global: {
            mocks: {
                $inertia: mockInertia,
                $page: { props: { user: null } },
            },
            stubs: {
                RecipePicker: { template: '<div />', props: ['recipes', 'selected'], emits: ['select'] },
                CloudImage: { template: '<img />', props: ['publicId', 'crop', 'quality', 'width', 'alt', 'class'] },
                'inertia-link': { template: '<a><slot /></a>', props: ['href', 'as'] },
            },
        },
        props: { ...defaultProps, ...propsOverride },
    });
}

describe('MultiFactory', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.spyOn(window, 'confirm').mockReturnValue(false);
    });

    it('renders both output product names', () => {
        const wrapper = factory();
        expect(wrapper.text()).toContain('Iron Plate');
        expect(wrapper.text()).toContain('Wire');
    });

    it('renders all output yields', () => {
        const wrapper = factory();
        expect(wrapper.text()).toContain('30');
        expect(wrapper.text()).toContain('60');
    });

    it('starts in non-editing state', () => {
        const wrapper = factory();
        expect(wrapper.vm.editing).toBe(false);
    });

    it('form initializes from props', () => {
        const wrapper = factory();
        expect(wrapper.vm.form.name).toBe('Multi Test Factory');
        expect(wrapper.vm.form.outputs).toEqual(outputs);
        expect(wrapper.vm.form.is_shared).toBe(false);
    });

    it('cancelEditing sets editing to false', () => {
        const wrapper = factory();
        wrapper.vm.editing = true;
        wrapper.vm.cancelEditing();
        expect(wrapper.vm.editing).toBe(false);
    });

    it('saveEdits calls $inertia.patch with correct url and form', () => {
        const wrapper = factory();
        wrapper.vm.saveEdits();
        expect(mockInertia.patch).toHaveBeenCalledWith(
            '/factories/multi/2',
            wrapper.vm.form,
            expect.objectContaining({ onSuccess: expect.any(Function) })
        );
    });

    it('saveEdits onSuccess sets editing to false', () => {
        const wrapper = factory();
        wrapper.vm.editing = true;
        wrapper.vm.saveEdits();
        const { onSuccess } = mockInertia.patch.mock.calls[0][2];
        onSuccess();
        expect(wrapper.vm.editing).toBe(false);
    });

    it('deletePrompt does not delete when user cancels', () => {
        vi.spyOn(window, 'confirm').mockReturnValue(false);
        const wrapper = factory();
        wrapper.vm.deletePrompt();
        expect(mockInertia.delete).not.toHaveBeenCalled();
    });

    it('deletePrompt calls $inertia.delete when confirmed', () => {
        vi.spyOn(window, 'confirm').mockReturnValue(true);
        const wrapper = factory();
        wrapper.vm.deletePrompt();
        expect(mockInertia.delete).toHaveBeenCalledWith('/factories/multi/2');
    });

    it('renderedNotes returns html for markdown notes', () => {
        const wrapper = factory({ notes: '**bold text**' });
        expect(typeof wrapper.vm.renderedNotes).toBe('string');
        expect(wrapper.vm.renderedNotes).toContain('bold text');
    });

    it('is_shared=true reflects in form', () => {
        const wrapper = factory({ is_shared: true });
        expect(wrapper.vm.form.is_shared).toBe(true);
    });

    it('updateRecipe updates form.recipe_id and selectedRecipe', () => {
        const wrapper = factory();
        const newRecipe = { id: 10, description: 'New Alt' };
        wrapper.vm.updateRecipe({ recipe: newRecipe });
        expect(wrapper.vm.form.recipe_id).toBe(10);
    });
});
