<template>
    <app-layout>
        <template #header>
            <div class="flex flex-col justify-center space-y-4 text-xl font-semibold">
                <span>
                    {{ factory ? factory.name : 'New Production Line' }}
                </span>

                <div class="flex flex-col items-center space-x-0 space-y-2 md:flex-row md:space-x-2 md:space-y-0">
                    <div class="relative flex w-full items-center md:w-48">
                        <input ref="yield" autofocus="autofocus" @change="fetch" type="number" step="0.5" min="0"
                               v-model="newYield" class="w-full appearance-none rounded py-2 px-1 shadow dark:bg-sky-800 md:w-48" />
                        <div class="absolute right-8 bottom-2 pointer-events-none">per min</div>
                    </div>
                    <select @change="setDefaultRecipe" class="w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800" v-model="newProduct">
                        <option :key="option.id" v-for="option in products" :value="option">
                            {{ option.name }}
                        </option>
                    </select>
                    <select @change="fetch" class="w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800" v-model="newRecipe">
                        <option :key="option.id" v-for="option in recipes[newProduct.name]" :value="option">
                            <template v-if="option.favorite">&star;</template>
                            {{ option.description || 'default' }}
                        </option>
                    </select>
                    <select @change="fetch" v-model="newVariant" class="w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800">
                        <option value="mk1">Production mk1 (base)</option>
                        <option value="mk2">Production mk2 (mk++ mod)</option>
                        <option value="mk3">Production mk3 (mk++ mod)</option>
                        <option value="mk4">Production mk4 (mk++ mod)</option>
                    </select>
                    <select @change="fetch" v-model="newBeltSpeed" class="w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800">
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
        </template>
        <!-- end template header -->


        <div class="py-12">
            <div class="mx-auto flex sm:px-6 lg:px-8">
                <div
                    v-if="done && production"
                    class="relative flex flex-1 flex-col space-y-2 p-4 dark:text-gray-100"
                >
                    <div class="flex flex-col md:flex-row space-y-8 space-x-0 md:space-y-0 flex-1 md:space-x-8 py-4">
                        <!-- Left Side -->
                        <div
                            class="flex w-96 flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900"
                        >
                            <div
                                class="rounded-t-lg bg-gray-900 p-4 text-center text-xl font-semibold text-white dark:bg-sky-700"
                            >
                                Production Summary
                            </div>
                            <table class="">
                                <template
                                    v-if="Object.keys(rawMaterials).length"
                                >
                                    <tr class="bg-sky-300 dark:bg-sky-800">
                                        <th
                                            class="text-lg font-semibold"
                                            colspan="3"
                                        >
                                            Raw Materials (per min)
                                        </th>
                                    </tr>
                                    <tr
                                        v-for="(props,name) in rawMaterials"
                                    >
                                        <td colspan="2" class="p-2">
                                            <cloud-image class="inline-flex" :public-id="`${name}.png`" crop="scale" quality="100" width="32" :alt="name" />
                                            {{ name }}
                                        </td>
                                        <td class="p-2 text-right">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="disabledRawMaterials[name] = !!!disabledRawMaterials[name]" class="btn-sm btn-emerald">
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-6 w-6"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                                        />
                                                    </svg>
                                                </button>
                                                <input :disabled="disabledRawMaterials[name]" @input="rawUnchanged = false"
                                                    class="w-24 rounded bg-gray-200 p-2 text-right text-sm disabled:cursor-not-allowed
                                                    disabled:bg-gray-600 dark:bg-sky-200 dark:text-slate-900 dark:disabled:bg-gray-600"
                                                    v-model="rawMaterials[name]"
                                                    :rel="name"
                                                    type="text"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <button
                                                @click="helpRawMaterials"
                                                class="btn btn-gray mb-4 mr-2"
                                            >
                                                Help
                                            </button>
                                            <button
                                                @click="fetchNewYield"
                                                :disabled="rawUnchanged"
                                                class="btn btn-emerald mb-4"
                                            >
                                                Update Yield
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <template
                                    v-if="
                                        Object.keys(production.intermediate_materials).length
                                    "
                                >
                                    <tr class="bg-sky-300 dark:bg-sky-800">
                                        <th
                                            class="text-lg font-semibold"
                                            colspan="3"
                                        >
                                            Intermediate Products
                                        </th>
                                    </tr>
                                    <tr
                                        v-for="(qty,name) in production.intermediate_materials"
                                    >
                                        <td colspan="2" class="p-2">
                                            <div class="flex">
                                                <cloud-image
                                                    class="mr-2 inline-flex"
                                                    :public-id="`${name}.png`"
                                                    crop="scale"
                                                    quality="100"
                                                    width="32"
                                                    :alt="name"
                                                />
                                                <div>
                                                    <span>{{ name }}</span>
                                                    <br />
                                                    <span class="italic">{{ qty }} per min</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2 text-right">
                                            <div class="flex space-x-2">
                                                <label
                                                    :for="`importToggle${name.replace(/ /gi,'')}`"
                                                    class="flex cursor-pointer items-center"
                                                >
                                                    <!-- Produce -->
                                                    <div class="mr-2 font-medium">
                                                        Produce
                                                    </div>
                                                    <!-- toggle -->
                                                    <div class="relative">
                                                        <!-- input -->
                                                        <input
                                                            :id="`importToggle${name.replace(/ /gi,'')}`"
                                                            v-model="newImports[name]"
                                                            type="checkbox"
                                                            class="sr-only"
                                                        />
                                                        <!-- line -->
                                                        <div
                                                            class="block h-8 w-14 rounded-full bg-gray-600 dark:bg-gray-200"
                                                        ></div>
                                                        <!-- dot -->
                                                        <div
                                                            class="dot absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition dark:bg-gray-800"
                                                        ></div>
                                                    </div>
                                                    <!-- import -->
                                                    <div
                                                        class="ml-2 font-medium"
                                                    >
                                                        Import
                                                    </div>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <button
                                                @click="helpImport"
                                                class="btn btn-gray mr-2"
                                            >
                                                Help
                                            </button>
                                            <button
                                                @click="fetch"
                                                class="btn btn-emerald"
                                            >
                                                Recalculate
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr>
                                    <td>&nbsp;</td>
                                </tr>

