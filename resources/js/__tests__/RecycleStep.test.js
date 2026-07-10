import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import '../bootstrap';
import RecycleStep from '../Pages/Production/RecycleStep.vue';
import { initChecks, isChecked, resetChecks } from '@/stepChecks';

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

    it('flashes when a destination link targeting it fires (flash parity with ProductionStep)', async () => {
        vi.useFakeTimers();
        mockBus.on.mockClear();
        const wrapper = mountStep(sinkStep);

        const flashHandler = mockBus.on.mock.calls.find(([event]) => event === 'flash')?.[1];
        expect(flashHandler).toBeTruthy();

        // non-matching dest → no flash
        flashHandler({ dest: '1.A' });
        await wrapper.vm.$nextTick();
        expect(wrapper.find('tbody').classes()).not.toContain('bg-slate-300');

        // matching dest → flash on, then off after the timeout
        flashHandler({ dest: 'R.A' });
        await wrapper.vm.$nextTick();
        expect(wrapper.find('tbody').classes()).toContain('bg-slate-300');

        vi.advanceTimersByTime(1300);
        await wrapper.vm.$nextTick();
        expect(wrapper.find('tbody').classes()).not.toContain('bg-slate-300');
        vi.useRealTimers();
    });
});

describe('RecycleStep — build-check toggle (V143)', () => {
    const makeStore = () => {
        const data = {};

        return {
            getItem: (key, def = null) => (key in data ? data[key] : def),
            setItem: (key, value) => {
                data[key] = JSON.parse(JSON.stringify(value));
            },
            _data: data,
        };
    };

    beforeEach(() => {
        resetChecks();
    });

    it('renders unchecked by default', () => {
        initChecks(makeStore(), 5);
        const wrapper = mountStep(sinkStep);

        expect(wrapper.text()).toContain('⬜');
        expect(wrapper.text()).not.toContain('✅');
    });

    it('click toggles the shared store at <name>-sink and shows the checkmark', async () => {
        const store = makeStore();
        initChecks(store, 5);
        const wrapper = mountStep(sinkStep);

        const toggle = wrapper.findAll('span.cursor-pointer').find((el) => el.text().includes('⬜'));
        expect(toggle).toBeTruthy();

        await toggle.trigger('click');
        expect(isChecked('AWESOME Sink-sink')).toBe(true);
        expect(wrapper.text()).toContain('✅');
        expect(store._data['step_checks:5']).toEqual({ 'AWESOME Sink-sink': true });

        await wrapper.find('span.cursor-pointer').trigger('click');
        expect(isChecked('AWESOME Sink-sink')).toBe(false);
        expect(wrapper.text()).toContain('⬜');
    });

    it('threads its check key to the build diagram (V144)', () => {
        initChecks(makeStore(), 5);
        const wrapper = mountStep(sinkStep);

        expect(wrapper.findComponent({ name: 'build-diagram' }).attributes('check-key')).toBe('AWESOME Sink-sink');
    });
});
