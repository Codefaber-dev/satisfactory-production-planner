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
        const electronics = store.checklist.filter(
            (o) => o.tags.includes('electronics') && !o.hidden
        );
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
