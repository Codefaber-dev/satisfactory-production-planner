import { describe, it, expect, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import Parser from '../Components/Parser.vue';

// attachTo is required: jsdom only populates textContent for elements attached to document body.
// nextTick() is required: mounted() sets reactive data; DOM update is async.

let wrapper;

afterEach(() => {
    wrapper?.unmount();
});

async function factory(slotContent) {
    const w = mount(Parser, {
        attachTo: document.body,
        slots: slotContent ? { default: slotContent } : undefined,
    });
    await nextTick();
    return w;
}

describe('Parser', () => {
    it('mounts without error', async () => {
        expect(async () => {
            wrapper = await factory();
        }).not.toThrow();
    });

    it('renders empty output when no slot content', async () => {
        wrapper = await factory();
        expect(wrapper.vm.rendered.trim()).toBe('');
    });

    it('parses markdown heading from slot', async () => {
        wrapper = await factory('# Hello World');
        expect(wrapper.find('h1').exists()).toBe(true);
        expect(wrapper.find('h1').text()).toBe('Hello World');
    });

    it('parses markdown paragraph from slot', async () => {
        wrapper = await factory('Just a paragraph.');
        expect(wrapper.find('p').exists()).toBe(true);
        expect(wrapper.find('p').text()).toBe('Just a paragraph.');
    });

    it('parses bold markdown', async () => {
        wrapper = await factory('**bold text**');
        expect(wrapper.find('strong').exists()).toBe(true);
        expect(wrapper.find('strong').text()).toBe('bold text');
    });

    it('slot source is in a hidden container, not displayed as plain text', async () => {
        wrapper = await factory('# Hidden Source');
        const hiddenDiv = wrapper.find('[style="display: none;"]');
        expect(hiddenDiv.exists()).toBe(true);
        expect(hiddenDiv.text()).toContain('# Hidden Source');
    });

    it('rendered output is separate from hidden slot source', async () => {
        wrapper = await factory('# Title');
        // source stays as raw markdown in hidden div
        const hiddenDiv = wrapper.find('[style="display: none;"]');
        expect(hiddenDiv.text()).toContain('# Title');
        // rendered is html, not raw markdown
        expect(wrapper.vm.rendered).not.toContain('# Title');
        expect(wrapper.vm.rendered).toContain('Title');
    });

    it('setting rendered data updates the v-html output', async () => {
        wrapper = await factory();
        await wrapper.setData({ rendered: '<em>injected</em>' });
        expect(wrapper.find('em').exists()).toBe(true);
        expect(wrapper.find('em').text()).toBe('injected');
    });
});
