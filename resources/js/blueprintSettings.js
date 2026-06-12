export const SIZE_MIN = 1;
export const SIZE_MAX = 40;

const SIZES_KEY = 'bp_sizes';
const enabledKey = (factoryId) => `bp_enabled:${factoryId ?? 'new'}`;

export const clampSize = (val) => {
    const n = Number.parseInt(val, 10);

    if (Number.isNaN(n)) return SIZE_MIN;

    return Math.min(SIZE_MAX, Math.max(SIZE_MIN, n));
};

export const loadSizes = (store) => {
    const raw = store.getItem(SIZES_KEY, {}) || {};

    return Object.fromEntries(Object.entries(raw).map(([name, size]) => [name, clampSize(size)]));
};

export const saveSizes = (store, sizes) => {
    store.setItem(SIZES_KEY, sizes);
};

export const loadEnabled = (store, factoryId) => {
    const raw = store.getItem(enabledKey(factoryId), {}) || {};

    return Object.fromEntries(Object.entries(raw).map(([name, on]) => [name, !!on]));
};

export const saveEnabled = (store, factoryId, enabled) => {
    store.setItem(enabledKey(factoryId), enabled);
};

export const effectiveMultiples = (sizes, enabled) => {
    return Object.fromEntries(
        Object.entries(enabled)
            .filter(([_, on]) => on)
            .map(([name]) => [name, clampSize(sizes[name] ?? SIZE_MIN)])
    );
};
