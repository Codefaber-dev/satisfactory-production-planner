import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import '../bootstrap';
import ChecklistIndex from '../Pages/Checklist/Index.vue';
import { useSatisfactoryStore } from '../SatisfactoryStore';

const ing = (name, extra = {}) => ({
    name,
    tier: 3,
    raw: false,
    is_liquid: false,
    is_ficsmas: false,
    ...extra,
});

// mounted() syncs the store, replacing every row — flush that re-render before
// querying, or finds grab detached elements from the pre-sync static list
const mountPage = async (pinia, ingredients) => {
    const wrapper = mount(ChecklistIndex, {
        global: {
            plugins: [pinia],
            stubs: {
                AppLayout: { template: '<div><slot name="header" /><slot /></div>' },
                CloudImage: true,
            },
        },
        props: { ingredients },
    });
    await wrapper.vm.$nextTick();

    return wrapper;
};

describe('Checklist Index — Dimensional Depot column (V150)', () => {
    let pinia;

    beforeEach(() => {
        localStorage.clear();
        pinia = createPinia();
        setActivePinia(pinia);
    });

    it('renders a sortable Dimensional Depot column right of Storage Automated', async () => {
        const wrapper = await mountPage(pinia, [ing('Test Widget')]);

        const headers = wrapper.findAll('th').map((th) => th.text());
        expect(headers).toContain('Dimensional Depot');
        expect(headers.indexOf('Dimensional Depot')).toBe(headers.indexOf('Storage Automated?') + 1);
    });

    it('toggle flips the row depot flag and defaults to No', async () => {
        const wrapper = await mountPage(pinia, [ing('Test Widget')]);
        const store = useSatisfactoryStore();

        const cell = wrapper.find('[data-depot-cell]');
        expect(cell.text()).toBe('No');

        await cell.find('input[type="checkbox"]').setValue(true);

        expect(store.checklist.find((o) => o.name === 'Test Widget').depot).toBe(true);
        expect(cell.text()).toBe('Yes');
    });

    it('sorts by the depot column via its header', async () => {
        const wrapper = await mountPage(pinia, [ing('Alpha Widget'), ing('Beta Widget')]);
        const store = useSatisfactoryStore();

        const beta = store.checklist.find((o) => o.name === 'Beta Widget');
        store.update({ ...beta, depot: true });

        // ascending: false (Alpha) before true (Beta); second click flips
        await wrapper.find('[data-depot-header]').trigger('click');
        let names = wrapper.findAll('td:first-child').map((td) => td.text().trim());
        expect(names[0]).toBe('Alpha Widget');

        await wrapper.find('[data-depot-header]').trigger('click');
        names = wrapper.findAll('td:first-child').map((td) => td.text().trim());
        expect(names[0]).toBe('Beta Widget');
    });
});

describe('SatisfactoryStore — depot state (V150)', () => {
    beforeEach(() => {
        localStorage.clear();
        setActivePinia(createPinia());
    });

    it('fresh synced rows carry depot=false; overlay preserves it; reset clears it', () => {
        const store = useSatisfactoryStore();
        store.sync([ing('Test Widget')]);

        const row = store.checklist.find((o) => o.name === 'Test Widget');
        expect(row.depot).toBe(false);

        store.update({ ...row, depot: true });
        store.sync([ing('Test Widget')]);
        expect(store.checklist.find((o) => o.name === 'Test Widget').depot).toBe(true);

        store.resetChecklist();
        expect(store.checklist.find((o) => o.name === 'Test Widget').depot).toBe(false);
    });
});
