import { reactive, readonly } from 'vue';

// Shared step build-completion store (V143): one map for the whole plan so the
// step row, diagram modal, master diagram, and nav panels all reflect the same
// entry. Persisted per factory to localStorage (V145) via the injected store
// (blueprintSettings pattern, V42) — unsaved plans share the 'new' bucket.
const checksKey = (factoryId) => `step_checks:${factoryId ?? 'new'}`;

const state = reactive({
    store: null,
    factoryId: null,
    checks: {},
});

export const checksState = readonly(state);

export const initChecks = (store, factoryId) => {
    const raw = store.getItem(checksKey(factoryId), {}) || {};

    state.store = store;
    state.factoryId = factoryId ?? null;
    state.checks = Object.fromEntries(Object.entries(raw).map(([key, on]) => [key, !!on]));
};

export const toggleCheck = (key) => {
    state.checks = {
        ...state.checks,
        [key]: !state.checks[key],
    };

    state.store?.setItem(checksKey(state.factoryId), state.checks);
};

export const isChecked = (key) => !!state.checks[key];

export const resetChecks = () => {
    state.store = null;
    state.factoryId = null;
    state.checks = {};
};
