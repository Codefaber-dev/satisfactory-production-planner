import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import '../bootstrap';
import ProductionSummary from '../Pages/Production/ProductionSummary.vue';

const baseProduction = {
    intermediate_materials: {},
    byproducts: {},
};

function mountSummary(extraProps = {}) {
    return mount(ProductionSummary, {
        global: {
            stubs: { CloudImage: true, BuildingSummary: true },
        },
        props: {
            production: baseProduction,
            rawMaterials: {},
            newImports: {},
            disabledRawMaterials: {},
            buildingChecks: {},
            production__building_summary: { total_build_cost: {} },
            production__building_details: {},
            production__total_power: 0,
            production__total_power_generated: 0,
            production__net_power: 0,
            rawUnchanged: true,
            ...extraProps,
        },
    });
}

describe('ProductionSummary — recycling output (T110 / V66 / V67)', () => {
    beforeEach(() => vi.clearAllMocks());

    it('renders the recycled points/min line', () => {
        const wrapper = mountSummary({
            recycling: { points: 8160, recycled: {}, packaged: [], waste: {} },
        });
        const line = wrapper.find('[data-test="recycled-points"]');
        expect(line.exists()).toBe(true);
        expect(line.text()).toContain('8,160');
    });

    it('renders a packaged-fluid recycle row', () => {
        const wrapper = mountSummary({
            recycling: {
                points: 7800,
                recycled: {},
                packaged: [
                    { fluid: 'Water', product: 'Packaged Water', qty: 60, buildings: 1, power: 10, container: 'Empty Canister', container_qty: 60, points: 7800 },
                ],
                waste: {},
            },
        });
        const rows = wrapper.findAll('[data-test="recycled-packaged-row"]');
        expect(rows).toHaveLength(1);
        expect(rows[0].text()).toContain('Packaged Water');
        expect(rows[0].text()).toContain('Water');
    });

    it('renders no recycling section when there is no recycling data', () => {
        const wrapper = mountSummary({ recycling: null });
        expect(wrapper.find('[data-test="recycled-points"]').exists()).toBe(false);
    });
});
