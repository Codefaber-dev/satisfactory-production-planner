import { describe, it, expect, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useSatisfactoryStore } from '../SatisfactoryStore';

describe('SatisfactoryStore', () => {
    beforeEach(() => {
        localStorage.clear();
        setActivePinia(createPinia());
    });

    it('checklist is an array with items', () => {
        const store = useSatisfactoryStore();
        expect(Array.isArray(store.checklist)).toBe(true);
        expect(store.checklist.length).toBeGreaterThan(0);
    });

    it('each item has name, tags, tier', () => {
        const store = useSatisfactoryStore();
        const item = store.checklist[0];
        expect(typeof item.name).toBe('string');
        expect(Array.isArray(item.tags)).toBe(true);
        expect(item.tier).toBeDefined();
    });

    it('tags getter returns unique values', () => {
        const store = useSatisfactoryStore();
        const tags = store.tags;
        expect(tags.length).toBe(new Set(tags).size);
    });

    it('tags getter returns sorted values', () => {
        const store = useSatisfactoryStore();
        const tags = store.tags;
        expect(tags).toEqual([...tags].sort());
    });

    it('filter with empty search and no tags returns all visible items', () => {
        const store = useSatisfactoryStore();
        const result = store.filter('', [], [], false);
        const visible = store.checklist.filter((o) => !o.hidden);
        expect(result.length).toBe(visible.length);
    });

    it('filter with search term narrows by name', () => {
        const store = useSatisfactoryStore();
        const result = store.filter('Iron', [], [], false);
        expect(result.length).toBeGreaterThan(0);
        expect(result.every((o) => o.name.toLowerCase().includes('iron'))).toBe(true);
    });

    it('filter with tag narrows to items having that tag', () => {
        const store = useSatisfactoryStore();
        const result = store.filter('', ['electronics'], [], false);
        expect(result.length).toBeGreaterThan(0);
        expect(result.every((o) => o.tags.includes('electronics'))).toBe(true);
    });

    it('filter with tier excludes items of other tiers', () => {
        const store = useSatisfactoryStore();
        const result = store.filter('', [], [4], false);
        expect(result.length).toBeGreaterThan(0);
        expect(result.every((o) => +o.tier === 4)).toBe(true);
    });

    it('filter with search and tag requires both to match', () => {
        const store = useSatisfactoryStore();
        // Find an item that has a known tag and name fragment
        const electronics = store.checklist.filter((o) => o.tags.includes('electronics') && !o.hidden);
        if (electronics.length === 0) return;
        const name = electronics[0].name.slice(0, 3);
        const result = store.filter(name, ['electronics'], [], false);
        expect(result.every((o) => o.tags.includes('electronics'))).toBe(true);
        expect(result.every((o) => o.name.toLowerCase().includes(name.toLowerCase()))).toBe(true);
    });

    it('update replaces item by name', () => {
        const store = useSatisfactoryStore();
        const first = store.checklist[0];
        const updated = { ...first, producing: 99 };
        store.update(updated);
        const found = store.checklist.find((o) => o.name === first.name);
        expect(found.producing).toBe(99);
    });

    it('resetChecklist restores original state', () => {
        const store = useSatisfactoryStore();
        const originalLength = store.checklist.length;
        const first = store.checklist[0];
        store.update({ ...first, producing: 99 });
        expect(store.checklist.find((o) => o.name === first.name).producing).toBe(99);
        store.resetChecklist();
        expect(store.checklist.length).toBe(originalLength);
        expect(store.checklist[0].producing).toBe(0);
    });
});

describe('SatisfactoryStore — DB ingredient sync (V149)', () => {
    beforeEach(() => {
        localStorage.clear();
        setActivePinia(createPinia());
    });

    const ing = (name, extra = {}) => ({
        name,
        tier: 5,
        raw: false,
        is_liquid: false,
        is_ficsmas: false,
        ...extra,
    });

    it('appends a DB-only item with derived tags and fresh state', () => {
        const store = useSatisfactoryStore();
        store.sync([ing('Test Widget'), ing('Test Ore', { tier: 1, raw: true })]);

        const sam = store.checklist.find((o) => o.name === 'Test Widget');
        expect(sam).toBeTruthy();
        expect(sam.tags).toEqual(['tier 5']);
        expect(sam.tier).toBe('5');
        expect(sam.producing).toBe(0);
        expect(sam.automated).toBe(false);

        const raw = store.checklist.find((o) => o.name === 'Test Ore');
        expect(raw.tags).toEqual(['tier 1', 'raw']);
    });

    it('curated product_list tags win over derived tags on a name match', () => {
        const store = useSatisfactoryStore();
        store.sync([ing('Battery', { tier: 3 })]);

        const battery = store.checklist.find((o) => o.name === 'Battery');
        // curated: ['electronics', 'tier 7'] — not the derived ['tier 3']
        expect(battery.tags).toContain('electronics');
        expect(battery.tags).toContain('tier 7');
        expect(battery.tier).toBe('7');
    });

    it('preserves saved user state by name across a sync', () => {
        const store = useSatisfactoryStore();
        const battery = store.checklist.find((o) => o.name === 'Battery');
        store.update({ ...battery, producing: 42, automated: true, notes: 'north field' });

        store.sync([ing('Battery'), ing('Test Widget')]);

        const after = store.checklist.find((o) => o.name === 'Battery');
        expect(after.producing).toBe(42);
        expect(after.automated).toBe(true);
        expect(after.notes).toBe('north field');
    });

    it('drops rows no longer in the DB set and resets from the catalog', () => {
        const store = useSatisfactoryStore();
        store.sync([ing('Battery'), ing('Test Widget')]);

        expect(store.checklist).toHaveLength(2);
        expect(store.checklist.find((o) => o.name === 'AI Limiter')).toBeUndefined();

        const battery = store.checklist.find((o) => o.name === 'Battery');
        store.update({ ...battery, producing: 9 });
        store.resetChecklist();

        expect(store.checklist).toHaveLength(2);
        expect(store.checklist.every((o) => o.producing === 0)).toBe(true);
    });

    it('sync without a prop is a no-op', () => {
        const store = useSatisfactoryStore();
        const before = store.checklist.length;

        store.sync(undefined);
        store.sync([]);

        expect(store.checklist).toHaveLength(before);
    });

    it('ficsmas flag derives a ficsmas tag on DB-only items', () => {
        const store = useSatisfactoryStore();
        store.sync([ing('Candy Cane', { is_ficsmas: true })]);

        expect(store.checklist.find((o) => o.name === 'Candy Cane').tags).toContain('ficsmas');
    });
});

