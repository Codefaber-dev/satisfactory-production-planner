<template>
    <app-layout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Checklist</h2>
        </template>

        <div class="flex flex-col space-y-8 py-12">
            <div class="mx-auto flex max-w-7xl space-x-10 sm:px-6 lg:px-8">
                <!-- left column -->
                <form class="w-full" @submit.prevent="fetch">
                    <div
                        class="flex w-full flex-col justify-center space-y-4 bg-white p-4 shadow-xl dark:bg-gray-800 sm:rounded-lg"
                    >
                        <button @click="promptReset" class="btn-sm btn-emerald">Reset Checklist</button>

                        <span class="whitespace-nowrap font-semibold"> Toggle Hidden </span>
                        <label class="cursor-pointer rounded bg-slate-200 px-2 py-1 dark:bg-slate-700">
                            <input type="checkbox" v-model="showHidden" />
                            {{ showHidden ? 'Shown' : 'Hidden' }}
                        </label>

                        <span class="whitespace-nowrap font-semibold"> Search Products </span>
                        <input
                            v-model="search"
                            class="w-full rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800"
                        />

                        <span class="font-semibold"> Tier </span>

                        <label
                            class="cursor-pointer rounded bg-slate-200 px-2 py-1 dark:bg-slate-700"
                            :key="tag"
                            v-for="tag in tierTags"
                            :for="`tag-${tag}`"
                        >
                            <input type="checkbox" :id="`tag-${tag}`" :value="tag" v-model="selectedTiers" />
                            Tier {{ tag }}
                        </label>

                        <span class="font-semibold"> Tags </span>

                        <input
                            v-model="tagFilter"
                            placeholder="Filter Tags"
                            class="w-full rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800"
                        />

                        <label
                            class="cursor-pointer whitespace-nowrap rounded bg-slate-200 px-2 py-1 dark:bg-slate-700"
                            :key="tag"
                            v-for="tag in filteredTags"
                            :for="`tag-${tag}`"
                        >
                            <input type="checkbox" :id="`tag-${tag}`" :value="tag" v-model="tags" />
                            {{ tag.$ucwords() }}
                        </label>
                    </div>
                </form>

                <!-- right col -->
                <div class="flex-1">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th
                                    @click="reSortBy('name')"
                                    class="cursor-pointer whitespace-nowrap px-2 hover:underline"
                                >
                                    Product
                                </th>
                                <th
                                    @click="reSortBy('tier')"
                                    class="cursor-pointer whitespace-nowrap px-2 hover:underline"
                                >
                                    Tier
                                </th>
                                <th
                                    @click="reSortBy('producing')"
                                    class="cursor-pointer whitespace-normal px-2 text-center hover:underline"
                                >
                                    Producing Qty
                                </th>
                                <th
                                    @click="reSortBy('automated')"
                                    class="cursor-pointer whitespace-normal px-2 text-center hover:underline"
                                >
                                    Production Automated?
                                </th>
                                <th
                                    @click="reSortBy('storage')"
                                    class="cursor-pointer whitespace-normal px-2 text-center hover:underline"
                                >
                                    Storage Automated?
                                </th>
                                <th
                                    @click="reSortBy('hidden')"
                                    class="cursor-pointer whitespace-normal px-2 text-center hover:underline"
                                >
                                    Hide?
                                </th>
                                <th
                                    @click="reSortBy('notes')"
                                    class="cursor-pointer whitespace-nowrap px-2 hover:underline"
                                >
                                    Notes
                                </th>
                            </tr>
                        </thead>

                        <tr :key="product.name" v-for="product in filteredProducts">
                            <td nowrap="nowrap" class="p-2">
                                <div class="flex items-center whitespace-nowrap pr-16 text-sm">
                                    <cloud-image
                                        :public-id="product.name"
                                        crop="scale"
                                        quality="100"
                                        width="64"
                                        class="mr-4 h-16 w-16"
                                        :alt="product.name"
                                    />
                                    {{ product.name }}
                                </div>
                            </td>
                            <td nowrap="nowrap" class="p-2">
                                {{ product.tags.filter((o) => o.indexOf('tier') > -1)[0].$ucwords() }}
                            </td>
                            <td nowrap="nowrap" class="p-2">
                                <input
                                    type="text"
                                    :class="[
                                        product.producing
                                            ? 'bg-emerald-300 dark:bg-emerald-600'
                                            : 'bg-slate-300 dark:bg-slate-600',
                                    ]"
                                    class="w-16 rounded px-2 shadow"
                                    v-model.lazy.number="product.producing"
                                />
                            </td>
                            <td nowrap="nowrap" class="p-2">
                                <label
                                    :class="[
                                        product.automated
                                            ? 'bg-emerald-300 dark:bg-emerald-600'
                                            : 'bg-rose-300 dark:bg-rose-600',
                                    ]"
                                    class="cursor-pointer rounded py-1 pr-2 pl-1"
                                >
                                    <input type="checkbox" class="w-0 appearance-none" v-model="product.automated" />
                                    {{ product.automated ? 'Yes' : 'No' }}
                                </label>
                            </td>
                            <td nowrap="nowrap" class="p-2">
                                <label
                                    :class="[
                                        product.storage
                                            ? 'bg-emerald-300 dark:bg-emerald-600'
                                            : 'bg-rose-300 dark:bg-rose-600',
                                    ]"
                                    class="cursor-pointer rounded py-1 pr-2 pl-1"
                                >
                                    <input type="checkbox" class="w-0 appearance-none" v-model="product.storage" />
                                    {{ product.storage ? 'Yes' : 'No' }}
                                </label>
                            </td>
                            <td nowrap="nowrap" class="p-2">
                                <label
                                    :class="[
                                        product.hidden
                                            ? 'bg-sky-300 dark:bg-sky-600'
                                            : 'bg-slate-300 dark:bg-slate-600',
                                    ]"
                                    class="cursor-pointer rounded py-1 pr-2 pl-1"
                                >
                                    <input type="checkbox" class="w-0 appearance-none" v-model="product.hidden" />
                                    {{ product.hidden ? 'Show' : 'Hide' }}
                                </label>
                            </td>
                            <td nowrap="nowrap" class="p-2">
                                <textarea
                                    :class="[
                                        product.notes
                                            ? 'bg-emerald-300 dark:bg-emerald-600'
                                            : 'bg-slate-300 dark:bg-slate-600',
                                    ]"
                                    class="h-16 w-96 rounded p-1 px-2 text-sm shadow"
                                    v-model.lazy="product.notes"
                                ></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { useSatisfactoryStore } from '@/SatisfactoryStore';
