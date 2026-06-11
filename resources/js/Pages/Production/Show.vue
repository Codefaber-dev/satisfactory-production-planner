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
                        <div class="flex items-center space-x-1">
                            <label class="whitespace-nowrap text-sm">Cost ×</label>
                            <input
                                type="number"
                                step="0.1"
                                min="0.1"
                                max="10"
                                v-model.number="costMultiplier"
                                class="w-20 rounded py-2 px-1 shadow dark:bg-sky-800"
                                title="Recipe cost multiplier (1.0 = default)"
                            />
                        </div>
                        <div class="flex items-center space-x-1">
                            <label class="whitespace-nowrap text-sm">Power ×</label>
                            <input
                                type="number"
                                step="0.1"
                                min="0.1"
                                max="10"
                                v-model.number="powerMultiplier"
                                class="w-20 rounded py-2 px-1 shadow dark:bg-sky-800"
                                title="Power cost multiplier (1.0 = default)"
                            />
                        </div>
                        <button @click="addOutput" class="btn btn-emerald">Add Output</button>
                    </template>
                    <template v-else>
                        <button class="btn-sm btn-gray" @click="removeOutput(index)">X</button>
                    </template>
                </div>

                <div class="my-4 flex flex-wrap gap-2">
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
                    <button
                        v-if="uniqueBuildings.length"
                        type="button"
                        @click="showBuildingSettings = !showBuildingSettings"
                        class="btn btn-gray text-sm"
                    >
                        Building Settings
                    </button>
                </div>

                <div v-if="showBuildingSettings && uniqueBuildings.length" class="rounded border border-sky-700 p-3 mb-2">
                    <p class="text-xs mb-2 font-medium text-gray-400">Building count multiple — rounds up to nearest multiple of N</p>
                    <div class="flex flex-wrap gap-3">
                        <div v-for="building in uniqueBuildings" :key="building" class="flex items-center space-x-1">
                            <label class="text-xs whitespace-nowrap">{{ building }}</label>
                            <input
                                type="number"
                                min="1"
                                step="1"
                                :value="buildingMultiples[building] || 1"
                                @change="setBuildingMultiple(building, $event.target.value)"
                                class="w-14 rounded py-1 px-1 text-sm shadow dark:bg-sky-800"
                            />
                        </div>
                    </div>
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

                    <!-- Tabs (mobile/tablet only) -->
                    <div class="xl:hidden">
                        <ul class="flex flex-wrap gap-2">
                            <li>
                                <button
                                    @click="selectedTab = 'productionSteps'"
                                    :class="selectedTab === 'productionSteps' ? 'btn btn-gray' : 'btn btn-emerald'"
                                >
                                    Production Steps
                                </button>
                            </li>
                            <li>
                                <button
                                    @click="selectedTab = 'productionSummary'"
                                    :class="selectedTab === 'productionSummary' ? 'btn btn-gray' : 'btn btn-emerald'"
                                >
                                    Summary
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-1 flex-col space-y-8 space-x-0 py-4 md:flex-row md:space-y-0 md:space-x-4">
                        <!-- Left Side -->
                        <production-summary
                            :class="{ 'hidden': selectedTab !== 'productionSummary', 'xl:block': true }"
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
                            :class="{ 'hidden': selectedTab !== 'productionSteps', 'xl:flex': true }"
                            ref="productionSteps"
                            :diagrams="diagrams"
                            :hide-completed="hideCompleted"
                            :new-imports="newImports"
                            :production="production"
                            :overviews="overviews"
                            :production-checks="productionChecks"
                            :recipes="recipes"
                            :choices="allChosenRecipes"
                            :somersloop-slots="somersloopSlots"
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

        this.Bus.on('UpdateSomersloopSlots', ({ key, slots }) => {
            this.somersloopSlots = { ...this.somersloopSlots, [key]: slots };
            this.fetch({ preserveScroll: true });
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
            for (const o of this.form.outputs) {
                o.yield = (+o.yield * ratio).$round4();
            }

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
        'somersloops',
        'cost_multiplier',
        'power_multiplier',
        'building_multiples',
    ],

    data() {
        const outputs = this.multi
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
            somersloopSlots: this.somersloops || {},
            newSpeedLimit: this.speedLimit || 'both',
            costMultiplier: this.cost_multiplier || 1.0,
            powerMultiplier: this.power_multiplier || 1.0,
            buildingMultiples: this.building_multiples || {},
            showBuildingSettings: false,
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

        uniqueBuildings() {
            return [...new Set(this.production__building_details.map((d) => d.building))].filter(Boolean).sort();
        },

        production__building_details() {
            // if (! this.$refs.productionSteps) {
            //     return [];
            // }

            return Object.values(this.overviews).map((o) => {
                const clock = o.clock;
                const variant_name = o.selected_variant_name;
                return {
                    clock,
                    variant_name,
                    building: o.overviews[clock]?.building,
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
            const deets = this.production__building_details.groupBy('variant_name');
            const ret = {
                variants: {},
            };

            for (const prop in deets) {
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

            const p = this.form.outputs[0];
            const parts = [p.product.name, p.yield, p.recipe.description || p.product.name, this.form.variant];

            return parts.join('/');
        },

        params() {
            const params = {
                imports: Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(','),
                belt_speed: this.form.belt_speed,
                factory: this.newFactory ? this.newFactory.id : '',
                variant: this.form.variant,
                choices: this.newChoices,
                even: this.newEven ? 1 : 0,
                speedLimit: this.newSpeedLimit,
                somersloops: this.somersloopSlots,
                cost_multiplier: this.costMultiplier,
                power_multiplier: this.powerMultiplier,
                building_multiples: this.buildingMultiples,
            };

            if (this.form.outputs.length > 1) {
                params.product = this.form.outputs.filter((o) => o.product && o.recipe).map((o) => o.product.name);
                params.yield = this.form.outputs.filter((o) => o.product && o.recipe).map((o) => o.yield);
                params.recipe = this.form.outputs
                    .filter((o) => o.product && o.recipe)
                    .map((o) => o.recipe.description || o.product.name);
                params.factory = undefined;
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

            const raw = [];

            for (const prop in this.rawMaterials) {
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
                const old_yield = +this.form.outputs.find((o) => o.product.name === product.name).yield;
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

            let name;
            const imports = Object.keys(this.newImports)
                .filter((o) => this.newImports[o])
                .join(',');
            const output = this.form.outputs[0];

            if (this.newFactory) {
                this.$inertia.patch(`/factories/${this.newFactory.id}`, {
                    ingredient_id: output.product.id,
                    recipe_id: output.recipe.id,
                    yield: output.yield,
                    choices: this.allChosenRecipes,
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
                choices: this.allChosenRecipes,
                imports,
            });
        },

        saveMultiFactory() {
            const imports = Object.keys(this.newImports)
                .filter((o) => this.newImports[o])
                .join(',');

            if (this.newFactory) {
                return this.$inertia.patch(`/factories/multi/${this.newFactory.id}`, {
                    outputs: this.form.outputs,
                    choices: this.allChosenRecipes,
                    imports,
                });
            }
            const name = prompt('Provide a name for your factory');

            this.$inertia.post('/factories/multi', {
                name,
                outputs: this.form.outputs,
                choices: this.allChosenRecipes,
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
            const available = this.recipes[row.product.name];

            if (available.length === 1) {
                row.recipe = available[0];
                return;
            }

            const favorite = available.find((o) => this.isFavorite(o));
            if (favorite) {
                row.recipe = favorite;
                return;
            }

            const base = available.find((o) => !o.alt_recipe);
            if (base) {
                row.recipe = base;
                return;
            }

            row.recipe = available[0];
        },

        isFavorite(recipe) {
            return !!recipe.favorite;
        },

        reset() {
            this.$inertia.get('dashboard');
        },

        toggleProductionCheck(material) {
            if (Object.hasOwn(this.productionChecks, material))
                this.productionChecks[material] = !this.productionChecks[material];
            else this.productionChecks[material] = true;
        },

        selectNewRecipe({ recipe }) {
            const row = this.form.outputs.find((o) => o.recipe && o.recipe.product_id === recipe.product_id);
            if (row) {
                row.recipe = recipe;
            } else {
                this.form.outputs[0].recipe = recipe;
            }
            this.fetch({ preserveScroll: true });
        },

        selectNewSubRecipe({ recipe }) {
            this.setNewSubFavorite(recipe);
        },

        setBuildingMultiple(name, val) {
            const step = Math.max(1, parseInt(val) || 1);
            this.buildingMultiples = { ...this.buildingMultiples, [name]: step };
            this.fetch({ preserveScroll: true });
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
