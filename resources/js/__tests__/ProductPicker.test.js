import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ProductPicker from '../Components/ProductPicker.vue';

const ProductDetailStub = {
    name: 'ProductDetail',
    template: '<div @click="$emit(\'select\', { product })" data-testid="product-detail">{{ product.name }}</div>',
    props: ['product', 'slim'],
    emits: ['select'],
};

const products = [
    { name: 'Iron Plate', tags: ['ingots'] },
    { name: 'Iron Rod', tags: ['ingots'] },
    { name: 'Wire', tags: ['electronics'] },
    { name: 'Cable', tags: ['electronics'] },
];

function factory(overrides = {}) {
    return mount(ProductPicker, {
        global: {
            components: { ProductDetail: ProductDetailStub },
            stubs: { 'cloud-image': true },
        },
        props: {
            products,
            selected: { name: 'Iron Plate' },
            ...overrides,
        },
    });
}

describe('ProductPicker', () => {
    it('renders selected product name', () => {
        const wrapper = factory();
        expect(wrapper.text()).toContain('Iron Plate');
    });

    it('menu hidden by default', () => {
        const wrapper = factory();
        expect(wrapper.vm.showMenu).toBe(false);
    });

    it('filteredProducts returns all when filter is empty', () => {
        const wrapper = factory();
        expect(wrapper.vm.filteredProducts).toHaveLength(products.length);
    });

    it('filteredProducts narrows by name', () => {
        const wrapper = factory();
        wrapper.vm.filter = 'Iron';
        const result = wrapper.vm.filteredProducts;
        expect(result).toHaveLength(2);
        expect(result.every((o) => o.name.toLowerCase().includes('iron'))).toBe(true);
    });

    it('filteredProducts case-insensitive', () => {
        const wrapper = factory();
        wrapper.vm.filter = 'wire';
        expect(wrapper.vm.filteredProducts).toHaveLength(1);
        expect(wrapper.vm.filteredProducts[0].name).toBe('Wire');
    });

    it('select emits select event with product', () => {
        const wrapper = factory();
        wrapper.vm.select({ product: products[0] });
        expect(wrapper.emitted('select')).toBeTruthy();
        expect(wrapper.emitted('select')[0][0]).toEqual({ product: products[0] });
    });

    it('select closes menu', () => {
        const wrapper = factory();
        wrapper.vm.showMenu = true;
        wrapper.vm.select({ product: products[0] });
        expect(wrapper.vm.showMenu).toBe(false);
    });

    it('hide sets showMenu to false', () => {
        const wrapper = factory();
        wrapper.vm.showMenu = true;
        wrapper.vm.hide();
        expect(wrapper.vm.showMenu).toBe(false);
    });

    it('selectSingleResult emits when exactly one result', () => {
        const wrapper = factory();
        wrapper.vm.filter = 'Wire';
        wrapper.vm.selectSingleResult();
        expect(wrapper.emitted('select')).toBeTruthy();
        expect(wrapper.emitted('select')[0][0].product.name).toBe('Wire');
    });

    it('selectSingleResult emits on exact match', () => {
        const wrapper = factory();
        wrapper.vm.filter = 'Cable';
        wrapper.vm.selectSingleResult();
        expect(wrapper.emitted('select')).toBeTruthy();
        expect(wrapper.emitted('select')[0][0].product.name).toBe('Cable');
    });

    it('selectSingleResult does not emit when multiple results', () => {
        const wrapper = factory();
        wrapper.vm.filter = 'Iron';
        wrapper.vm.selectSingleResult();
        expect(wrapper.emitted('select')).toBeFalsy();
    });
});
