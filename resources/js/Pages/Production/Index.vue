<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                New Production Line <span v-if="product">: {{product.name}}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex space-x-10">
                <!-- left column -->
                <form :class="[done ? 'w-1/3' : 'w-full']" @submit.prevent="fetch">
                    <div class="bg-white dark:bg-gray-700 shadow-xl sm:rounded-lg p-4 flex flex-col space-y-8 items-center transition-all">
                        <div class="bg-white dark:bg-gray-700 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Select a product</span>
                            <select @change="setDefaultRecipe" class="rounded shadow dark:bg-gray-800 dark:text-gray-100" v-model="product" name="product" id="product">
                                <option :key="option.id" v-for="option in products" :value="option">{{
                                        option.name
                                    }}
                                </option>
                            </select>
                        </div>
                        <div v-if="product" class="bg-white dark:bg-gray-700 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Select a recipe</span>
                            <select class="rounded shadow dark:bg-gray-800 dark:text-gray-100" v-model="recipe" name="recipe" id="recipe">
                                <option :key="option.id" v-for="option in recipes[product.name]" :value="option">
                                    <span v-if="option.favorite">&star;</span>
                                    {{ option.description || 'default' }}
                                </option>
                            </select>
                            <button :disabled="working" @click="setNewFavorite"
                                    class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 inline rounded-xl mt-2"
                                    v-if="recipe && !recipe.favorite">Set Favorite
                            </button>
                        </div>
                        <div v-if="recipe" class="bg-white dark:bg-gray-700 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Enter quantity per minute</span>
                            <input @input="fetch" type="number" step="0.5" min="0" v-model="yield" class="rounded shadow">
                        </div>
                        <div v-if="yield" class="bg-white dark:bg-gray-700 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Select default building variant</span>
                            <select @change="fetch" v-model="variant" class="rounded shadow">
                                <option>mk1</option>
                                <option>mk2</option>
                                <option>mk3</option>
                                <option>mk4</option>
                            </select>
                        </div>

                        <div class="space-x-4">
                            <button :disabled="working" @click="fetch"
                                    class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 inline rounded-xl"
                                    v-if="yield">Go
                            </button>
                            <button :disabled="working" v-if="product" @click="reset"
                                    class="px-4 py-2 text-white bg-gray-500 hover:bg-gray-600 inline rounded-xl">
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import {Inertia} from '@inertiajs/inertia';


    export default {
        props : [
            'products',
            'recipes',
            'favorites'
        ],
        components: {
            AppLayout,
        },

        data() {
            return {
                product : null,
                recipe : null,
                yield : null,
                done: false,
                working: false,
                production : null,
                variant : 'mk1'
            }
        },

        methods : {
            async fetch() {
                if ( this.yield < 1 )
                    return false;

                this.working = true;

                this.$inertia.get(`/dashboard/${this.product.name}/${this.yield}/${this.recipe.description||this.product.name}/${this.variant}`);

                // fetch(`/calc/${this.product.name}/${this.yield}/${this.recipe.description||this.product.name}`)
                //     .then( async response => {
                //         this.production = await response.json();
                //         this.working = false;
                //         this.done = true;
                //     });
            },
            setNewFavorite() {
                this.$inertia.post(`/favorites/${this.recipe.id}`);
            },

            setDefaultRecipe() {
                this.recipes[this.product.name].forEach(recipe => {
                    if (this.isFavorite(recipe)) {
                        this.setRecipe(recipe);
                    }
                })
            },
            isFavorite(recipe) {
                return !! recipe && recipe.hasOwnProperty('favorite') && recipe.favorite;
            },
            setRecipe(recipe) {
                this.recipe = recipe;
                this.yield = 10;
                this.fetch();
            },
            reset() {
                this.production = null;
                this.working = false;
                this.done = false;
                this.yield = null;
                this.recipe = null;
                this.product = null;
            }
        }
    }
</script>
