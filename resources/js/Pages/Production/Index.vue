<template>
    <app-layout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                New Production Line
                <span v-if="product">: {{ product.name }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto flex max-w-7xl space-x-10 sm:px-6 lg:px-8">
                <!-- left column -->
                <form :class="[done ? 'w-1/3' : 'w-full']" @submit.prevent="fetch">
                    <div
                        class="flex flex-col items-center space-y-8 bg-white p-4 shadow-xl transition-all dark:bg-slate-700 sm:rounded-lg"
                    >
                        <!-- product -->
                        <div
                            class="flex w-full flex-col space-y-2 bg-white p-4 shadow-xl dark:bg-gray-800 sm:rounded-lg"
                        >
                            <span class="font-semibold">Select a product</span>
                            <product-picker @select="selectProduct" :selected="product" :products="products" />
                        </div>
                        <div
                            v-if="product && product.name.length && recipe"
                            class="flex w-full flex-col space-y-2 bg-white p-4 shadow-xl dark:bg-gray-800 sm:rounded-lg"
                        >
                            <span class="font-semibold">Select a recipe</span>
                            <recipe-picker
                                class="border-sky-300 dark:border-slate-500"
                                :recipes="recipes[product.name]"
                                :selected="recipe"
                                @select="selectRecipe"
                            />
                            <!--                            <select-->
                            <!--                                ref="recipe"-->
                            <!--                                class="rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800 dark:text-gray-100"-->
                            <!--                                v-model="recipe"-->
                            <!--                                name="recipe"-->
                            <!--                                id="recipe"-->
                            <!--                            >-->
                            <!--                                <option-->
                            <!--                                    :data-recipe-id="option.id"-->
                            <!--                                    :key="option.id"-->
                            <!--                                    v-for="option in recipes[product.name]"-->
                            <!--                                    :value="option"-->
                            <!--                                >-->
                            <!--                                    <span v-if="option.favorite">&star;</span>-->
                            <!--                                    {{ option.description || 'default' }}-->
                            <!--                                </option>-->
                            <!--                            </select>-->
                            <!--                            <button-->
                            <!--                                :disabled="working"-->
                            <!--                                @click="setNewFavorite"-->
                            <!--                                class="btn btn-emerald"-->
                            <!--                                v-if="-->
                            <!--                                    recipe &&-->
                            <!--                                    !recipe.favorite &&-->
                            <!--                                    recipes[product.name].length > 1-->
                            <!--                                "-->
                            <!--                            >-->
                            <!--                                Set Favorite-->
                            <!--                            </button>-->
                        </div>
                        <div
                            v-if="recipe && recipe.base_per_min"
                            class="flex w-full flex-col space-y-2 bg-white p-4 shadow-xl dark:bg-gray-800 sm:rounded-lg"
                        >
                            <span class="font-semibold">Enter quantity per minute</span>
                            <input
                                type="number"
                                step="0.5"
                                min="0"
                                v-model="yield"
                                class="rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800"
                            />
                        </div>
                        <div
                            v-if="yield"
                            class="flex w-full flex-col space-y-2 bg-white p-4 shadow-xl dark:bg-gray-800 sm:rounded-lg"
                        >
                            <span class="font-semibold">Select default building variant</span>
                            <select
                                v-model="variant"
                                class="rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800"
                            >
                                <option>mk1</option>
                                <option value="mk2">mk2 (mk++ mod)</option>
                                <option value="mk3">mk3 (mk++ mod)</option>
                                <option value="mk4">mk4 (mk++ mod)</option>
                            </select>
                        </div>

                        <div class="space-x-4">
                            <button :disabled="working" @click="fetch" class="btn btn-emerald" v-if="yield">Go</button>
                            <button :disabled="working" v-if="product" @click="reset" class="btn btn-gray">
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
import AppLayout from '@/Layouts/AppLayout';
import { Inertia } from '@inertiajs/inertia';
import ProductPicker from '@/Components/ProductPicker';
import RecipePicker from '@/Components/RecipePicker';

export default {
    props: ['products', 'recipes', 'favorites'],
    components: {
        AppLayout,
        ProductPicker,
        RecipePicker,
    },

    data() {
        return {
            product: { name: '' },
            recipe: { product: { name: '' } },
            yield: null,
            done: false,
            working: false,
            production: null,
            variant: 'mk1',
        };
    },

    methods: {
        async fetch() {
            if (this.yield < 1) return false;

            this.working = true;

            this.$inertia.get(
                `/dashboard/${this.product.name}/${this.yield}/${this.recipe.description || this.product.name}/${
                    this.variant
                }`
            );

            // fetch(`/calc/${this.product.name}/${this.yield}/${this.recipe.description||this.product.name}`)
            //     .then( async response => {
            //         this.production = await response.json();
            //         this.working = false;
            //         this.done = true;
            //     });
        },

        saveMyFactory() {
            const name = prompt('Provide a name for your factory');

            if (!name) return;

            this.$inertia.post('/factories', {
                name,
                ingredient_id: this.product.id,
                recipe_id: this.recipe.id,
                yield: this.yield,
            });
        },

        selectProduct({ product }) {
            this.reset();

            if (!product || !product.name) {
                console.log('invalid product, using dummy');
                this.product = { name: '' };
                return;
            }

            this.product = product;
            this.setDefaultRecipe();
        },

        selectRecipe({ recipe }) {
            this.setRecipe(recipe);
        },

        setNewFavorite() {
            const recipe = this.recipe;
            this.$inertia.post(`/favorites/${this.recipe.id}`, {
                preserveState: true,
                preserveScroll: true,
            });
        },

        setDefaultRecipe() {
            // if there is only one recipe, use it
            if (this.recipes[this.product.name].length === 1) {
                this.setRecipe(this.recipes[this.product.name][0]);
                return;
            }

            // if there is a favorite recipe, use that
            if (this.recipes[this.product.name].some((o) => this.isFavorite(o))) {
                this.setRecipe(this.recipes[this.product.name].filter((o) => this.isFavorite(o))[0]);
                return;
            }

            // if there is a base recipe, use that
            if (this.recipes[this.product.name].some((o) => !o.alt_recipe)) {
                this.setRecipe(this.recipes[this.product.name].filter((o) => !o.alt_recipe)[0]);
                return;
            }

            // else, use the first recipe available
            this.setRecipe(this.recipes[this.product.name][0]);
        },

        isFavorite(recipe) {
            return !!recipe && recipe.hasOwnProperty('favorite') && recipe.favorite;
        },

        setRecipe(recipe) {
            this.recipe = recipe;
            this.yield = 100;
            this.$forceUpdate();
        },
        reset() {
            this.production = null;
            this.working = false;
            this.done = false;
            this.yield = null;
            this.recipe = null;
            this.product = { name: '' };
        },
    },
};
</script>
