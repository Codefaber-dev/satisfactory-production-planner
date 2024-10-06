<template>
    <app-layout>
        <ProductionQuickNav v-bind="{ productionChecks }" />

        <!-- busy overlay -->
        <div v-if="working" class="fixed inset-0 z-[1000] bg-slate-900 bg-opacity-30"></div>

        <template #header>
            <div class="flex flex-col justify-center space-y-4 text-xl font-semibold">
                <span class="flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="mr-2 h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    {{ newFactory ? newFactory.name : 'New Production Line' }}
                </span>

                <div
                    v-for="(row, index) in form.outputs"
                    class="flex flex-col items-center space-x-0 space-y-2 md:flex-row md:space-x-2 md:space-y-0"
                >
                    <div class="relative flex w-full items-center md:w-48">
                        <input
                            ref="yield"
                            autofocus="autofocus"
                            type="number"
                            step="0.5"
                            min="0"
                            v-model="row.yield"
                            class="w-full appearance-none rounded py-2 px-1 shadow dark:bg-sky-800 md:w-48"
                        />
                        <div class="pointer-events-none absolute right-8 bottom-2">per min</div>
                    </div>
                    <select
                        @change="setDefaultRecipe(row)"
                        class="w-full rounded py-2 px-1 shadow dark:bg-sky-800 md:w-[unset]"
                        v-model="row.product"
                    >
                        <option :key="option.id" v-for="option in products" :value="option">
                            {{ option.name }}
                        </option>
                    </select>
                    <select
                        v-if="row.product"
                        class="w-full rounded py-2 px-1 shadow dark:bg-sky-800 md:w-[unset]"
                        v-model="row.recipe"
                    >
                        <option :key="option.id" v-for="option in recipes[row.product.name]" :value="option">
                            <template v-if="option.favorite">&star;</template>
                            {{ option.description || 'default' }}
                        </option>
                    </select>
                    <template v-if="index === 0">
                        <select
                            v-model="form.variant"
                            class="w-full rounded py-2 px-1 shadow dark:bg-sky-800 md:w-[unset]"
                        >
                            <option value="mk1">Production mk1 (base)</option>
                            <option disabled value="mk2">Production mk2 (mk++ mod) (no mod support yet)</option>
                            <option disabled value="mk3">Production mk3 (mk++ mod) (no mod support yet)</option>
                            <option disabled value="mk4">Production mk4 (mk++ mod) (no mod support yet)</option>
                        </select>
                        <select
                            v-model="form.belt_speed"
                            class="w-full rounded py-2 px-1 shadow dark:bg-sky-800 md:w-[unset]"
                        >
                            <option value="60">Belts mk1</option>
                            <option value="120">Belts mk2</option>
                            <option value="270">Belts mk3</option>
                            <option value="480">Belts mk4</option>
                            <option value="780">Belts mk5</option>
                            <option value="1200">Belts mk6</option>
                        </select>
                        <button @click="addOutput" class="btn btn-emerald">Add Output</button>
                    </template>
                    <template v-else>
                        <button class="btn-sm btn-gray" @click="removeOutput(index)">X</button>
                    </template>
                </div>

                <div class="my-4 space-x-4">
                    <button
                        v-if="$page.props?.user?.name"
                        :disabled="working"
                        @click="saveMyFactory"
                        class="btn btn-emerald"
                    >
                        {{ newFactory ? 'Save Changes To Factory' : 'Save To My Factories' }}
                    </button>
                    <button :disabled="working" @click="fetch" class="btn btn-emerald">Update</button>
                    <inertia-link class="btn btn-gray" href="/dashboard"> Start Over </inertia-link>
                </div>
                <!--            <div class="mt-4 flex flex-col">-->
                <!--                <hr class="mb-4" />-->
                <!--                <span class="font-semibold">-->
                <!--                    Recipe:-->
                <!--                    <ul class="flex">-->
                <!--                        <li-->
                <!--                            class="px-4 font-medium"-->
                <!--                            v-for="ing in production.final.recipe.ingredients"-->
                <!--                        >-->
                <!--                            {{ ing.name }} ({{ +ing.pivot.base_qty }} per min)-->
                <!--                        </li>-->
                <!--                    </ul>-->
                <!--                </span>-->
                <!--                <span class="font-semibold">-->
                <!--                    Byproducts:-->
                <!--                    <ul class="flex">-->
                <!--                        <li-->
                <!--                            class="px-4 font-medium"-->
                <!--                            v-for="ing in production.final.recipe.byproducts"-->
                <!--                        >-->
                <!--                            {{ ing.name }} ({{ +ing.pivot.base_qty }} per min)-->
                <!--                        </li>-->
                <!--                    </ul>-->
                <!--                </span>-->
                <!--            </div>-->
            </div>
        </template>
        <!-- end template header -->

        <div class="">
            <div class="mx-auto flex px-2">
                <div v-if="done && production" class="relative flex flex-1 flex-col space-y-2 p-2 dark:text-gray-100">
                    <production-warning :overrides="production.overrides" :show-warnings="showWarnings" />

                    <!-- Tabs -->
                    <div class="xl:hidden">
                        <ul class="flex space-x-4">
                            <li>
                                <button @click="selectedTab = 'productionSteps'">Production Steps</button>
                            </li>
                            <li>
                                <button @click="selectedTab = 'productionSummary'">Production Summary</button>
                            </li>
                            <li>
                                <button @click="selectedTab = 'buildingSummary'">Building Summary</button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-1 flex-col space-y-8 space-x-0 py-4 md:flex-row md:space-y-0 md:space-x-4">
                        <!-- Left Side -->
                        <production-summary
                            :building-checks="buildingChecks"
                            :disabled-raw-materials="disabledRawMaterials"
                            @fetch="fetch"
                            @fetchNewYield="fetchNewYield"
                            :help-import="helpImport"
                            :help-raw-materials="helpRawMaterials"
                            :new-imports="newImports"
                            :production="production"
                            :production__building_summary="production__building_summary"
                            :production__building_details="production__building_details"
                            :production__total_power="production__total_power"
                            :production__total_power_generated="production__total_power_generated"
                            :production__net_power="production__net_power"
                            :raw-materials="rawMaterials"
                            :raw-unchanged="rawUnchanged"
                        />

                        <!-- middle -->
                        <production-steps
                            ref="productionSteps"
                            :diagrams="diagrams"
                            :hide-completed="hideCompleted"
                            :new-imports="newImports"
                            :production="production"
                            :overviews="overviews"
                            :production-checks="productionChecks"
                            :recipes="recipes"
                            :choices="allChosenRecipes"
                            @setNewSubFavorite="setNewSubFavorite"
                            :even="newEven"
                            :speed-limit="newSpeedLimit"
                            @toggle="toggleProductionCheck"
                            @toggleDiagrams="toggleDiagrams"
                            @toggleEvenRows="toggleEvenRows"
                            @toggleSpeedLimit="toggleSpeedLimit"
                        />
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
import ProductionQuickNav from '@/Components/ProductionQuickNav.vue';

