import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Factory from '../Components/Factory.vue';

const mockInertia = {
    patch: vi.fn(),
    delete: vi.fn(),
};

const defaultProps = {
    id: 1,
    name: 'Test Factory',
    product: { name: 'Iron Plate', recipes: [] },
    recipe: { id: 1, description: 'default', product: { name: 'Iron Plate' } },
    yield: 30,
    imports: '',
    notes: '',
    choices: {},
    url: '/production/Iron Plate/30',
    is_shared: false,
    hash_id: 'abc123',
};

function factory(propsOverride = {}) {
    return mount(Factory, {
        global: {
            mocks: {
                $inertia: mockInertia,
                $page: { props: { user: null } },
            },
            stubs: {
                RecipePicker: { template: '<div />', props: ['recipes', 'selected'], emits: ['select'] },
                CloudImage: { template: '<img />', props: ['publicId', 'crop', 'quality', 'width', 'alt'] },
                'inertia-link': { template: '<a><slot /></a>', props: ['href', 'as'] },
            },
        },
        props: { ...defaultProps, ...propsOverride },
    });
}

describe('Factory', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.spyOn(window, 'confirm').mockReturnValue(false);
    });

    it('renders product name', () => {
        const wrapper = factory();
        expect(wrapper.text()).toContain('Iron Plate');
    });

    it('renders yield value', () => {
        const wrapper = factory();
        expect(wrapper.text()).toContain('30');
    });

    it('renders recipe description', () => {
        const wrapper = factory();
        expect(wrapper.text()).toContain('default');
    });

    it('starts in non-editing state', () => {
        const wrapper = factory();
        expect(wrapper.vm.editing).toBe(false);
    });

    it('form initializes from props', () => {
        const wrapper = factory();
        expect(wrapper.vm.form.name).toBe('Test Factory');
        expect(wrapper.vm.form.yield).toBe(30);
        expect(wrapper.vm.form.recipe_id).toBe(1);
        expect(wrapper.vm.form.is_shared).toBe(false);
    });

    it('cancelEditing sets editing to false', () => {
        const wrapper = factory();
        wrapper.vm.editing = true;
        wrapper.vm.cancelEditing();
        expect(wrapper.vm.editing).toBe(false);
    });

    it('updateRecipe updates form.recipe_id and selectedRecipe', () => {
        const wrapper = factory();
        const newRecipe = { id: 5, description: 'Alt Recipe' };
        wrapper.vm.updateRecipe({ recipe: newRecipe });
        expect(wrapper.vm.form.recipe_id).toBe(5);
        expect(wrapper.vm.selectedRecipe).toEqual(newRecipe);
    });

    it('saveEdits calls $inertia.patch with correct url and form', () => {
        const wrapper = factory();
        wrapper.vm.saveEdits();
        expect(mockInertia.patch).toHaveBeenCalledWith(
            '/factories/1',
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

    it('deletePrompt does not delete when user cancels confirm', () => {
        vi.spyOn(window, 'confirm').mockReturnValue(false);
        const wrapper = factory();
        wrapper.vm.deletePrompt();
        expect(mockInertia.delete).not.toHaveBeenCalled();
    });

    it('deletePrompt calls $inertia.delete when confirmed', () => {
        vi.spyOn(window, 'confirm').mockReturnValue(true);
        const wrapper = factory();
        wrapper.vm.deletePrompt();
        expect(mockInertia.delete).toHaveBeenCalledWith('/factories/1');
    });

    it('renderedNotes computed returns html string', () => {
        const wrapper = factory({ notes: '**bold**' });
        expect(typeof wrapper.vm.renderedNotes).toBe('string');
        expect(wrapper.vm.renderedNotes).toContain('bold');
    });

    it('is_shared=true reflects in form', () => {
        const wrapper = factory({ is_shared: true });
        expect(wrapper.vm.form.is_shared).toBe(true);
    });
});
