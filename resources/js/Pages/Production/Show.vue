<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                New Production Line : <span v-if="product">{{ product.name }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8 flex space-x-10">
                <!-- left column -->
                <form :class="[done ? 'w-96' : 'w-full']" @submit.prevent="fetch">
                    <div
                        class="bg-white dark:bg-gray-900 dark:text-gray-100 shadow-xl sm:rounded-lg p-4 flex flex-col space-y-8 items-center transition-all">
                        <div class="bg-white dark:bg-gray-800 dark:text-gray-100 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Select a product</span>
                            <select @change="setDefaultRecipe" class="rounded shadow" v-model="newProduct"
                                    name="product" id="product">
                                <option :key="option.id" v-for="option in products" :value="option">{{
                                        option.name
                                    }}
                                </option>
                            </select>
                        </div>
                        <div v-if="newProduct" class="bg-white dark:bg-gray-800 dark:text-gray-100 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Select a recipe</span>
                            <select @change="fetch" class="rounded shadow" v-model="newRecipe" name="recipe"
                                    id="recipe">
                                <option :key="option.id" v-for="option in recipes[newProduct.name]" :value="option">
                                    <span v-if="option.favorite">&star;</span>
                                    {{ option.description || 'default' }}
                                </option>
                            </select>
                            <button :disabled="working" @click="setNewFavorite"
                                    class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 inline rounded-xl mt-2"
                                    v-if="newRecipe && !newRecipe.favorite">Set Favorite
                            </button>
                        </div>
                        <div v-if="newRecipe" class="bg-white dark:bg-gray-800 dark:text-gray-100 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Enter quantity per minute</span>
                            <input autofocus @input="fetch" type="number" step="0.5" min="0" v-model="newYield"
                                   class="rounded shadow">
                        </div>
                        <div v-if="newYield" class="bg-white dark:bg-gray-800 dark:text-gray-100 w-full shadow-xl sm:rounded-lg p-4 flex flex-col">
                            <span class="font-semibold">Select default building variant</span>
                            <select @change="fetch" v-model="newVariant" class="rounded shadow">
                                <option>mk1</option>
                                <option>mk2</option>
                                <option>mk3</option>
                                <option>mk4</option>
                            </select>
                        </div>

                        <div class="space-x-4">
                            <button :disabled="working" @click="fetch"
                                    class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 inline rounded-xl"
                                    v-if="newYield">Go
                            </button>
                            <button :disabled="working" v-if="newProduct" @click="reset"
                                    class="px-4 py-2 text-white bg-gray-500 hover:bg-gray-600 inline rounded-xl">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!--                                        <pre class="bg-white dark:bg-gray-800 dark:text-gray-100" v-if="done && production">-->
                    <!--                                            {{ production }}-->
                    <!--                                        </pre>-->
                </form>
                <!-- right column -->
                <div v-if="done && production"
                     class="bg-white dark:bg-gray-800 dark:text-gray-100 shadow-xl sm:rounded-lg p-4 flex flex-col space-y-2 flex-1">
                    <span class="font-semibold text-xl mb-4">
                        Production Line: [{{ production__productYield }} per min] {{ production__productName }} - <span
                        class="italic">{{ production__productRecipe }}</span>
                    </span>
                    <span class="font-semibold">
                        Recipe: {{ production__productInputs }}
                    </span>
                    <span class="font-semibold">
                        Byproducts: {{ production__productByproducts }}
                    </span>
                    <hr>
                    <div class="flex flex-1 space-x-8 py-4">
                        <!-- Left Side -->
                        <div class="w-96 flex flex-col text-sm dark:bg-gray-900 rounded-lg">
                            <table class="">
                                <tr>
                                    <th class="font-semibold text-xl" colspan="2">
                                        Production Summary
                                    </th>
                                </tr>
                                <tr class="bg-blue-300 dark:bg-blue-900">
                                    <th class="font-semibold text-lg" colspan="2">
                                        Raw Materials (per min)
                                    </th>
                                </tr>
                                <tr v-for="material in production__rawMaterials">
                                    <td class="p-2" v-text="material.name"></td>
                                    <td class="p-2 text-right" v-text="material.qty"></td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                </tr>

                                <tr class="bg-blue-300 dark:bg-blue-900">
                                    <th class="font-semibold text-lg" colspan="2">
                                        Power Summary
                                    </th>
                                </tr>
                                <tr>
                                    <td class="p-2">Energy Per Finished Product (MJ)</td>
                                    <td class="p-2 text-right">
                                        {{ Math.round(100 * production__total_power / newYield / 60) / 2 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="p-2">Total Power Used (MW)</td>
                                    <td class="p-2 text-right">{{ production__total_power }}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Coal Generator Equiv.</td>
                                    <td class="p-2 text-right">{{ Math.ceil(production__total_power / 75) }}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Fuel Generator Equiv.</td>
                                    <td class="p-2 text-right">{{ Math.ceil(production__total_power / 150) }}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Nuclear Power Plant Equiv.</td>
                                    <td class="p-2 text-right">{{ Math.ceil(production__total_power / 2500) }}</td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                </tr>

                                <tr class="bg-blue-300 dark:bg-blue-900">
                                    <th class="font-semibold text-lg" colspan="2">
                                        Parts List (Buildings)
                                    </th>
                                </tr>
                                <tr v-for="(num,mat) in production__building_summary.total_build_cost">
                                    <td class="p-2"><input type="checkbox"> {{ mat }}</td>
                                    <td class="p-2 text-right">{{ num }}</td>
                                </tr>
                            </table>
                        </div>
                        <!-- middle Side -->
                        <div class="flex-1 flex flex-col text-sm dark:bg-gray-900 rounded-lg p-2">
                            <table>
                                <tr>
                                    <th class="font-semibold text-xl" colspan="100">
                                        Production Sub-Lines
                                    </th>
                                </tr>
                                <tr>
                                    <th class="font-semibold">Product</th>
                                    <th class="font-semibold">Qty Per Min</th>
                                    <th class="font-semibold">Recipe</th>
                                    <th class="font-semibold">Inputs</th>
                                    <th class="font-semibold">Production</th>
                                </tr>
                                <tbody v-for="(level,index) in production__allMaterials">
                                <tr>
                                    <th class="bg-blue-200 dark:bg-gray-500" colspan="100">{{ index }}</th>
                                </tr>
                                <tr class="dark:odd:bg-gray-700 odd:bg-gray-100" v-for="material in level">
                                    <td class="p-2"><input type="checkbox"> {{ material.name }}</td>
                                    <td class="p-2" v-text="material.qty"></td>
                                    <td class="p-2">
                                        <!-- material is end product -->
                                        <template v-if="material.name === newProduct.name">
                                            <div class="flex flex-col">
                                                <select @change="fetch" class="rounded shadow w-full text-sm"
                                                        v-model="newRecipe">
                                                    <option :key="option.id" v-for="option in recipes[newProduct.name]"
                                                            :value="option">
                                                        <span v-if="option.favorite">&star;</span>
                                                        {{ option.description || 'default' }}
                                                    </option>
                                                </select>

                                                <button :disabled="working" @click="setNewFavorite"
                                                        class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 inline rounded-xl mt-2"
                                                        v-if="newRecipe && !newRecipe.favorite">Set Favorite
                                                </button>
                                            </div>
                                        </template>

                                        <!-- everything else -->
                                        <template v-else>
                                            <select :disabled="recipes[material.name].length===1"
                                                    @change="setNewSubFavorite(recipe_models[material.name])"
                                                    class="rounded shadow w-full text-sm"
                                                    :class="{'bg-gray-300' : recipes[material.name].length===1, 'dark:bg-black' : recipes[material.name].length===1}"
                                                    v-model="recipe_models[material.name].id">
                                                <option :key="option.id" v-for="option in recipes[material.name]"
                                                        :value="option.id">
                                                    <span v-if="option.favorite">&star;</span>
                                                    {{ option.description || 'default' }}
                                                </option>
                                            </select>

                                        </template>
                                    </td>
                                    <td nowrap class="p-2">
                                        <!--                                        {{ production.recipes[material.name].inputs.split(', ')}}-->
                                        <div v-for="ing in production.recipes[material.name].inputs.split(', ')"
                                             v-text="ing"></div>
                                    </td>
                                    <td class="p-2">
                                        <select v-model="production.recipes[material.name].selected_variant"
                                                class="text-sm rounded shadow w-full">
                                            <option :value="mk"
                                                    v-for="(opt,mk) in production.recipes[material.name].building_details">
                                                {{ opt.num_buildings }}x {{ mk }} @{{ opt.clock_speed }}%
                                                [{{ Math.round(opt.power_usage) }} MW]
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="w-96 flex-col text-sm dark:bg-gray-900 rounded-lg">
                            <table>
                                <tr>
                                    <th class="font-semibold text-xl" colspan="100">
                                        Building Detail
                                    </th>
                                </tr>
                                <tr>
                                    <th>Building</th>
                                    <th>Num. Required</th>
                                    <th>Power Usage (MW)</th>
                                    <th>Build Cost</th>
                                </tr>
                                <tbody class="dark:odd:bg-gray-700 odd:bg-gray-100"
                                       v-for="(o,bldg) in production__building_summary.variants">
                                <tr>
                                    <td class="p-2">{{ bldg }}</td>
                                    <td class="p-2">{{ o.num_buildings }}</td>
                                    <td class="p-2 text-right">{{ Math.round(o.power_usage) }}</td>
                                    <td nowrap="" class="p-2 text-right">
                                        <div class="flex flex-col">
                                            <div :key="mat" v-for="(num,mat) in o.build_cost">{{ mat }} {{ num }}</div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                                <tbody class="font-bold bg-blue-200 dark:bg-gray-900 rounded-b-lg">
                                <tr>
                                    <td class="p-2">Total</td>
                                    <td class="p-2">{{ production__total_buildings }}</td>
                                    <td class="p-2 text-right">{{ production__total_power }}</td>
                                    <td nowrap="" class="p-2 text-right">
                                        <div class="flex flex-col">
                                            <div :key="mat"
                                                 v-for="(num,mat) in production__building_summary.total_build_cost">
                                                {{ mat }} {{ num }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
<!--            <div v-if="done && production" class="w-3/4 mx-auto sm:px-6 lg:px-8 flex space-x-10 mt-4">-->
<!--                <div class="w-1/5"></div>-->
<!--                <div class="bg-white dark:bg-gray-800 dark:text-gray-100 shadow-xl sm:rounded-lg p-4 flex space-x-4 flex-1">-->
<!--                    <div class="w-3/5 flex flex-col text-sm">-->

<!--                    </div>-->
<!--                    <div class="flex flex-col flex-1 text-sm">-->

<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import {Inertia} from '@inertiajs/inertia';


export default {
    props: [
        'products',
        'recipes',
        'favorites',
        'production',
        'product',
        'recipe',
        'yield',
        'variant'
    ],
    components: {
        AppLayout,
    },

    mounted() {
        window.Page = this;
    },

    data() {
        return {
            done: true,
            working: false,
            newYield: this.yield,
            newProduct: this.product,
            newRecipe: this.recipe,
            newVariant: this.variant,
            recipe_models: this.production.recipe_models,
            // production_recipes : this.production.recipes
        }
    },

    computed: {
        production__rawMaterials() {
            if (!this.production)
                return [];

            let ret = [];

            for (let prop in this.production['raw materials']) {
                if (this.production['raw materials'].hasOwnProperty(prop))
                    ret.push({name: prop.replace('1 - ', ''), qty: Math.round(this.production['raw materials'][prop])});
            }

            return ret.sort((a, b) => (a.qty > b.qty) ? 1 : -1);
        },

        production__allMaterials() {
            if (!this.production)
                return [];

            let ret = {};

            for (let prop in this.production['parts per minute']) {
                if (this.production['parts per minute'].hasOwnProperty(prop) && prop.charAt(0) > 1) {
                    let level = 'Level ' + (+prop.charAt(0) - 1).toString();
                    if (ret[level] == null)
                        ret[level] = [];

                    ret[level].push({
                        name: prop.replace(/\d - /, ''),
                        qty: Math.round(this.production['parts per minute'][prop])
                    })
                }

            }

            return ret;
        },

        production__productName() {
            if (!this.production)
                return false;
            return this.production.product;
        },

        production__productYield() {
            if (!this.production)
                return false;
            return this.production.yield;
        },

        production__productRecipe() {
            if (!this.production)
                return false;
            return this.production.recipe;
        },

        production__productInputs() {
            if (!this.production)
                return false;

            return this.production.recipes[this.production__productName].inputs;
        },

        production__productByproducts() {
            if (!this.production)
                return false;

            let b = this.production['byproducts per minute'], r = [];

            if (b.hasOwnProperty('length') && !b.length)
                return "n/a";

            for (let prop in b) {
                if (b.hasOwnProperty(prop))
                    r.push(`${prop} - ${b[prop]} per min`)
            }

            return r.join(', ');
        },

        production__building_details() {
            let ret = [];

            for (let prop in this.production.recipes) {
                if (this.production.recipes.hasOwnProperty(prop))
                    ret.push(this.production.recipes[prop]);
            }

            return ret
                .map(o => Object.assign(o.building_details[o.selected_variant], {variant: o.selected_variant}));
        },

        production__total_power() {
            return Math.round(this.production__building_details.map(o => +o.power_usage).reduce((a, b) => a + b, 0));
        },

        production__total_buildings() {
            return Math.round(this.production__building_details.map(o => +o.num_buildings).reduce((a, b) => a + b, 0));
        },

        production__building_summary() {
            let ret = {};
            ret.total_build_cost = {};
            ret.variants = {}
            this.production__building_details
                .forEach(o => {
                    if (!ret.variants.hasOwnProperty(o.variant))
                        ret.variants[o.variant] = Object.assign({}, o);
                    else {
                        ret.variants[o.variant].num_buildings += o.num_buildings;
                        ret.variants[o.variant].power_usage += o.power_usage;
                    }

                    for (let prop in o.build_cost) {
                        // increment the build cost for the particular building
                        if (ret.variants[o.variant].build_cost.hasOwnProperty(prop))
                            ret.variants[o.variant].build_cost[prop] += o.build_cost[prop];
                        else
                            ret.variants[o.variant].build_cost[prop] = o.build_cost[prop];

                        // increment the total build cost
                        if (ret.total_build_cost.hasOwnProperty(prop))
                            ret.total_build_cost[prop] += o.build_cost[prop];
                        else
                            ret.total_build_cost[prop] = o.build_cost[prop];
                    }
                });

            return ret;
        }
    },

    methods: {
        async fetch() {
            if (this.yield < 1)
                return false;

            this.working = true;

            this.$inertia.get(`/dashboard/${this.newProduct.name}/${this.newYield}/${this.newRecipe.description || this.newProduct.name}/${this.newVariant}`);

            // fetch(`/calc/${this.product.name}/${this.yield}/${this.recipe.description||this.product.name}`)
            //     .then( async response => {
            //         this.production = await response.json();
            //         this.working = false;
            //         this.done = true;
            //     });
        },
        setNewSubFavorite(recipe) {
            if (recipe.product_id === this.recipe.product_id)
                this.$inertia.post(`/favorites/${recipe.id}`);
            else {
                this.$inertia.post(`/favorites/sub/${recipe.id}`);
                setTimeout(this.$forceUpdate, 1200);
            }
        },
        setNewFavorite() {
            this.$inertia.post(`/favorites/${this.recipe.id}`);
        },
        setDefaultRecipe() {
            this.recipes[this.newProduct.name].forEach(recipe => {
                if (this.isFavorite(recipe)) {
                    this.setRecipe(recipe);
                }
            })
        },
        isFavorite(recipe) {
            return !!recipe.favorite;
        },
        setRecipe(recipe) {
            this.newRecipe = recipe;
            this.newYield = 10;
            this.fetch();
        },
        reset() {
            this.$inertia.get('dashboard');
        }
    }
}
</script>