describe('SatisfactoryStore — saved checklists CRUD (V151)', () => {
    beforeEach(() => {
        localStorage.clear();
        setActivePinia(createPinia());
    });

    it('migrates the legacy checklist key into a Default list, preserving user state', () => {
        const rows = [
            {
                name: 'Battery',
                tags: ['electronics', 'tier 7'],
                tier: '7',
                producing: 12,
                automated: true,
                storage: false,
                hidden: false,
                notes: 'legacy note',
            },
        ];
        localStorage.setItem('checklist', JSON.stringify(rows));

        const store = useSatisfactoryStore();

        expect(store.listSummaries).toHaveLength(1);
        expect(store.activeName).toBe('Default');
        expect(store.checklist.find((o) => o.name === 'Battery').producing).toBe(12);
        expect(store.checklist.find((o) => o.name === 'Battery').notes).toBe('legacy note');
        expect(localStorage.getItem('checklist')).toBeNull();
    });

    it('fresh browser gets a Default list with the full item set', () => {
        const store = useSatisfactoryStore();

        expect(store.activeName).toBe('Default');
        expect(store.checklist.length).toBeGreaterThan(0);
    });

    it('createList starts a fresh full item set and becomes active', () => {
        const store = useSatisfactoryStore();
        const battery = store.checklist.find((o) => o.name === 'Battery');
        store.update({ ...battery, producing: 5 });

        const id = store.createList('Phase 4');

        expect(store.activeId).toBe(id);
        expect(store.activeName).toBe('Phase 4');
        expect(store.checklist.find((o) => o.name === 'Battery').producing).toBe(0);
        expect(store.listSummaries).toHaveLength(2);
    });

    it('createList rejects blank names', () => {
        const store = useSatisfactoryStore();

        expect(store.createList('   ')).toBeNull();
        expect(store.listSummaries).toHaveLength(1);
    });

    it('renameList renames; blank rename is a no-op', () => {
        const store = useSatisfactoryStore();
        store.renameList(store.activeId, 'Main Base');

        expect(store.activeName).toBe('Main Base');

        store.renameList(store.activeId, '  ');
        expect(store.activeName).toBe('Main Base');
    });

    it('deleting the active list switches to a remaining one', () => {
        const store = useSatisfactoryStore();
        const first = store.activeId;
        store.createList('Second');

        store.deleteList(store.activeId);

        expect(store.activeId).toBe(first);
        expect(store.listSummaries).toHaveLength(1);
    });

    it('deleting the last list recreates a fresh Default', () => {
        const store = useSatisfactoryStore();
        const battery = store.checklist.find((o) => o.name === 'Battery');
        store.update({ ...battery, producing: 5 });

        store.deleteList(store.activeId);

        expect(store.listSummaries).toHaveLength(1);
        expect(store.activeName).toBe('Default');
        expect(store.checklist.find((o) => o.name === 'Battery').producing).toBe(0);
    });

    it('isolates row state between lists', () => {
        const store = useSatisfactoryStore();
        const first = store.activeId;
        const battery = store.checklist.find((o) => o.name === 'Battery');
        store.update({ ...battery, producing: 7 });

        store.createList('Second');
        expect(store.checklist.find((o) => o.name === 'Battery').producing).toBe(0);

        const second = store.checklist.find((o) => o.name === 'Battery');
        store.update({ ...second, producing: 3 });

        store.selectList(first);
        expect(store.checklist.find((o) => o.name === 'Battery').producing).toBe(7);
    });

    it('reset touches only the active list', () => {
        const store = useSatisfactoryStore();
        const first = store.activeId;
        const battery = store.checklist.find((o) => o.name === 'Battery');
        store.update({ ...battery, producing: 7 });

        store.createList('Second');
        store.resetChecklist();

        store.selectList(first);
        expect(store.checklist.find((o) => o.name === 'Battery').producing).toBe(7);
    });

    it('sync merges the catalog into every list', () => {
        const store = useSatisfactoryStore();
        const first = store.activeId;
        store.createList('Second');

        store.sync([{ name: 'Test Widget', tier: 2, raw: false, is_liquid: false, is_ficsmas: false }]);

        expect(store.checklist).toHaveLength(1);
        expect(store.checklist[0].name).toBe('Test Widget');

        store.selectList(first);
        expect(store.checklist).toHaveLength(1);
        expect(store.checklist[0].name).toBe('Test Widget');
    });
});
