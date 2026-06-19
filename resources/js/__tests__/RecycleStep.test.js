import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import '../bootstrap';
import RecycleStep from '../Pages/Production/RecycleStep.vue';

function mountStep(step, extra = {}) {
    return mount(RecycleStep, {
        global: { stubs: { CloudImage: true, BuildDiagram: { name: 'build-diagram', template: '<div class="stub-diagram" />' } } },
        props: { step, identifier: 'R.A', diagrams: true, ...extra },
    });
}

const packagingStep = {
    type: 'package',
    name: 'Packaged Water',
    building: 'Packager',
    num_buildings: 1,
    power: 10,
    footprint: { foundations: 4 },
    inputs: [{ item: 'Water', qty: 60 }],
    output: { item: 'Packaged Water', qty: 60, to_sink: true },
};

const sinkStep = {
    type: 'sink',
    name: 'AWESOME Sink',
    building: 'AWESOME Sink',
    num_buildings: 1,
    power: 30,
    footprint: { foundations: 24 },
    inputs: [{ item: 'Packaged Water', qty: 60 }, { item: 'Concrete', qty: 30 }],
    output: { points: 8160 },
};

describe('RecycleStep — V88 full build step', () => {
    it('renders a packaging step: building, input, output linking to the sink', () => {
        const wrapper = mountStep(packagingStep, { sinkIdentifier: 'R.B', stepMap: { Water: '1.A' } });

        expect(wrapper.text()).toContain('Packaged Water');
        expect(wrapper.text()).toContain('Packager');
        // input links back to its producing step
        const inputs = wrapper.findAll('[data-test="recycle-input"]');
        expect(inputs).toHaveLength(1);
        expect(inputs[0].text()).toContain('Water');
        expect(inputs[0].find('a').attributes('href')).toBe('#1.A');
        // output links forward to the sink
        expect(wrapper.find('a[href="#R.B"]').exists()).toBe(true);
        // diagram shown
        expect(wrapper.find('.stub-diagram').exists()).toBe(true);
    });

    it('renders the sink step: inputs linked, output is points, diagram shown', () => {
        const wrapper = mountStep(sinkStep, { stepMap: { 'Packaged Water': 'R.A', Concrete: '2.C' } });

        expect(wrapper.text()).toContain('AWESOME Sink');
        expect(wrapper.text()).toContain('8,160');
        expect(wrapper.text()).toContain('points/min');

        const inputs = wrapper.findAll('[data-test="recycle-input"]');
        expect(inputs).toHaveLength(2);
        // each input links to its producing step
        expect(wrapper.find('a[href="#R.A"]').exists()).toBe(true);
        expect(wrapper.find('a[href="#2.C"]').exists()).toBe(true);
        expect(wrapper.find('.stub-diagram').exists()).toBe(true);
    });

    it('hides the diagram when there is no footprint', () => {
        const wrapper = mountStep({ ...sinkStep, footprint: null });
        expect(wrapper.find('.stub-diagram').exists()).toBe(false);
    });
});
