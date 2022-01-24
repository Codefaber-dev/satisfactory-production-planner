<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                My Favorite Recipes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col space-y-10">

                <div class="flex">
                    <div class="flex flex-col space-y-4 w-64 mr-8">
                        <div class="text-2xl">
                            Presets
                        </div>

                        <ul>
                            <li v-for="(preset,name) in presets" class="flex flex-col bg-sky-200 dark:bg-sky-600 rounded shadow p-4 mt-6">
                                <p class="font-semibold mb-2">{{ name }}</p>
                                <span v-for="item in preset" class="font-sm flex py-1">
                                    <cloud-image :public-id="item.product" width="32" crop="scale" class="mr-2"/>
                                    {{ item.recipe }}
                                </span>

                                <button @click="usePreset(preset)" class="btn btn-emerald mt-2">
                                    Use Preset
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-col space-y-10 flex-1">
                        <div class="flex flex-col space-y-2">
                            <div class="text-2xl">
                                Search for a product
                            </div>
                            <input class="rounded px-4 py-2 w-full shadow dark:bg-sky-800" type="text" v-model="search">
                        </div>

                        <div :key="product.id" v-for="product in filtered"
                             class="flex flex-col bg-white dark:bg-sky-900 shadow rounded p-4 mt-4">
                            <div class="text-xl font-semibold mb-4 flex items-center">
                                <cloud-image class="mr-4" :public-id="product.name" width="48" crop="scale"/>
                                {{ product.name }}
                            </div>
                            <recipe-picker
                                class="dark:border-sky-800 dark:bg-sky-600"
                                :recipes="product.recipes"
                                :selected="product.recipes.length > 1 ? product.recipes.filter(o=>o.favorite)[0] : product.recipes[0]"
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

import RecipePicker from "@/Components/RecipePicker";
import AppLayout from "@/Layouts/AppLayout";

export default {
    name: "Index",

    components: {
        AppLayout,
        RecipePicker
    },

    props: {
        products: Array,
        favorites: Array
    },

    data() {
        return {
            search: '',
            presets : {
                'Screw Screws' : [
                    {
                        'product': 'Reinforced Iron Plate',
                        'recipe': 'Stitched Iron Plate'
                    },
                    {
                        'product': 'Computer',
                        'recipe': 'Caterium Computer'
                    },
                    {
                        'product': 'Heavy Modular Frame',
                        'recipe': 'Heavy Encased Frame'
                    },
                    {
                        'product': 'Rotor',
                        'recipe': 'Steel Rotor'
                    },
                ],
                'Pure Products' : [
                    {
                        'product': 'Aluminum Ingot',
                        'recipe': 'Pure Aluminum Ingot'
                    },
                    {
                        'product': 'Caterium Ingot',
                        'recipe': 'Pure Caterium Ingot'
                    },
                    {
                        'product': 'Copper Ingot',
                        'recipe': 'Pure Copper Ingot'
                    },
                    {
                        'product': 'Iron Ingot',
                        'recipe': 'Pure Iron Ingot'
                    },
                    {
                        'product': 'Quartz Crystal',
                        'recipe': 'Pure Quartz Crystal'
                    },
                ]
            }

        }
    },

    computed: {
        filtered() {
            if (!this.search)
                return this.products;

            return this.products.filter(o => {
                return o.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1 ||
                    o.recipes.some(oo => (oo.description || '').toLowerCase().indexOf(this.search.toLowerCase()) > -1);
            });
        }
    },

    methods: {
        update({recipe}) {
            this.$inertia.post('/favorites', {
                recipe: recipe.id
            }, {
                preserveState: true,
                preserveScroll: true
            })
        },
        usePreset(preset) {
            this.$inertia.post('/favorites/preset', {
                preset
            }, {
                preserveState: true,
                preserveScroll: true
            })
        }
    }
}
</script>

<style scoped>

</style>
