import { beforeEach, describe, expect, it } from 'vitest';
import { checksState, initChecks, isChecked, resetChecks, toggleCheck } from '@/stepChecks';

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

describe('stepChecks store (V143)', () => {
    beforeEach(() => {
        resetChecks();
    });

    it('starts empty on a fresh store — all steps unchecked', () => {
        initChecks(makeStore(), 5);

        expect(checksState.checks).toEqual({});
        expect(isChecked('Iron Ingot-base')).toBe(false);
    });

    it('toggle flips the entry and isChecked reflects it', () => {
        initChecks(makeStore(), 5);

        toggleCheck('Iron Ingot-base');
        expect(isChecked('Iron Ingot-base')).toBe(true);

        toggleCheck('Iron Ingot-base');
        expect(isChecked('Iron Ingot-base')).toBe(false);
    });

    it('replaces the map immutably on toggle', () => {
        initChecks(makeStore(), 5);
        const before = checksState.checks;

        toggleCheck('Iron Ingot-base');
        expect(checksState.checks).not.toBe(before);
    });
});

describe('stepChecks persistence (V145)', () => {
    beforeEach(() => {
        resetChecks();
    });

    it('persists per factory under step_checks:<id> on every toggle', () => {
        const store = makeStore();
        initChecks(store, 5);

        toggleCheck('Iron Ingot-base');
        expect(store._data['step_checks:5']).toEqual({ 'Iron Ingot-base': true });

        toggleCheck('Iron Ingot-base');
        expect(store._data['step_checks:5']).toEqual({ 'Iron Ingot-base': false });
    });

    it('uses the "new" bucket when there is no factory id', () => {
        const store = makeStore();
        initChecks(store, undefined);

        toggleCheck('AWESOME Sink-sink');
        expect(store._data['step_checks:new']).toEqual({ 'AWESOME Sink-sink': true });
    });

    it('round-trips: re-init restores the persisted map', () => {
        const store = makeStore();
        initChecks(store, 5);
        toggleCheck('Iron Ingot-base');
        toggleCheck('Copper Ingot-base');

        resetChecks();
        initChecks(store, 5);

        expect(isChecked('Iron Ingot-base')).toBe(true);
        expect(isChecked('Copper Ingot-base')).toBe(true);
        expect(isChecked('Steel Ingot-base')).toBe(false);
    });

    it('isolates factories: one factory’s checks never leak into another', () => {
        const store = makeStore();
        initChecks(store, 5);
        toggleCheck('Iron Ingot-base');

        initChecks(store, 9);
        expect(isChecked('Iron Ingot-base')).toBe(false);

        initChecks(store, undefined);
        expect(isChecked('Iron Ingot-base')).toBe(false);

        initChecks(store, 5);
        expect(isChecked('Iron Ingot-base')).toBe(true);
    });

    it('coerces persisted values to booleans on load', () => {
        const store = makeStore();
        store.setItem('step_checks:5', { 'Iron Ingot-base': 1, 'Copper Ingot-base': 0 });

        initChecks(store, 5);

        expect(checksState.checks).toEqual({ 'Iron Ingot-base': true, 'Copper Ingot-base': false });
    });

    it('tolerates a null persisted entry', () => {
        const store = makeStore();
        store.setItem('step_checks:5', null);

        initChecks(store, 5);

        expect(checksState.checks).toEqual({});
    });
});
