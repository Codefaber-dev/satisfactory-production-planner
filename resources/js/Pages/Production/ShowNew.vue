<template>
    <app-layout>
        <template #header>
            <div class='flex flex-col justify-center space-y-4 text-xl font-semibold'>
                <span>
                    {{ factory ? factory.name : 'New Production Line' }}
                </span>

                <div class='flex flex-col items-center space-x-0 space-y-2 md:flex-row md:space-x-2 md:space-y-0'>
                    <div class='relative flex w-full items-center md:w-48'>
                        <input ref='yield' autofocus='autofocus' @change='fetch' type='number' step='0.5' min='0'
                               v-model='newYield'
                               class='w-full appearance-none rounded py-2 px-1 shadow dark:bg-sky-800 md:w-48' />
                        <div class='absolute right-8 bottom-2 pointer-events-none'>per min</div>
                    </div>
                    <select @change='setDefaultRecipe'
                            class='w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800' v-model='newProduct'>
                        <option :key='option.id' v-for='option in products' :value='option'>
                            {{ option.name }}
                        </option>
                    </select>
                    <select @change='fetch' class='w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800'
                            v-model='newRecipe'>
                        <option :key='option.id' v-for='option in recipes[newProduct.name]' :value='option'>
                            <template v-if='option.favorite'>&star;</template>
                            {{ option.description || 'default' }}
                        </option>
                    </select>
                    <select @change='fetch' v-model='newVariant'
                            class='w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800'>
                        <option value='mk1'>Production mk1 (base)</option>
                        <option value='mk2'>Production mk2 (mk++ mod)</option>
                        <option value='mk3'>Production mk3 (mk++ mod)</option>
                        <option value='mk4'>Production mk4 (mk++ mod)</option>
                    </select>
                    <select @change='fetch' v-model='newBeltSpeed'
                            class='w-full md:w-[unset] rounded py-2 px-1 shadow dark:bg-sky-800'>
                        <option value='60'>Belts mk1 (base)</option>
                        <option value='120'>Belts mk2 (base)</option>
                        <option value='270'>Belts mk3 (base)</option>
                        <option value='480'>Belts mk4 (base)</option>
                        <option value='780'>Belts mk5 (base)</option>
                        <option value='2000'>Belts mk6 (Covered Conveyer Belt Mod)</option>
                        <option value='7500'>Belts mk7 (Covered Conveyer Belt Mod)</option>
                    </select>
                </div>

                <div class="my-4 space-x-4">
                <button
                    :disabled="working"
                    @click="saveMyFactory"
                    class="btn btn-emerald"
                >
                    {{ factory ? "Save Changes To Factory" : "Save To My Factories" }}
                </button>
                <button
                    @click="diagrams = !diagrams; savePrefs();"
                    class="btn btn-emerald"
                >
                    {{ diagrams ? "✅" : "⬜" }}
                    Toggle Diagrams
                </button>
            </div>
            <div class="mt-4 flex flex-col">
                <hr class="mb-4" />
                <span class="font-semibold">
                    Recipe:
                    <ul class="flex">
                        <li
                            class="px-4 font-medium"
                            v-for="ing in production.final.recipe.ingredients"
                        >
                            {{ ing.name }} ({{ +ing.pivot.base_qty }} per min)
                        </li>
                    </ul>
                </span>
                <span class="font-semibold">
                    Byproducts:
                    <ul class="flex">
                        <li
                            class="px-4 font-medium"
                            v-for="ing in production.final.recipe.byproducts"
                        >
                            {{ ing.name }} ({{ +ing.pivot.base_qty }} per min)
                        </li>
                    </ul>
                </span>
            </div>
            </div>
        </template>
        <!-- end template header -->


        <div class='py-12'>
            <div class='mx-auto flex sm:px-6 lg:px-8'>
                <div
                    v-if='done && production'
                    class='relative flex flex-1 flex-col space-y-2 p-4 dark:text-gray-100'
                >
                    <production-warning :overrides='production.overrides' :show-warnings="showWarnings" />

                    <div class='flex flex-col md:flex-row space-y-8 space-x-0 md:space-y-0 flex-1 md:space-x-8 py-4'>
                        <!-- Left Side -->
                        <production-summary :building-checks='buildingChecks'
                                            :disabled-raw-materials='disabledRawMaterials'
                                            :fetch='fetch' :fetch-new-yield='fetchNewYield' :help-import='helpImport'
                                            :help-raw-materials='helpRawMaterials' :new-imports='newImports'
                                            :new-yield='newYield' :production='production'
                                            :production__building_summary='production__building_summary'
                                            :production__total_power='production__total_power'
                                            :raw-materials='rawMaterials'
                                            :raw-unchanged='rawUnchanged' />

                        <!-- middle -->
                        <production-steps :diagrams='diagrams' :hide-completed='hideCompleted' :new-imports='newImports'
                                      :new-product='newProduct' :new-recipe='newRecipe' :production='production'
                                      :production-checks='productionChecks' :recipes='recipes'
                                      @selectNewRecipe='selectNewRecipe' @selectNewSubRecipe='selectNewSubRecipe'
                                      @toggle='toggleProductionCheck' />

                        <!-- right -->
                        <building-summary :production__building_summary='production__building_summary'
                                      :production__total_power='production__total_power' />
                    </div>
                </div>
            </div>
        </div>

    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import store from '@/store';
