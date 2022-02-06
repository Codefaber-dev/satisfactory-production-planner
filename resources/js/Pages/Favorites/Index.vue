<template>
    <app-layout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100"
            >
                My Favorite Recipes
            </h2>
        </template>

        <div class="py-12">
            <div
                class="mx-auto flex max-w-7xl flex-col space-y-10 sm:px-6 lg:px-8"
            >
                <div class="flex">
                    <div class="mr-8 flex w-64 flex-col space-y-4">
                        <div class="text-2xl">Presets</div>

                        <ul>
                            <li
                                v-for="(preset, name) in presets"
                                class="mt-6 flex flex-col rounded bg-sky-200 p-4 shadow dark:bg-sky-600"
                            >
                                <p class="mb-2 font-semibold">
                                    <span>{{ name }}</span>
                                </p>
                                <span
                                    v-for="item in preset"
                                    class="font-sm flex py-1"
                                >
                                    <cloud-image
                                        :public-id="item.product"
                                        width="32"
                                        crop="scale"
                                        class="mr-2"
                                    />
                                    {{ item.recipe }}
                                </span>

                                <button
                                    @click="usePreset(preset)"
                                    class="btn btn-emerald mt-2"
                                >
                                    Use Preset
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-1 flex-col space-y-10">
                        <div class="flex flex-col space-y-2">
                            <div class="text-2xl">Search for a product</div>
                            <input
                                class="w-full rounded px-4 py-2 shadow dark:bg-sky-800"
                                type="text"
                                v-model="search"
                            />
                        </div>

                        <div
                            :key="product.id"
                            v-for="product in filtered"
                            class="mt-4 flex flex-col rounded bg-white p-4 shadow dark:bg-sky-900"
                        >
                            <div
                                class="mb-4 flex items-center text-xl font-semibold"
                            >
                                <cloud-image
                                    class="mr-4"
                                    :public-id="product.name"
                                    width="48"
                                    crop="scale"
                                />
                                <span class="flex-1">{{ product.name }}</span>
                                <button
                                    :disabled="
                                        !product.recipes.filter(
                                            (o) => o.favorite
                                        ).length
                                    "
                                    @click="reset(product)"
                                    class="btn-sm btn-emerald ml-2"
                                >
                                    Reset To Default
                                </button>
                            </div>
                            <recipe-picker
                                class="dark:border-sky-800 dark:bg-sky-600"
                                :recipes="product.recipes"
                                :selected="getSelected(product.recipes)"
                                @select="update"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import RecipePicker from '@/Components/RecipePicker';
import AppLayout from '@/Layouts/AppLayout';
import { debounce } from 'lodash';

export default {
    name: 'Index',

    components: {
        AppLayout,
        RecipePicker,
    },

    props: {
        products: Array,
        favorites: Array,
    },

    data() {
        return {
            search: '',
            presets: {
                'Screw Screws': [
                    {
                        product: 'Reinforced Iron Plate',
                        recipe: 'Stitched Iron Plate',
                    },
                    {
                        product: 'Computer',
                        recipe: 'Caterium Computer',
                    },
                    {
                        product: 'Heavy Modular Frame',
                        recipe: 'Heavy Encased Frame',
                    },
                    {
                        product: 'Rotor',
                        recipe: 'Steel Rotor',
                    },
                ],
                'Pure Products': [
                    {
                        product: 'Aluminum Ingot',
                        recipe: 'Pure Aluminum Ingot',
                    },
                    {
                        product: 'Caterium Ingot',
                        recipe: 'Pure Caterium Ingot',
                    },
                    {
                        product: 'Copper Ingot',
                        recipe: 'Pure Copper Ingot',
                    },
                    {
                        product: 'Iron Ingot',
                        recipe: 'Pure Iron Ingot',
                    },
                    {
                        product: 'Quartz Crystal',
                        recipe: 'Pure Quartz Crystal',
                    },
                ],
                'Test Circular Dependency Resolution': [
                    {
                        product: 'Computer',
                        recipe: 'Caterium Computer',
                    },
                    {
                        product: 'Circuit Board',
                        recipe: 'Caterium Circuit Board',
                    },
                    {
                        product: 'Fuel',
                        recipe: 'Unpackage Fuel',
                    },
                    {
                        product: 'Packaged Fuel',
                        recipe: 'default',
                    },
                    {
                        product: 'Rubber',
                        recipe: 'Recycled Rubber',
                    },
                    {
                        product: 'Plastic',
                        recipe: 'Recycled Plastic',
                    },
                ],
            },
        };
    },

    computed: {
        searchDebounced: {
            get() {
                return this.search;
            },
            set: debounce(function (newValue) {
                this.search = newValue;
            }, 900),
        },

        filtered() {
            if (!this.searchDebounced) return this.products;

            return this.products.filter((o) => {
                return (
                    o.name
                        .toLowerCase()
                        .indexOf(this.searchDebounced.toLowerCase()) > -1 ||
                    o.recipes.some(
                        (oo) =>
                            (oo.description || '')
                                .toLowerCase()
                                .indexOf(this.searchDebounced.toLowerCase()) >
                            -1
                    )
                );
            });
        },
    },

    methods: {
        update({ recipe }) {
            this.$inertia.post(
                '/favorites',
                {
                    recipe: recipe.id,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                }
            );
        },
        reset(product) {
            this.$inertia.delete(`/favorites/${product.id}`);
        },
        usePreset(preset) {
            this.$inertia.post(
                '/favorites/preset',
                {
                    preset,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                }
            );
        },
        getSelected(recipes) {
            if (recipes.length === 0) return recipes[0];

            if (recipes.some((o) => o.favorite))
                return recipes.filter((o) => o.favorite)[0];

            if (recipes.some((o) => !o.description))
                return recipes.filter((o) => !o.description)[0];

            return recipes[0];
        },
    },
};
</script>

<style scoped></style>
