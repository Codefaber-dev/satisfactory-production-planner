import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';
import product_list from './product_list';

product_list.forEach((o) => {
    o.tier = o.tags.filter((name) => +name.includes('tier'))[0].replace('tier ', '');
});

const fresh_product_list = [...product_list];

export const useSatisfactoryStore = defineStore('satisfactory', {
    state: () => ({
        checklist: useStorage('checklist', product_list),
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
        resetChecklist() {
            this.checklist = [...fresh_product_list];
        },

        filter(search, tags = [], tiers = [], showHidden) {
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