<!--                                <tr class="bg-sky-300 dark:bg-sky-700">-->
<!--                                    <th-->
<!--                                        class="text-lg font-semibold"-->
<!--                                        colspan="3"-->
<!--                                    >-->
<!--                                        Power Summary-->
<!--                                    </th>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td-->
<!--                                        colspan="2"-->
<!--                                        class="whitespace-nowrap p-2"-->
<!--                                    >-->
<!--                                        Energy Per Product (MJ)-->
<!--                                    </td>-->
<!--                                    <td class="p-2 text-right">-->
<!--                                        {{ Math.round((100 * production__total_power) / newYield / 60) / 2 }}-->
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td colspan="2" class="p-2">-->
<!--                                        Total Power Used (MW)-->
<!--                                    </td>-->
<!--                                    <td class="p-2 text-right">-->
<!--                                        {{ production__total_power }}-->
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td colspan="2" class="p-2">-->
<!--                                        Coal Generator Equiv.-->
<!--                                    </td>-->
<!--                                    <td class="p-2 text-right">-->
<!--                                        {{ Math.ceil(production__total_power / 75) }}-->
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td colspan="2" class="p-2">-->
<!--                                        Fuel Generator Equiv.-->
<!--                                    </td>-->
<!--                                    <td class="p-2 text-right">-->
<!--                                        {{ Math.ceil( production__total_power / 150 ) }}-->
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td colspan="2" class="p-2">-->
<!--                                        Nuclear Power Plant Equiv.-->
<!--                                    </td>-->
<!--                                    <td class="p-2 text-right">-->
<!--                                        {{ Math.ceil( production__total_power / 2500 ) }}-->
<!--                                    </td>-->
<!--                                </tr>-->

<!--                                <tr>-->
<!--                                    <td>&nbsp;</td>-->
<!--                                </tr>-->

<!--                                <tr class="bg-sky-300 dark:bg-sky-700">-->
<!--                                    <th-->
<!--                                        class="text-lg font-semibold"-->
<!--                                        colspan="3"-->
<!--                                    >-->
<!--                                        Parts List (Buildings)-->
<!--                                    </th>-->
<!--                                </tr>-->
<!--                                <tr-->
<!--                                    @click="-->
<!--                                        buildingChecks[mat] = !buildingChecks[mat]-->
<!--                                    "-->
<!--                                    class="cursor-pointer"-->
<!--                                    v-for="( num, mat ) in production__building_summary.total_build_cost"-->
<!--                                >-->
<!--                                    <td-->
<!--                                        colspan="2"-->
<!--                                        class="whitespace-nowrap p-2"-->
<!--                                    >-->
<!--                                        <cloud-image-->
<!--                                            class="mr-2 inline-flex"-->
<!--                                            :public-id="mat"-->
<!--                                            crop="scale"-->
<!--                                            quality="100"-->
<!--                                            width="24"-->
<!--                                            :alt="mat"-->
<!--                                        />-->
<!--                                        <input-->
<!--                                            v-model="buildingChecks[mat]"-->
<!--                                            type="checkbox"-->
<!--                                        />-->
<!--                                        {{ mat }}-->
<!--                                    </td>-->
<!--                                    <td class="p-2 text-right">{{ num }}</td>-->
<!--                                </tr>-->
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import RecipePicker from '@/Components/RecipePicker';
import store from '@/store';
import ProductionWarning from '@/Pages/Production/ProductionWarning';