import ProductionSummary from '@/Pages/Production/ProductionSummary';
import ProductionSteps from '@/Pages/Production/ProductionSteps';
import BuildingSummary from '@/Pages/Production/BuildingSummary';
import ProductionWarning from '@/Pages/Production/ProductionWarning';


export default {
    name: 'ShowNew',

    components: {
        BuildingSummary,
        ProductionSteps,
        ProductionSummary,
        AppLayout,
        ProductionWarning
    },

    props: ['products', 'recipes', 'favorites', 'production', 'product', 'recipe', 'yield', 'variant', 'belt_speed', 'constraints', 'factory', 'imports'],

    data() {
        return {
            done: true,
            working: false,
            newYield: this.yield,
            newProduct: this.product,
            newRecipe: this.recipes[this.product.name].filter(o => o.id === this.production.recipe.id)[0],
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
            newImports: Object.fromEntries(this.imports.split(",").map(o=>[o,true])) || {},
            showWarnings: true,
        };
    },

    computed: {
        production__building_details() {
            let ret = [];

            for (let tier in this.production.results) {
                if (+tier === 1) continue;

                for (let ing in this.production.results[tier]) {
                    this.production.results[tier][ing].production.forEach(p => {
                        let selected =
                        ret.push({
                            product: ing,
                            variant_name: p.overview.selected_variant_name,
                            ...p.overview.details[p.overview.selected_variant_name],
                        });
                    });
                }
            }

            return ret;
        },

        production__building_summary() {
            let ret = {};
            ret.total_build_cost = {};
            ret.variants = {};
            this.production__building_details.forEach((o) => {
                if (!ret.variants.hasOwnProperty(o.variant_name))
                    ret.variants[o.variant_name] = Object.assign({}, o);
                else {
                    ret.variants[o.variant_name].num_buildings += o.num_buildings;
                    ret.variants[o.variant_name].power_usage += o.power_usage;
                }

                for (let prop in o.build_cost) {
                    // increment the build cost for the particular building
                    if (ret.variants[o.variant_name].build_cost.hasOwnProperty(prop))
                        ret.variants[o.variant_name].build_cost[prop] +=
                            o.build_cost[prop];
                    else
                        ret.variants[o.variant_name].build_cost[prop] =
                            o.build_cost[prop];

                    // increment the total build cost
                    if (ret.total_build_cost.hasOwnProperty(prop))
                        ret.total_build_cost[prop] += o.build_cost[prop];
                    else ret.total_build_cost[prop] = o.build_cost[prop];
                }
            });

            return ret;
        },

        production__total_power() {
            return this.production__building_details.map(o => +o.power_usage).reduce((a, b) => a + b, 0);
        },

        production__total_buildings() {
            return this.production__building_details.map(o => +o.num_buildings).reduce((a, b) => a + b, 0);
        },
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
            if (recipe.product_id === this.recipe.product_id) {
                this.$inertia.post(`/favorites/${recipe.id}`);
            }
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

        // getOutputs(name) {
        //     let ret = {};
        //
        //     Object.keys(this.production.recipes)
        //         .filter((product) => {
        //             return !!this.production.recipes[product];
        //         })
        //         .filter((product) => {
        //             return this.production.recipes[product].inputs[name]?.needed_qty > 0;
        //         })
        //         .forEach((product) => {
        //             ret[product] = Math.round(10000 * this.production.recipes[product].inputs[name].needed_qty) / 10000;
        //         });
        //
        //     return ret;
        // },

        helpRawMaterials() {
            alert('Constrained by something? Enter your actual available raw materials then click Update Yield. Click the green button next to the input to ignore that material for the recalculation.');
        },

        helpImport() {
            alert('Choose whether to produce each intermediate product in this factory (default) or to import select products from elsewhere.');
        },
    },
};
</script>

