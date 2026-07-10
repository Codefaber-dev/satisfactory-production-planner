import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ProductionQuickNav from '../Components/ProductionQuickNav.vue';

// registration is Bus-driven: capture the handler and feed links directly
const mountNav = (productionChecks, links) => {
    let register;
    const wrapper = mount(ProductionQuickNav, {
        global: {
            mocks: {
                Bus: {
                    on: (event, handler) => {
                        if (event === 'RegisterQuickNav') {
                            register = handler;
                        }
                    },
                    emit: () => {},
                },
            },
        },
        props: { productionChecks },
    });

    for (const link of links) {
        register(link);
    }

    return wrapper;
};

const link = (name, identifier, checkKey) => ({ name, identifier, checkKey });

describe('ProductionQuickNav — ✅ markers exact-key (V146)', () => {
    it('marks an entry iff the store holds a truthy value at its exact check key', async () => {
        const wrapper = mountNav({ 'Iron Ingot-base': true }, [
            link('Iron Ingot', '1.A', 'Iron Ingot-base'),
            link('Copper Ingot', '1.B', 'Copper Ingot-base'),
        ]);
        await wrapper.vm.$nextTick();

        const items = wrapper.findAll('li button');
        expect(items[0].text()).toContain('✅');
        expect(items[1].text()).not.toContain('✅');
    });

    it('a present-but-false key shows no marker (unchecked step stays unmarked)', async () => {
        const wrapper = mountNav({ 'Iron Ingot-base': false }, [link('Iron Ingot', '1.A', 'Iron Ingot-base')]);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('li button').text()).not.toContain('✅');
    });

    it('never matches by name substring (kills the includes() false positive)', async () => {
        // old code: Object.keys(checks).some((oo) => oo.includes(o.name)) —
        // 'Iron Ingot-base' includes 'Iron', so 'Iron' was falsely marked
        const wrapper = mountNav({ 'Iron Ingot-base': true }, [link('Iron', '0.A', 'Iron-base')]);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('li button').text()).not.toContain('✅');
    });

    it('marker tracks store changes reactively while the panel is open', async () => {
        const wrapper = mountNav({}, [link('Iron Ingot', '1.A', 'Iron Ingot-base')]);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('li button').text()).not.toContain('✅');

        await wrapper.setProps({ productionChecks: { 'Iron Ingot-base': true } });

        expect(wrapper.find('li button').text()).toContain('✅');
    });
});