export default {
    name: 'ShowNew',

    components: {
        ProductionWarning,
        AppLayout,
        RecipePicker,
    },

    props: ['products', 'recipes', 'favorites', 'production', 'product', 'recipe', 'yield', 'variant', 'belt_speed', 'constraints', 'factory', 'imports'],

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
            rawMaterials: this.production.raw_materials,
            intermediateMaterials: {},
            disabledRawMaterials: {},
            newConstraints: [],
            rawUnchanged: true,
            diagrams: store.getItem('diagrams', true),
            newImports: this.imports,
            showWarnings: true,
        };
    },

    methods: {
        async fetch() {
            if (this.yield < 1) return false;

            this.working = true;

            let parts = ['dashboard', this.newProduct.name, this.newYield, this.newRecipe.description || this.newProduct.name, this.newVariant],
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(',');

            this.$inertia.get(`/${parts.join('/')}?belt_speed=${this.newBeltSpeed}&factory=${this.factory ? this.factory.id : ''}&imports=${imports}`);
        },

        async fetchNewYield() {
            if (this.yield < 1) return false;

            this.working = true;

            let parts = ['newyield', this.newProduct.name, this.newYield, this.newRecipe.description || this.newProduct.name, this.newVariant],
                raw = [],
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(',');

            for (let prop in this.rawMaterials) {
                if (!this.disabledRawMaterials[prop]) raw.push(`${prop}:${this.rawMaterials[prop]}`);
            }

            if (!raw.length) return false;

            this.$inertia.get(`/${parts.join('/')}?belt_speed=${this.newBeltSpeed}&raw=${raw.join(',')}&factory=${this.factory ? this.factory.id : ''}&imports=${imports}`);
        },

        saveMyFactory() {
            let name,
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(',');

            if (this.factory) {
                this.$inertia.patch(`/factories/${this.factory.id}`, {
                    name: this.factory.name,
                    ingredient_id: this.product.id,
                    recipe_id: this.recipe.id,
                    yield: this.yield,
                    imports,
                });
            } else {
                name = prompt('Provide a name for your factory');
            }

            if (!name) return;

            this.$inertia.post('/factories', {
                name,
                ingredient_id: this.product.id,
                recipe_id: this.recipe.id,
                yield: this.yield,
                imports,
            });
        },

        setNewSubFavorite(recipe) {
            if (recipe.product_id === this.recipe.product_id) this.$inertia.post(`/favorites/${recipe.id}`);
            else {
                this.$inertia.post(`/favorites/sub/${recipe.id}`);
                setTimeout(this.$forceUpdate, 1200);
            }
        },

        setNewFavorite() {
            this.$inertia.post(`/favorites/${this.recipe.id}`);
        },

        setDefaultRecipe() {
            this.recipes[this.newProduct.name].forEach((recipe) => {
                if (this.isFavorite(recipe)) {
                    this.setRecipe(recipe);
                }
            });
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

        toggleProductionCheck(material) {
            if (this.productionChecks.hasOwnProperty(material)) this.productionChecks[material] = !this.productionChecks[material];
            else this.productionChecks[material] = true;
        },

        selectNewRecipe({ recipe }) {
            this.setRecipe(recipe);
        },

        selectNewSubRecipe({ recipe }) {
            this.setNewSubFavorite(recipe);
        },

        savePrefs() {
            store.setItem('diagrams', this.diagrams);
        },

        getOutputs(name) {
            let ret = {};

            Object.keys(this.production.recipes)
                .filter((product) => {
                    return !!this.production.recipes[product];
                })
                .filter((product) => {
                    return this.production.recipes[product].inputs[name]?.needed_qty > 0;
                })
                .forEach((product) => {
                    ret[product] = Math.round(10000 * this.production.recipes[product].inputs[name].needed_qty) / 10000;
                });

            return ret;
        },

        helpRawMaterials() {
            alert('Constrained by something? Enter your actual available raw materials then click Update Yield. Click the green button next to the input to ignore that material for the recalculation.');
        },

        helpImport() {
            alert('Choose whether to produce each intermediate product in this factory (default) or to import select products from elsewhere.');
        },
    },
};
</script>

<style scoped></style>