import { mapStores } from 'pinia';
import AppLayout from '@/Layouts/AppLayout';
import CloudImage from '@/Components/CloudImage';

export default {
    name: 'Index',

    components: {
        AppLayout,
        CloudImage,
    },

    data() {
        return {
            search: '',
            tagFilter: '',
            tags: [],
            selectedTiers: [],
            sortBy: 'name',
            sortAsc: true,
            showHidden: false,
        };
    },

    methods: {
        reSortBy(prop) {
            if (this.sortBy === prop) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortBy = prop;
                this.sortAsc = true;
            }

            this.$forceUpdate();
        },

        promptReset() {
            const confirmed = confirm('Are you sure you want to reset the checklist? This cannot be undone.');

            if (!confirmed) {
                return;
            }

            this.satisfactoryStore.resetChecklist();
        },

        doSort(a, b) {
            let aa = (a == +a ? +a : a) || 0,
                bb = (b == +b ? +b : b) || 0;

            if (aa < bb) {
                return this.sortAsc ? -1 : 1;
            }
            if (aa > bb) {
                return this.sortAsc ? 1 : -1;
            }

            return 0;
        },
    },

    computed: {
        ...mapStores(useSatisfactoryStore),

        filteredProducts() {
            return this.satisfactoryStore
                .filter(this.search, this.tags, this.selectedTiers, this.showHidden)
                .sort((a, b) => this.doSort(a[this.sortBy], b[this.sortBy]));
        },

        filteredTags() {
            if (!this.tagFilter.trim().length) {
                return this.satisfactoryStore.tags.filter((o) => o.toLowerCase().indexOf('tier') < 0);
            }

            return this.satisfactoryStore.tags
                .filter((o) => o.toLowerCase().indexOf('tier') < 0)
                .filter((o) => o.toLowerCase().indexOf(this.tagFilter.toLowerCase()) > -1);
        },

        tierTags() {
            return [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        },
    },
};
</script>

<style scoped></style>
