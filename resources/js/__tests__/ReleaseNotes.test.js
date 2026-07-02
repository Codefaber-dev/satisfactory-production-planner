import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import ReleaseNotes from '../Components/ReleaseNotes.vue';

const reload = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    router: {
        reload: (...args) => reload(...args),
    },
}));

function makeWrapper(props = {}) {
    return shallowMount(ReleaseNotes, {
        props: {
            version: '1.2.2',
            releaseNotes: undefined,
            ...props,
        },
    });
}

describe('ReleaseNotes lazy prop fetch (V72)', () => {
    beforeEach(() => {
        reload.mockClear();
        window.localStorage.clear();
    });

    it('fetches the optional prop on mount when not acknowledged (auto-show)', () => {
        makeWrapper();

        expect(reload).toHaveBeenCalledWith({ only: ['releaseNotes'] });
    });

    it('does not fetch on mount when the release was acknowledged', () => {
        window.localStorage.setItem('ack-releaseNotes-1.2.2', '1');

        makeWrapper();

        expect(reload).not.toHaveBeenCalled();
    });

    it('fetches when opened via toggle and content is absent', async () => {
        window.localStorage.setItem('ack-releaseNotes-1.2.2', '1');
        const wrapper = makeWrapper();

        await wrapper.vm.toggle();

        expect(reload).toHaveBeenCalledWith({ only: ['releaseNotes'] });
        expect(wrapper.vm.show).toBe(true);
    });

    it('does not refetch when content already present', async () => {
        window.localStorage.setItem('ack-releaseNotes-1.2.2', '1');
        const wrapper = makeWrapper({ releaseNotes: '<p>hi</p>' });

        await wrapper.vm.toggle();

        expect(reload).not.toHaveBeenCalled();
    });

    it('renders the fetched html', () => {
        const wrapper = makeWrapper({ releaseNotes: '<p>notes-here</p>' });

        expect(wrapper.html()).toContain('notes-here');
    });
});
