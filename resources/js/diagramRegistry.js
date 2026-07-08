import { reactive, readonly } from 'vue';

// Registry of every mounted build diagram, for modal cross-navigation (V130).
// BuildDiagram registers {identifier, name, open} on mount and unregisters on
// unmount, so the set always mirrors the diagrams the current plan renders —
// imported steps and diagram-less raws never mount one, so they never appear.
const entries = reactive([]);

export const diagramRegistry = readonly(entries);

export function registerDiagram(entry) {
    entries.push(entry);

    return () => {
        const index = entries.indexOf(entry);

        if (index >= 0) {
            entries.splice(index, 1);
        }
    };
}

export function resetDiagramRegistry() {
    entries.splice(0);
}
