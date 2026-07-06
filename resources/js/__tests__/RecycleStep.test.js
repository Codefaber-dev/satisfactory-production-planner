import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import '../bootstrap';
import RecycleStep from '../Pages/Production/RecycleStep.vue';

const mockBus = { on: vi.fn(), emit: vi.fn() };

function mountStep(step, extra = {}) {
    return mount(RecycleStep, {
        global: {
            mocks: { Bus: mockBus },
            stubs: {
                CloudImage: true,
                BuildDiagram: { name: 'build-diagram', template: '<div class="stub-diagram" />' },
            },
        },
        props: { step, identifier: 'R.A', diagrams: true, ...extra },
    });
}

// V91: RecycleStep is now the terminal AWESOME Sink step only — packaging fluids
// render through ProductionStep (V90). It mirrors the ProductionStep row layout.
const sinkStep = {
    type: 'sink',
    name: 'AWESOME Sink',
    building: 'AWESOME Sink',
    num_buildings: 1,
    power: 30,
    footprint: { foundations: 24 },
    inputs: [
        { item: 'Packaged Water', qty: 60 },
        { item: 'Concrete', qty: 30 },
    ],
    output: { points: 8160 },
};

describe('RecycleStep — terminal AWESOME Sink step (V91)', () => {
    it('mirrors the ProductionStep row layout: identifier th, step cell, inputs cell, diagram row', () => {
        const wrapper = mountStep(sinkStep, { stepMap: { 'Packaged Water': 'R.A', Concrete: '2.C' } });

        // ProductionStep-shaped wrapper + cells
        expect(wrapper.find('tbody.lg\\:table-row-group').exists()).toBe(true);
        expect(wrapper.find('th.lg\\:table-cell').exists()).toBe(true);
        expect(wrapper.findAll('td.lg\\:table-cell').length).toBeGreaterThanOrEqual(2);
        expect(wrapper.text()).toContain('R.A');
    });

    it('renders building, points output, and inputs linked to producing steps', () => {
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

    it('registers in QuickNav under its R.* identifier (V95)', () => {
        mockBus.emit.mockClear();
        mountStep(sinkStep, { identifier: 'R.B' });
        expect(mockBus.emit).toHaveBeenCalledWith('RegisterQuickNav', { name: 'AWESOME Sink', identifier: 'R.B' });
    });
});