export default {
    name: 'ShowNew',

    components: {
        ProductionQuickNav,
        BuildingSummary,
        ProductionSteps,
        ProductionSummary,
        AppLayout,
        ProductionWarning,
    },

    mounted() {
        this.Bus.on('UpdateOverviews', ({ key, clock, selected_variant_name }) => {
            if (this.overviews[key]) {
                this.overviews[key].clock = clock;
                this.overviews[key].selected_variant_name = selected_variant_name;
                this.$forceUpdate();
            }
        });

        this.Bus.on('AddOutput', ({ product, recipe, qty }) => {
            product = this.products.find((o) => o.name === product);
            recipe = this.recipes[product.name].find((o) => o.id === recipe.id);
            qty = +qty.$round4();

            this.pushOutput({
                qty,
                product,
                recipe,
            });

            this.fetch();
        });

        this.Bus.on('ScaleOutputs', ({ ratio }) => {
            this.form.outputs.forEach((o) => {
                o.yield = (+o.yield * ratio).$round4();
            });

            this.fetch();
        });
    },

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
        'factory',
        'multiFactory',
        'imports',
        'multi',
        'choices',
        'even',
        'speedLimit',
    ],

    data() {
        let outputs = this.multi
            ? this.multi.products.map((o, i) => {
                  return {
                      yield: this.multi.yields[i],
                      product: this.products.filter((oo) => oo.id === o.id)[0],
                      recipe: this.recipes[o.name].filter((o) => o.id === this.multi.recipes[i].id)[0],
                  };
              })
            : [
                  {
                      yield: this.yield,
                      product: this.products.filter((o) => o.id === this.product.id)[0],
                      recipe: this.recipes[this.product.name].filter((o) => o.id === this.production.recipe.id)[0],
                  },
              ];

        return {
            form: {
                outputs,
                variant: this.variant,
                belt_speed: this.belt_speed || 780,
            },
            newFactory: this.multi ? this.multiFactory : this.factory,
            done: true,
            working: false,
            // newYield: this.yield,
            // newProduct: this.products.filter(o => o.id === this.product.id)[0],
            // newRecipe: this.recipes[this.product.name].filter(o => o.id === this.production.recipe.id)[0],
            // newVariant: this.variant,
            // recipe_models: this.production.recipe_models,
            // newBeltSpeed: this.belt_speed || 780,
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
            newImports: this.imports ? Object.fromEntries((this.imports || '').split(',').map((o) => [o, true])) : {},
            showWarnings: true,
            newChoices: this.choices || {},
            newEven: !!this.even,
            newSpeedLimit: this.speedLimit || 'both',
            overviews: this.production.overviews,
            selectedTab: 'productionSteps',
        };
    },

    computed: {
        allChosenRecipes() {
            return Object.assign(
                {},
                this.newChoices,
                Object.fromEntries(
                    this.form.outputs
                        .filter((o) => o.product && o.recipe)
                        .map((o) => [o.product.name, o.recipe.description || o.product.name])
                )
            );
        },

        production__building_details() {
            // if (! this.$refs.productionSteps) {
            //     return [];
            // }

            return Object.values(this.overviews).map((o) => {
                let clock = o.clock,
                    variant_name = o.selected_variant_name;
                return {
                    clock,
                    variant_name,
                    ...o.overviews[clock].details[variant_name],
                };
            });

            // let ret = [];
            //
            // for (let tier in this.production.results) {
            //     if (+tier === 1) continue;
            //
            //     for (let ing in this.production.results[tier]) {
            //         this.production.results[tier][ing].production.forEach(p => {
            //             let selected =
            //             ret.push({
            //                 product: ing,
            //                 variant_name: p.overview.selected_variant_name,
            //                 ...p.overview.details[p.overview.selected_variant_name],
            //             });
            //         });
            //     }
            // }
            //
            // return ret;
        },

        production__building_summary() {
            let deets = this.production__building_details.groupBy('variant_name'),
                ret = {
                    variants: {},
                };

            for (let prop in deets) {
                ret.variants[prop] = {
                    build_cost: deets[prop].mapAndSumProperties('build_cost'),
                    num_buildings: deets[prop].sum('num_buildings'),
                    power_usage: deets[prop].sum('power_usage'),
                };
            }

            ret.total_build_cost = this.production__building_details.mapAndSumProperties('build_cost');

            return ret;

            // let ret = {};
            // ret.total_build_cost = {};
            // ret.variants = {};
            // this.production__building_details.forEach((o) => {
            //     if (!ret.variants.hasOwnProperty(o.variant_name))
            //         ret.variants[o.variant_name] = Object.assign({}, o);
            //     else {
            //         ret.variants[o.variant_name].num_buildings += o.num_buildings;
            //         ret.variants[o.variant_name].power_usage += o.power_usage;
            //     }
            //
            //     for (let prop in o.build_cost) {
            //         // increment the build cost for the particular building
            //         if (ret.variants[o.variant_name].build_cost.hasOwnProperty(prop))
            //             ret.variants[o.variant_name].build_cost[prop] +=
            //                 o.build_cost[prop];
            //         else
            //             ret.variants[o.variant_name].build_cost[prop] =
            //                 o.build_cost[prop];
            //
            //         // increment the total build cost
            //         if (ret.total_build_cost.hasOwnProperty(prop))
            //             ret.total_build_cost[prop] += o.build_cost[prop];
            //         else ret.total_build_cost[prop] = o.build_cost[prop];
            //     }
            // });

            // return ret;
        },

        production__total_power() {
            return this.production__building_details
                .filter((o) => +o.power_usage > 0)
                .map((o) => +o.power_usage)
                .reduce((a, b) => a + b, 0);
        },

        production__total_power_generated() {
            return this.production__building_details
                .filter((o) => +o.power_usage < 0)
                .map((o) => +o.power_usage)
                .reduce((a, b) => a + b, 0);
        },

        production__net_power() {
            return this.production__building_details.map((o) => +o.power_usage).reduce((a, b) => a + b, 0);
        },

        production__total_buildings() {
            return this.production__building_details.map((o) => +o.num_buildings).reduce((a, b) => a + b, 0);
        },

        endpoint() {
            if (this.form.outputs.length > 1) {
                return 'multi';
            }

            let p = this.form.outputs[0],
                parts = [p.product.name, p.yield, p.recipe.description || p.product.name, this.form.variant];

            return parts.join('/');
        },

        params() {
            let params = {
                imports: Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(','),
                belt_speed: this.form.belt_speed,
                factory: this.newFactory ? this.newFactory.id : '',
                variant: this.form.variant,
                choices: this.newChoices,
                even: this.newEven ? 1 : 0,
                speedLimit: this.newSpeedLimit,
            };

            if (this.form.outputs.length > 1) {
                params.product = this.form.outputs.filter((o) => o.product && o.recipe).map((o) => o.product.name);
                params.yield = this.form.outputs.filter((o) => o.product && o.recipe).map((o) => o.yield);
                params.recipe = this.form.outputs
                    .filter((o) => o.product && o.recipe)
                    .map((o) => o.recipe.description || o.product.name);
                delete params.factory;
                params.multiFactory = this.newFactory ? this.newFactory.id : '';
            }

            return params;
        },
    },

    methods: {
        toggleSpeedLimit() {
            if (this.newSpeedLimit === 'both') {
                this.newSpeedLimit = 'inputs';
            } else if (this.newSpeedLimit === 'inputs') {
                this.newSpeedLimit = 'outputs';
            } else {
                this.newSpeedLimit = 'both';
            }

            this.fetch();
        },

        updateOverviews() {
            this.overviews = this.productionSteps.overviews || [];
            this.$forceUpdate();
        },

        async fetch(options = {}) {
            if (this.yield < 1) return false;

            this.working = true;

            this.$inertia.get(`/dashboard/${this.endpoint}`, this.params, options);
        },

        async fetchNewYield() {
            if (this.yield < 1) return false;

            this.working = true;

            let raw = [];

            for (let prop in this.rawMaterials) {
                if (!this.disabledRawMaterials[prop]) {
                    raw.push(`${prop}:${this.rawMaterials[prop]}`);
                }
            }

            if (!raw.length) return false;

            this.$inertia.get(`/newyield/${this.endpoint}`, {
                ...this.params,
                raw: raw.join(','),
            });
        },

        toggleEvenRows() {
            this.newEven = !this.newEven;
            this.fetch({ preserveScroll: true });
        },

        toggleDiagrams() {
            this.diagrams = !this.diagrams;
            this.savePrefs();
        },

        updateYield(name, qty) {
            this.form.outputs.find((o) => o.product.name === name).yield = qty;
        },

        pushOutput({ qty, product, recipe }) {
            if (product && this.form.outputs.some((o) => o.product.name === product.name)) {
                let old_yield = +this.form.outputs.find((o) => o.product.name === product.name).yield;
                this.updateYield(product.name, old_yield + qty);
                return;
            }

            this.form.outputs.push({
                yield: qty,
                product,
                recipe,
            });

            this.newFactory = null;
        },

        addOutput() {
            this.pushOutput({
                qty: 10,
                product: null,
                recipe: null,
            });
        },

        removeOutput(index) {
            this.form.outputs.splice(index, 1);
            this.newFactory = null;
            this.fetch();
        },

        saveMyFactory() {
            if (this.form.outputs.length > 1) {
                return this.saveMultiFactory();
            }

            let name,
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(','),
                output = this.form.outputs[0];

            if (this.newFactory) {
                this.$inertia.patch(`/factories/${this.newFactory.id}`, {
                    ingredient_id: output.product.id,
                    recipe_id: output.recipe.id,
                    yield: output.yield,
                    choices: this.newChoices,
                    imports,
                });
            } else {
                name = prompt('Provide a name for your factory');
            }

            if (!name) return;

            this.$inertia.post('/factories', {
                name,
                ingredient_id: output.product.id,
                recipe_id: output.recipe.id,
                yield: output.yield,
                choices: this.newChoices,
                imports,
            });
        },

        saveMultiFactory() {
            let name,
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(',');

            if (this.newFactory) {
                return this.$inertia.patch(`/factories/multi/${this.newFactory.id}`, {
                    outputs: this.form.outputs,
                    choices: this.newChoices,
                    imports,
                });
            } else {
                name = prompt('Provide a name for your factory');
            }

            this.$inertia.post('/factories/multi', {
                name,
                outputs: this.form.outputs,
                choices: this.newChoices,
                imports,
            });
        },

        setNewSubFavorite({ recipe }) {
            if (this.form.outputs.some((o) => o.recipe.product_id === recipe.product_id)) {
                this.form.outputs.find((o) => o.recipe.product_id === recipe.product_id).recipe = recipe;
            } else {
                this.newChoices[recipe.product.name] = recipe.description || recipe.product.name;
            }
            this.fetch({
                preserveScroll: true,
            });
        },

        setNewFavorite() {
            this.$inertia.post(`/favorites/${this.recipe.id}`);
        },

        // setDefaultRecipe(row) {
        //     this.recipes[row.product.name].forEach((recipe) => {
        //         if (this.isFavorite(recipe)) {
        //             row.recipe = recipe;
        //         }
        //     });
        // },

        setDefaultRecipe(row) {
            // if there is only one recipe, use it
            if (this.recipes[row.product.name].length === 1) {
                this.setRecipe(this.recipes[row.product.name][0]);
                return;
            }

            // if there is a favorite recipe, use that
            if (this.recipes[row.product.name].some((o) => this.isFavorite(o))) {
                this.setRecipe(this.recipes[row.product.name].filter((o) => this.isFavorite(o))[0]);
                return;
            }

            // if there is a base recipe, use that
            if (this.recipes[row.product.name].some((o) => !o.alt_recipe)) {
                this.setRecipe(this.recipes[row.product.name].filter((o) => !o.alt_recipe)[0]);
                return;
            }

            // else, use the first recipe available
            this.setRecipe(this.recipes[row.product.name][0]);
        },

        isFavorite(recipe) {
            return !!recipe.favorite;
        },

        reset() {
            this.$inertia.get('dashboard');
        },

        toggleProductionCheck(material) {
            if (this.productionChecks.hasOwnProperty(material))
                this.productionChecks[material] = !this.productionChecks[material];
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
            alert(
                'Constrained by something? Enter your actual available raw materials then click Update Yield. Click the green button next to the input to ignore that material for the recalculation.'
            );
        },

        helpImport() {
            alert(
                'Choose whether to produce each intermediate product in this factory (default) or to import select products from elsewhere.'
            );
        },
    },
};
</script>
