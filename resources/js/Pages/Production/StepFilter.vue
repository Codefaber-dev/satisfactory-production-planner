<template>
    <div class="relative text-sm font-normal text-slate-900 dark:text-slate-100">
        <input
            @focus="showMenu = true"
            @click="showMenu = true"
            @keyup.enter.prevent.stop="selectSingleResult"
            @keyup.esc="hide"
            type="text"
            v-model="query"
            class="w-56 rounded p-2 pr-8 dark:bg-slate-800"
            placeholder="Filter by ingredient..."
            data-test="step-filter-input"
        />
        <button
            v-if="modelValue"
            @click="clear"
            class="absolute right-1 top-1/2 -translate-y-1/2 rounded px-2 py-1 text-xs hover:bg-gray-200 dark:hover:bg-slate-700"
            title="Clear filter"
            data-test="step-filter-clear"
        >
            ✕
        </button>

        <!-- type-ahead menu (V110) -->
        <div
            v-show="showMenu && filteredOptions.length"
            style="top: 100%"
            class="absolute left-0 z-50 flex max-h-[400px] w-full flex-col overflow-y-auto rounded-b border border-sky-300 bg-white shadow-lg dark:border-sky-900 dark:bg-slate-900"
        >
            <button
                @click="select(option)"
                v-for="option in filteredOptions"
                :key="option"
                class="border-b border-gray-300 p-2 text-left hover:bg-sky-100 dark:border-sky-800 dark:hover:bg-sky-900"
                data-test="step-filter-option"
            >
                {{ option }}
            </button>
        </div>

        <!-- modal bg -->
        <div @click="hide" v-show="showMenu" class="fixed inset-0 z-40 bg-black opacity-5"></div>
    </div>
</template>

<script>
export default {
    name: 'StepFilter',

    props: {
        // all ingredient + product names of the displayed plan (deduped, sorted)
        options: {
            type: Array,
            default: () => [],
        },
        // the selected filter item (null = no filter)
        modelValue: {
            type: String,
            default: null,
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            query: '',
            showMenu: false,
        };
    },

    computed: {
        filteredOptions() {
            if (!this.query.length) {
                return this.options;
            }
            return this.options.filter((o) => o.toLowerCase().indexOf(this.query.toLowerCase()) > -1);
        },
    },

    methods: {
        hide() {
            this.showMenu = false;
        },

        select(option) {
            this.query = option;
            this.$emit('update:modelValue', option);
            this.hide();
        },

        selectSingleResult() {
            if (this.filteredOptions.length === 1) {
                this.select(this.filteredOptions[0]);
                return;
            }

            const exact = this.filteredOptions.find((o) => o.toLowerCase() === this.query.toLowerCase());
            if (exact) {
                this.select(exact);
            }
        },

        clear() {
            this.query = '';
            this.$emit('update:modelValue', null);
            this.hide();
        },
    },
};
</script>
