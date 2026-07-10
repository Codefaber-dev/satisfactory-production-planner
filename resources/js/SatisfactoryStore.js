import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';
import product_list from './product_list';

for (const o of product_list) {
    o.tier = o.tags.filter((name) => +name.includes('tier'))[0].replace('tier ', '');
}

const fresh_product_list = [...product_list];

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

export const useSatisfactoryStore = defineStore('satisfactory', {
    state: () => ({
        checklist: useStorage('checklist', product_list),
        // canonical item defs from the DB ingredients prop — not persisted (V149)
        catalog: [],
    }),

    getters: {
        tags(state) {
            return state.checklist
                .flatMap((o) => o.tags)
                .filter((o, i, c) => c.indexOf(o) === i)
                .sort();
        },
    },

    actions: {
        // build the canonical item list from the DB ingredients prop and overlay
        // the user's saved row state by name (V149); no prop ⇒ no-op
        sync(ingredients) {
            if (!ingredients?.length) {
                return;
            }

            this.catalog = ingredients.map((ingredient) => ({
                name: ingredient.name,
                tags: curatedTags.get(ingredient.name) ?? derivedTags(ingredient),
            }));
            this.checklist = mergeRows(this.catalog, this.checklist);
        },

        resetChecklist() {
            this.checklist = this.catalog.length ? this.catalog.map(freshRow) : [...fresh_product_list];
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
            this.checklist[this.checklist.findIndex((o) => o.name === product.name)] = product;
        },
    },
});
