<template>
    <app-layout>
        <template #header>

            <div class="font-semibold text-xl flex flex-col space-y-4 justify-center">
                <span>{{ factory ? factory.name : 'New Production Line' }}</span>

                <div class="flex space-x-2">
                    <span>
                        <input ref="yield" autofocus="autofocus" @change="fetch" type="number" step="0.5" min="0"
                               v-model="newYield"
                               class="rounded shadow w-24 py-2 px-1 dark:bg-sky-800 appearance-none"> per min
                    </span>
                    <select @change="setDefaultRecipe" class="rounded shadow py-2 dark:bg-sky-800" v-model="newProduct">
                        <option :key="option.id" v-for="option in products" :value="option">{{
                                option.name
                            }}
                        </option>
                    </select>
                    <select @change="fetch" class="rounded shadow py-2 px-1 dark:bg-sky-800" v-model="newRecipe">
                        <option :key="option.id" v-for="option in recipes[newProduct.name]" :value="option">
                            <span v-if="option.favorite">&star;</span>
                            {{ option.description || 'default' }}
                        </option>
                    </select>
                    <select @change="fetch" v-model="newVariant" class="rounded shadow py-2 px-1 dark:bg-sky-800">
                        <option value="mk1">Production mk1 (base)</option>
                        <option value="mk2">Production mk2 (mk++ mod)</option>
                        <option value="mk3">Production mk3 (mk++ mod)</option>
                        <option value="mk4">Production mk4 (mk++ mod)</option>
                    </select>
                    <select @change="fetch" v-model="newBeltSpeed" class="rounded shadow py-2 px-1 dark:bg-sky-800">
                        <option value="60">Belts mk1 (base)</option>
                        <option value="120">Belts mk2 (base)</option>
                        <option value="270">Belts mk3 (base)</option>
                        <option value="480">Belts mk4 (base)</option>
                        <option value="780">Belts mk5 (base)</option>
                        <option value="2000">Belts mk6 (Covered Conveyer Belt Mod)</option>
                        <option value="7500">Belts mk7 (Covered Conveyer Belt Mod)</option>
                    </select>
                </div>

            </div>
            <div class="my-4 space-x-4">
                <button :disabled="working" @click="saveMyFactory"
                        class="btn btn-emerald">
                    {{ factory ? 'Save Changes To Factory' : 'Save To My Factories' }}
                </button>
                <button @click="diagrams = !diagrams;savePrefs()"
                        class="btn btn-emerald">
                    {{ diagrams ? '✅' : '⬜' }}
                    Toggle Diagrams
                </button>
            </div>
            <div class="mt-4 flex flex-col">
                <hr class="mb-4">
                <span class="font-semibold">
                    Recipe:
                    <ul class="flex">
                        <li class="font-medium px-4" v-for="(o,name) in production__productInputs">
                            {{ name }} ({{ o.base_qty }} per min)
                        </li>
                    </ul>
                </span>
                <span class="font-semibold">
                    Byproducts: {{ production__productByproducts }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8 flex">
                <div v-if="done && production"
                     class="dark:text-gray-100 p-4 flex flex-col space-y-2 flex-1">
                    <div class="flex flex-1 space-x-8 py-4">
                        <!-- Left Side -->
                        <div
                            class="w-96 flex flex-col text-sm bg-white dark:bg-slate-900 shadow-lg border border-gray-500 dark:border-sky-700 rounded-lg">
                            <div
                                class="p-4 font-semibold text-xl bg-gray-900 dark:bg-sky-700 text-white rounded-t-lg text-center">
                                Production Summary
                            </div>
                            <table class="">
                                <tr class="bg-sky-300 dark:bg-sky-800">
                                    <th class="font-semibold text-lg" colspan="3">
                                        Raw Materials (per min)
                                    </th>
                                </tr>
                                <tr v-for="material in production__rawMaterials">
                                    <td colspan="2" class="p-2">
                                        <img class="mr-2 inline w-8 h-8" :src="imageUrl(material.name,32)"
                                             :alt="material.name">
                                        {{ material.name }}
                                    </td>
                                    <td class="p-2 text-right">
                                        <div class="flex space-x-2">
                                            <button
                                                @click="disabledRawMaterials[material.name] = !!!disabledRawMaterials[material.name]"
                                                class="btn-sm btn-emerald">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                            <input :disabled="disabledRawMaterials[material.name]"
                                                   @input="rawUnchanged=false"
                                                   class="w-24 text-right text-sm rounded bg-gray-200 dark:bg-sky-200 dark:text-slate-900 p-2 dark:disabled:bg-gray-600 disabled:bg-gray-600 disabled:cursor-not-allowed"
                                                   v-model="rawMaterials[material.name]" :rel="material.name"
                                                   type="text">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <button @click="fetchNewYield" :disabled="rawUnchanged"
                                                class="btn btn-emerald">
                                            Update Yield
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                </tr>

                                <tr class="bg-sky-300 dark:bg-sky-700">
                                    <th class="font-semibold text-lg" colspan="3">
                                        Power Summary
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">Energy Per Finished Product (MJ)</td>
                                    <td class="p-2 text-right">
                                        {{ Math.round(100 * production__total_power / newYield / 60) / 2 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">Total Power Used (MW)</td>
                                    <td class="p-2 text-right">{{ production__total_power }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">Coal Generator Equiv.</td>
                                    <td class="p-2 text-right">{{ Math.ceil(production__total_power / 75) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">Fuel Generator Equiv.</td>
                                    <td class="p-2 text-right">{{ Math.ceil(production__total_power / 150) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">Nuclear Power Plant Equiv.</td>
                                    <td class="p-2 text-right">{{ Math.ceil(production__total_power / 2500) }}</td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                </tr>

                                <tr class="bg-sky-300 dark:bg-sky-700">
                                    <th class="font-semibold text-lg" colspan="3">
                                        Parts List (Buildings)
                                    </th>
                                </tr>
                                <tr @click="buildingChecks[mat] = ! buildingChecks[mat]" class="cursor-pointer"
                                    v-for="(num,mat) in production__building_summary.total_build_cost">
                                    <td colspan="2" class="p-2"><img class="mr-2 inline w-6 h-6" :src="imageUrl(mat,24)"
                                                                     :alt="mat"> <input v-model="buildingChecks[mat]"
                                                                                        type="checkbox"> {{ mat }}
                                    </td>
                                    <td class="p-2 text-right">{{ num }}</td>
                                </tr>
                            </table>
                        </div>
                        <!-- middle -->
                        <div
                            class="flex-1 flex flex-col text-sm bg-white dark:bg-slate-900 shadow-lg rounded-lg border border-gray-500 dark:border-sky-700">
                            <div
                                class="p-4 font-semibold text-xl bg-gray-900 dark:bg-sky-700 text-white rounded-t-lg text-center">
                                Intermediate Products
                            </div>
                            <table>
                                <tr>
                                    <!--                                    <th class="font-semibold">Done</th>-->
                                    <th class="font-semibold">Product</th>
                                    <th class="font-semibold">Inputs</th>
                                    <th class="font-semibold">Recipe</th>
                                    <th class="font-semibold">Production</th>
                                </tr>
                                <tr v-show="Object.values(productionChecks).some(o=>o)">
                                    <th class="bg-blue-200 dark:bg-sky-600 text-center p-2" colspan="100">
                                        {{ hideCompleted ? 'Hiding' : 'Showing' }}
                                        {{ Object.values(productionChecks).filter(o => o).length }} completed rows
                                        <button @click="hideCompleted = ! hideCompleted"
                                                class="text-sm bg-emerald-500 hover:bg-emerald-600 focus:bg-emerald-700 rounded px-4 py-2">
                                            Toggle Completed
                                        </button>
                                    </th>
                                </tr>
                                <template v-for="(level,index) in production__allMaterials">
                                    <tr>
                                        <th class="bg-blue-200 dark:bg-slate-800 py-2" colspan="100">{{ index }}</th>
                                    </tr>


                                    <tbody v-for="material in level"
                                           v-show="!hideCompleted || ! productionChecks[material.name]"
                                           :class="[productionChecks[material.name] ? 'opacity-25' : 'opacity-100']">
                                    <tr class="border-t border-gray-200 dark:border-slate-700">
                                        <!--                                    <td class="text-center">-->
                                        <!--                                        <input v-model="productionChecks[material.name]" class="mr-1" type="checkbox">                                    </td>-->

                                        <td class="p-2">
                                            <div @click="toggleProductionCheck(material.name)"
                                                 class="bg-teal-200 dark:text-slate-800 border border-teal-500 rounded-lg p-2 flex items-center shadow-lg cursor-pointer">
                                                <img
                                                    class="mr-2" :src="imageUrl(material.name,64)"
                                                    :alt="material.name">

                                                <span class="font-semibold">{{ material.name }} <br>
                                                    <span class="font-light">{{ material.qty }} per min</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td nowrap class="p-2">
                                            <div
                                                class="dark:text-slate-800 bg-yellow-200 border border-yellow-500 p-2 rounded-lg shadow-lg">
                                                <div class="flex items-center"
                                                     v-for="(ing,name) in production.recipes[material.name].inputs">
                                                    <img class="mr-2" :src="imageUrl(name,64)" :alt="name">
                                                    <span class="font-semibold">{{ name }} <br>
                                                        <span class="font-light">{{
                                                                Math.round(100 * ing.needed_qty) / 100
                                                            }} per min</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <!-- material is end product -->
                                            <template v-if="material.name === newProduct.name">
                                                <div class="flex flex-col">
                                                    <recipe-picker @select="selectNewRecipe"
                                                                   :recipes="recipes[newProduct.name]"
                                                                   :selected="newRecipe"></recipe-picker>
                                                    <!--                                                    <select @change="fetch" class="rounded shadow w-full text-sm"-->
                                                    <!--                                                            v-model="newRecipe">-->
                                                    <!--                                                        <option :key="option.id"-->
                                                    <!--                                                                v-for="option in recipes[newProduct.name]"-->
                                                    <!--                                                                :value="option">-->
                                                    <!--                                                            <span v-if="option.favorite">&star;</span>-->
                                                    <!--                                                            {{ option.description || 'default' }}-->
                                                    <!--                                                        </option>-->
                                                    <!--                                                    </select>-->

                                                    <!--                                                    <button :disabled="working" @click="setNewFavorite"-->
                                                    <!--                                                            class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 inline rounded-xl mt-2"-->
                                                    <!--                                                            v-if="newRecipe && !newRecipe.favorite">Set Favorite-->
                                                    <!--                                                    </button>-->
                                                </div>
                                            </template>

                                            <!-- everything else -->
                                            <template v-else>
                                                <!--                                                <select :disabled="recipes[material.name].length===1"-->
                                                <!--                                                        @change="setNewSubFavorite(production.recipe_models[material.name])"-->
                                                <!--                                                        class="rounded shadow w-full text-sm"-->
                                                <!--                                                        :class="{'bg-gray-300' : recipes[material.name].length===1, 'dark:bg-black' : recipes[material.name].length===1}"-->
                                                <!--                                                        v-model="production.recipe_models[material.name].id">-->
                                                <!--                                                    <option :key="option.id" v-for="option in recipes[material.name]"-->
                                                <!--                                                            :value="option.id">-->
                                                <!--                                                        <span v-if="option.favorite">&star;</span>-->
                                                <!--                                                        {{ option.description || 'default' }}-->
                                                <!--                                                    </option>-->
                                                <!--                                                </select>-->
                                                <recipe-picker @select="selectNewSubRecipe"
                                                               :recipes="recipes[material.name]"
                                                               :selected="production.recipe_models[material.name]"></recipe-picker>
                                            </template>
                                        </td>

                                        <td class="p-2">
                                            <select v-model="production.recipes[material.name].selected_variant"
                                                    class="rounded shadow py-2 dark:bg-sky-800 w-full text-right">
                                                <option class="text-right" :value="mk"
                                                        v-for="(opt,mk) in production.recipes[material.name].building_details">
                                                    {{ opt.num_buildings }}x {{ mk }} @{{ opt.clock_speed }}%
                                                    [{{ Math.round(opt.power_usage) }} MW]
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr v-show="diagrams">
                                        <td class="text-center" colspan="100">
                                            <div class="flex justify-end space-x-8">

                                                <div class="w-48 text-left">
                                                    <ul>
                                                        <li class="border-b border-gray-300 flex">
                                                            <span class="font-semibold ml-2">Foundations</span>
                                                            <span class="flex-1 text-right">{{
                                                                    getFootprint(material.name).foundations
                                                                }} ({{ getFootprint(material.name).length_foundations }} x {{
                                                                    getFootprint(material.name).width_foundations
                                                                }})</span>
                                                        </li>
                                                        <li class="border-b border-gray-300 flex">
                                                            <span class="font-semibold ml-2">Walls</span>
                                                            <span class="flex-1 text-right">{{
                                                                    getFootprint(material.name).walls
                                                                }} ({{
                                                                    getFootprint(material.name).height_walls
                                                                }} x {{
                                                                    2 * (getFootprint(material.name).length_foundations + getFootprint(material.name).width_foundations)
                                                                }})</span>
                                                        </li>
                                                        <li class="border-b border-gray-300 flex">
                                                            <span class="font-semibold ml-2">Building Rows</span>
                                                            <span class="flex-1 text-right">{{
                                                                    getFootprint(material.name).rows
                                                                }}
                                                                                        </span>
                                                        </li>
                                                        <li class="border-b border-gray-300 flex">
                                                            <span class="font-semibold ml-2">Buildings Per Row</span>
                                                            <span class="flex-1 text-right">{{
                                                                    getFootprint(material.name).buildings_per_row
                                                                }}
                                                                                        </span>
                                                        </li>
                                                        <li class="border-b border-gray-300 flex">
                                                            <span class="font-semibold ml-2">Belt Speed</span>
                                                            <span class="flex-1 text-right">{{
                                                                    getFootprint(material.name).belt_speed
                                                                }}
                                                                                        </span>
                                                        </li>
                                                    </ul>
                                                    <!--                                                <pre>{{ getFootprint(material.name) }}</pre>-->
                                                </div>
                                                <div class="flex justify-center p-2">
                                                    <div style="box-sizing: content-box"
                                                         :style="{ height: (getFootprint(material.name).length_foundations*2) + 'rem', width: (getFootprint(material.name).width_foundations*2) + 'rem'}"
                                                         class="flex relative items-start justify-center bg-blue-300 shadow-lg text-xl">
                                                        <!--                                            <div :key="stat" v-for="(num,stat) in getFootprint(material.name).footprint">{{ stat }} {{ num }}</div>-->
                                                        <!--                                                <div class="text-blue-500">Foundations-->
                                                        <!--                                                    {{ getFootprint(material.name).length_foundations }} x-->
                                                        <!--                                                    {{ getFootprint(material.name).width_foundations }}-->
                                                        <!--                                                </div>-->
                                                        <div style="opacity: 0.3;box-sizing: content-box;"
                                                             class="absolute w-full h-full flex flex-wrap items-center justify-center">
                                                            <template
                                                                v-for="ii in Array(getFootprint(material.name).foundations)">
                                                                <div class="border border-blue-500"
                                                                     style="box-sizing: border-box; height:2rem; width:2rem"
                                                                ></div>
                                                            </template>
                                                        </div>
                                                        <div style="padding:2rem;"
                                                             class="absolute w-full h-full flex flex-wrap items-center justify-center">
                                                            <div class="flex items-center justify-center w-full"
                                                                 v-for="(ii,row) in Array(getFootprint(material.name).rows)">

                                                                <div
                                                                    v-for="(jj,col) in Array(getFootprint(material.name).buildings_per_row)"
                                                                    :style="{height:(getFootprint(material.name).building_length/4) + 'rem',
                                                                        width:(getFootprint(material.name).building_width/4)+'rem'}"
                                                                    :class="((1+col+(row*getFootprint(material.name).buildings_per_row)) <= getFootprint(material.name).num_buildings) ?
                                                                                                    ['border-blue-800','bg-blue-800'] : ['border-transparent','text-transparent','bg-transparent']"
                                                                    style="box-sizing: border-box;"
                                                                    class="border text-xs rounded flex items-center justify-center bg-opacity-25">
                                                                    {{ getFootprint(material.name).monogram }}
                                                                    <!--                                                            {{ 1+col+(row*getFootprint(material.name).buildings_per_row) }}-->
                                                                    <!--                                                            {{ getFootprint(material.name).num_buildings }}-->
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>


                                </template>
                            </table>
                        </div>
                        <!-- right -->
                        <div
                            class="flex-col text-sm bg-white dark:bg-slate-900 shadow-lg rounded-lg border border-gray-500 dark:border-sky-700">
                            <div
                                class="p-4 font-semibold text-xl bg-gray-900 dark:bg-sky-700 text-white rounded-t-lg text-center">
                                Building Summary
                            </div>
                            <table>
                                <tr>
                                    <th>Building</th>
                                    <th>Num</th>
                                    <th>Power Usage (MW)</th>
                                    <th>Build Cost</th>
                                    <!--                                    <th>Footprint</th>-->
                                </tr>
                                <tbody class="border-b border-gray-200 dark:border-slate-800"
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
import RecipePicker from "@/Components/RecipePicker";
import {Inertia} from '@inertiajs/inertia';
import store from '@/store';


export default {
    props: [
        'products',
        'recipes',
        'favorites',
        'production',
        'product',
        'recipe',
        'yield',
        'variant',
        'belt_speed',
        'constraints',
        'factory'
    ],
    components: {
        AppLayout,
        RecipePicker
    },

    mounted() {
        window.Page = this;

        this.$refs.yield.focus();

        this.newYield = this.production.yield;
    },

    data() {
        return {
            done: true,
            working: false,
            newYield: this.yield,
            newProduct: this.product,
            newRecipe: this.recipe,
            newVariant: this.variant,
            // recipe_models: this.production.recipe_models,
            newBeltSpeed: this.belt_speed || 780,
            // production_recipes : this.production.recipes,
            productionChecks: {},
            buildingChecks: {},
            hideCompleted: true,
            rawMaterials: {},
            disabledRawMaterials: {},
            newConstraints: [],
            rawUnchanged: true,
            diagrams: store.getItem('diagrams', true)
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

            ret = ret.sort((a, b) => (a.qty > b.qty) ? 1 : -1);

            ret.forEach(mat => {
                this.rawMaterials[mat.name] = mat.qty
            });

            return ret;
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
                        qty: Math.round(100 * this.production['parts per minute'][prop]) / 100
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

            let parts = [
                'dashboard',
                this.newProduct.name,
                this.newYield,
                this.newRecipe.description || this.newProduct.name,
                this.newVariant
            ]

            this.$inertia.get(`/${parts.join('/')}?belt_speed=${this.newBeltSpeed}&factory=${this.factory ? (this.factory.id || this.factory.name) : ''}`);
        },

        async fetchNewYield() {
            if (this.yield < 1)
                return false;

            this.working = true;

            let parts = [
                'newyield',
                this.newProduct.name,
                this.newYield,
                this.newRecipe.description || this.newProduct.name,
                this.newVariant
            ], raw = [];

            for (let prop in this.rawMaterials) {
                if (!this.disabledRawMaterials[prop])
                    raw.push(`${prop}:${this.rawMaterials[prop]}`);
            }

            this.$inertia.get(`/${parts.join('/')}?belt_speed=${this.newBeltSpeed}&raw=${raw.join(',')}&factory=${this.factory ? (this.factory.id || this.factory.name) : ''}`);
        },

        saveMyFactory() {
            let name;

            if (this.factory) {
                this.$inertia.patch(`/factories/${this.factory.id}`, {
                    name: this.factory.name,
                    ingredient_id: this.product.id,
                    recipe_id: this.recipe.id,
                    yield: this.yield
                });

            } else {
                name = prompt('Provide a name for your factory');
            }

            if (!name)
                return;

            this.$inertia.post('/factories', {
                name,
                ingredient_id: this.product.id,
                recipe_id: this.recipe.id,
                yield: this.yield
            })
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
            //this.newYield = 10;
            this.fetch();
        },
        reset() {
            this.$inertia.get('dashboard');
        },
        getFootprint(name) {
            return this.production.recipes[name].building_details[this.production.recipes[name].selected_variant].footprint;
        },
        imageUrl(name, size = 64) {
            return `https://res.cloudinary.com/codefaber/image/upload/c_scale,q_100,w_${size}/v1642885193/satisfactory/${name.replace(/ /ig, '')}.png`;
        },
        toggleProductionCheck(material) {
            if (this.productionChecks.hasOwnProperty(material))
                this.productionChecks[material] = !this.productionChecks[material];
            else
                this.productionChecks[material] = true;
        },
        selectNewRecipe({recipe}) {
            this.setRecipe(recipe);
        },
        selectNewSubRecipe({recipe}) {
            this.setNewSubFavorite(recipe);
        },
        savePrefs() {
            store.setItem('diagrams', this.diagrams);
        }
    }
}
</script>
