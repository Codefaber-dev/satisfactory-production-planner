import { useStorage } from '@vueuse/core';
import { defineStore } from 'pinia';
import product_list from './product_list';

for (const o of product_list) {
    o.tier = o.tags.filter((name) => +name.includes('tier'))[0].replace('tier ', '');
}

// curated tags win over derived ones when an ingredient matches by name (V149)
const curatedTags = new Map(product_list.map((o) => [o.name, o.tags]));

// DB-only items: milestone tier unknown, approximate with the DB tier (§C)
const derivedTags = (ingredient) => {
    const tags = [`tier ${ingredient.tier ?? 0}`];

    if (ingredient.raw) {
        tags.push('raw');
    }

    if (ingredient.is_ficsmas) {
        tags.push('ficsmas');
    }

    return tags;
};

const tierFromTags = (tags) => (tags.find((tag) => tag.includes('tier')) || 'tier 0').replace('tier ', '');

const freshRow = ({ name, tags }) => ({
    name,
    tags,
    tier: tierFromTags(tags),
    producing: 0,
    automated: false,
    storage: false,
    // Dimensional Depot upload automated? (V150)
    depot: false,
    hidden: false,
    notes: '',
});

// pre-sync fallback rows — deep-copied so lists never share row objects
const staticRows = () => product_list.map((o) => ({ ...o, tags: [...o.tags], depot: false }));

// canonical rows from the catalog, user state overlaid by name (V149)
const mergeRows = (catalog, rows) => {
    const byName = new Map((rows || []).map((o) => [o.name, o]));

    return catalog.map((item) => {
        const row = freshRow(item);
        const existing = byName.get(item.name);

        if (!existing) {
            return row;
        }

        return {
            ...row,
            producing: existing.producing ?? 0,
            automated: !!existing.automated,
            storage: !!existing.storage,
            depot: !!existing.depot,
            hidden: !!existing.hidden,
            notes: existing.notes ?? '',
        };
    });
};

// one-time V151 migration: the legacy single-array 'checklist' key becomes the
// rows of a named 'Default' list; malformed JSON falls back to the static list
const legacyRows = () => {
    try {
        const raw = window.localStorage.getItem('checklist');

        return raw ? JSON.parse(raw) : null;
    } catch (error) {
        console.error('Legacy checklist unreadable, starting fresh:', error);

        return null;
    }
};

export const useSatisfactoryStore = defineStore('satisfactory', {
    state: () => {
        const lists = useStorage('checklists', {});
        const activeId = useStorage('checklist_active', '');

        if (!Object.keys(lists.value).length) {
            lists.value = { default: { name: 'Default', rows: legacyRows() ?? staticRows() } };
            window.localStorage.removeItem('checklist');
        }

        if (!lists.value[activeId.value]) {
            activeId.value = Object.keys(lists.value)[0];
        }

        return {
            // named checklists + active pointer, both persisted (V151)
            lists,
            activeId,
            // canonical item defs from the DB ingredients prop — not persisted (V149)
            catalog: [],
        };
    },

    getters: {
        // the active list's rows — every page binding and action reads this
        checklist(state) {
            return state.lists[state.activeId]?.rows ?? [];
        },

        listSummaries(state) {
            return Object.entries(state.lists).map(([id, list]) => ({ id, name: list.name }));
        },

        activeName(state) {
            return state.lists[state.activeId]?.name ?? '';
        },

        tags() {
            return this.checklist
                .flatMap((o) => o.tags)
                .filter((o, i, c) => c.indexOf(o) === i)
                .sort();
        },
    },

    actions: {
        freshRows() {
            return this.catalog.length ? this.catalog.map(freshRow) : staticRows();
        },

        // build the canonical item list from the DB ingredients prop and overlay
        // every list's saved row state by name (V149); no prop ⇒ no-op
        sync(ingredients) {
            if (!ingredients?.length) {
                return;
            }

            this.catalog = ingredients.map((ingredient) => ({
                name: ingredient.name,
                tags: curatedTags.get(ingredient.name) ?? derivedTags(ingredient),
            }));
            this.lists = Object.fromEntries(
                Object.entries(this.lists).map(([id, list]) => [
                    id,
                    { ...list, rows: mergeRows(this.catalog, list.rows) },
                ])
            );
        },

        createList(name) {
            const trimmed = (name ?? '').trim();

            if (!trimmed) {
                return null;
            }

            const id = crypto.randomUUID();
            this.lists = { ...this.lists, [id]: { name: trimmed, rows: this.freshRows() } };
            this.activeId = id;

            return id;
        },

        renameList(id, name) {
            const trimmed = (name ?? '').trim();

            if (!trimmed || !this.lists[id]) {
                return;
            }

            this.lists = { ...this.lists, [id]: { ...this.lists[id], name: trimmed } };
        },

        deleteList(id) {
            const { [id]: removed, ...rest } = this.lists;

            if (!removed) {
                return;
            }

            // never zero lists: deleting the last recreates a fresh Default (V151)
            this.lists = Object.keys(rest).length
                ? rest
                : { default: { name: 'Default', rows: this.freshRows() } };

            if (!this.lists[this.activeId]) {
                this.activeId = Object.keys(this.lists)[0];
            }
        },

        selectList(id) {
            if (this.lists[id]) {
                this.activeId = id;
            }
        },

        resetChecklist() {
            const active = this.lists[this.activeId];

            if (!active) {
                return;
            }

            this.lists = { ...this.lists, [this.activeId]: { ...active, rows: this.freshRows() } };
        },

        filter(search, tags, tiers, showHidden) {
            return this.checklist.filter((o) => {
                if (o.hidden && !showHidden) {
                    return false;
                }

                if (tiers.length && !tiers.includes(+o.tier)) {
                    return false;
                }

                switch (true) {
                    case !search.toString().trim() && !tags.length:
                    case !search.toString().trim() && o.tags.some((oo) => tags.indexOf(oo) > -1):
                    case !tags.length && o.name.toLowerCase().indexOf(search.toLowerCase()) > -1:
                    case o.tags.some((oo) => tags.indexOf(oo) > -1) &&
                        o.name.toLowerCase().indexOf(search.toLowerCase()) > -1:
                        return true;
                }
                return false;
            });
        },

        update(product) {
            const rows = this.checklist;
            rows[rows.findIndex((o) => o.name === product.name)] = product;
        },
    },
});
