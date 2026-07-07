<template>
    <app-layout>
        <ProductionQuickNav v-bind="{ productionChecks }" />

        <!-- busy overlay -->
        <div v-if="working" class="fixed inset-0 z-1000 bg-slate-900 bg-opacity-30"></div>

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
                        <button @click="addOutput" class="btn btn-emerald">Add Output</button>
                        <button
                            v-if="done && production"
                            :disabled="working"
                            data-test="perfect-ratio"
                            @click="applyPerfectRatio"
                            class="btn btn-emerald"
                            title="Scale the whole plan to the smallest size where every building runs whole at 100% clock"
                        >
                            🎯 Perfect Ratio
                        </button>
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
                        type="button"
                        @click="showBuildingSettings = !showBuildingSettings"
                        class="btn btn-gray"
                    >
                        Settings
                    </button>
                </div>

                <plan-settings-panel
                    v-if="showBuildingSettings"
                    v-model:belt-speed="form.belt_speed"
                    v-model:cost-multiplier="costMultiplier"
                    v-model:building-cost-multiplier="buildingCostMultiplier"
                    v-model:power-multiplier="powerMultiplier"
                    :auto-package-recycle="newAutoPackageRecycle"
                    :even="newEven"
                    :speed-limit="newSpeedLimit"
                    :unique-buildings="uniqueBuildings"
                    :designer-dims="designerDims"
                    :bp-designer="bpDesigner"
                    :bp-enabled="bpEnabled"
                    :bp-sizes="bpSizes"
                    :working="working"
                    @toggle-auto-package-recycle="toggleAutoPackageRecycle"
                    @toggle-even-rows="toggleEvenRows"
                    @toggle-speed-limit="toggleSpeedLimit"
                    @set-designer-mk="setDesignerMk"
                    @toggle-blueprint-enabled="toggleBlueprintEnabled"
                    @set-blueprint-size="setBlueprintSize"
                    @apply="fetch({ preserveScroll: true })"
                />
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
                            :import-notes="newImportNotes"
                            :raw-sources="newRawSources"
                            :recycling="production.recycling"
                            @updateImportNote="updateImportNote"
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
                            :import-notes="newImportNotes"
                            :recycling="production.recycling"
                            :production="production"
                            :overviews="overviews"
                            :production-checks="productionChecks"
                            :recipes="recipes"
                            :choices="allChosenRecipes"
                            :raw-sources="newRawSources"
                            :somersloop-slots="somersloopSlots"
                            :cost-multiplier="costMultiplier"
                            :building-multiples="appliedMultiples"
                            :designer-mk="bpDesigner"
                            @setNewSubFavorite="setNewSubFavorite"
                            @updateRawSource="updateRawSource"
                            :applied-even="!!even"
                            @toggle="toggleProductionCheck"
                            @toggleDiagrams="toggleDiagrams"
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
import { clampSize, effectiveMultiples, loadEnabled, loadSizes, saveEnabled, saveSizes } from '@/blueprintSettings';
import { computePerfectRatio } from '@/perfectRatio';
import { DESIGNER_DIMS, loadDesignerMk, saveDesignerMk } from '@/blueprintFootprint';
import ProductionSummary from '@/Pages/Production/ProductionSummary';
import ProductionSteps from '@/Pages/Production/ProductionSteps';
import ProductionWarning from '@/Pages/Production/ProductionWarning';
import ProductionQuickNav from '@/Components/ProductionQuickNav.vue';
import PlanSettingsPanel from '@/Pages/Production/PlanSettingsPanel.vue';

export default {
    name: 'ShowNew',

    components: {
        ProductionQuickNav,
        ProductionSteps,
        ProductionSummary,
        AppLayout,
        ProductionWarning,
        PlanSettingsPanel,
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

        this.Bus.on('FillOutput', ({ product, qty }) => {
            this.fillOutput(product, qty);
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
        'building_cost_multiplier',
        'raw_sources',
        'import_notes',
        'auto_package_recycle',
    ],

    data() {
        const bpSizes = loadSizes(store);
        const bpEnabled = loadEnabled(store, (this.multi ? this.multiFactory : this.factory)?.id);
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
            somersloopSlots: Object.fromEntries(
                Object.entries(this.somersloops || {}).map(([k, v]) => [k, Number.parseInt(v) || 0])
            ),
            newSpeedLimit: this.speedLimit || 'both',
            costMultiplier: this.cost_multiplier || 1.0,
            powerMultiplier: this.power_multiplier || 1.0,
            buildingCostMultiplier: this.building_cost_multiplier || 1.0,
            // V59: per-raw source mode config (default import for any raw not present).
            newRawSources: this.raw_sources || {},
            // V64: per-ingredient import notes (map ingredient → free-text note).
            newImportNotes: this.import_notes || {},
            // V67: auto-package-and-recycle excess fluids toggle (default OFF).
            newAutoPackageRecycle: !!this.auto_package_recycle,
            bpSizes,
            bpEnabled,
            appliedMultiples: effectiveMultiples(bpSizes, bpEnabled),
            bpDesigner: loadDesignerMk(store),
            designerDims: DESIGNER_DIMS,
            showBuildingSettings: false,
            overviews: this.production.overviews,
            selectedTab: 'productionSteps',
        };
    },

    computed: {
        allChosenRecipes() {
            // V57: capture every recipe actually in force in the production tree
            // (incl. sub-recipes that match the current default), not just the
            // user's explicit newChoices + top-level outputs — otherwise those
            // picks are dropped on save and the factory reverts on reload (#4).
            const treeRecipes = Object.fromEntries(
                Object.values(this.overviews || {})
                    .filter((o) => o.overview?.product && o.overview.recipe)
                    .map((o) => [o.overview.product, o.overview.recipe])
            );

            return Object.assign(
                {},
                treeRecipes,
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

        buildingMultiples() {
            return effectiveMultiples(this.bpSizes, this.bpEnabled);
        },

        production__building_details() {
            const recipeDetails = Object.values(this.overviews).map((o) => {
                const clock = o.clock;
                const variant_name = o.selected_variant_name;
                return {
                    clock,
                    variant_name,
                    building: o.overviews[clock]?.building,
                    ...o.overviews[clock].details[variant_name],
                };
            });

            // V76: extract-mode raws contribute their extractors (summary/parts/power)
            // V87: auto-package Packager steps fold into the build (summary/parts/power)
            const recycleDetails = (this.production.recycling?.packaged || []).map((p) => ({
                product: p.product,
                variant_name: p.building || 'Packager',
                num_buildings: p.buildings,
                exact_buildings: p.buildings,
                power_usage: p.power,
                build_cost: p.build_cost || {},
                clock_speed: 100,
                footprint: { power_shards: 0, somersloops: 0 },
            }));

            // V106: the AWESOME Sink folds in too — power + build cost, like the Packager rows
            const sinkDetails = (this.production.recycling_steps || [])
                .filter((s) => s.type === 'sink')
                .map((s) => ({
                    product: s.name,
                    variant_name: s.building,
                    num_buildings: s.num_buildings,
                    exact_buildings: s.num_buildings,
                    power_usage: s.power,
                    build_cost: s.build_cost || {},
                    clock_speed: 100,
                    footprint: { power_shards: 0, somersloops: 0 },
                }));

            return [...recipeDetails, ...(this.production.extractors || []), ...recycleDetails, ...sinkDetails];
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
                building_cost_multiplier: this.buildingCostMultiplier,
                building_multiples: this.buildingMultiples,
                raw_sources: this.newRawSources,
                import_notes: this.newImportNotes,
                auto_package_recycle: this.newAutoPackageRecycle ? 1 : 0,
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

    watch: {
        production(newProd) {
            this.overviews = newProd.overviews;
        },
    },

    methods: {
        // V112: the panel select supplies the value directly (header cycle button removed)
        toggleSpeedLimit(value) {
            this.newSpeedLimit = value;
            this.fetch();
        },

        updateOverviews() {
            this.overviews = this.productionSteps.overviews || [];
            this.$forceUpdate();
        },

        async fetch(options = {}) {
            if (this.yield < 1) return false;

            this.working = true;
            this.appliedMultiples = this.buildingMultiples;

            this.$inertia.get(`/dashboard/${this.endpoint}`, this.params, options);
        },

        // V59–V63: a raw's source mode changed. Update the map immutably and refetch
        // so the new sourcing (extractor / convert recipe / unpackage) is applied.
        // V104/B58: leaving convert/unpackage drops the raw's recipe choice so the
        // stale pick can't be re-captured on save (backend gate authoritative).
        updateRawSource({ name, config }) {
            this.newRawSources = {
                ...this.newRawSources,
                [name]: config,
            };

            if (!['convert', 'unpackage'].includes(config?.mode)) {
                const { [name]: dropped, ...rest } = this.newChoices;
                this.newChoices = rest;
            }

            this.fetch({ preserveScroll: true });
        },

        // V64: an import note changed. Update the map immutably. No refetch — notes
        // carry no calc impact; the bound map updates the display directly and
        // persists on save.
        updateImportNote({ name, note }) {
            const next = { ...this.newImportNotes };
            if (note) {
                next[name] = note;
            } else {
                delete next[name];
            }
            this.newImportNotes = next;
        },

        // V67: toggle auto-package-and-recycle excess fluids. Changes the plan's
        // recycling output (and adds Packager steps), so refetch.
        toggleAutoPackageRecycle() {
            this.newAutoPackageRecycle = !this.newAutoPackageRecycle;
            this.fetch({ preserveScroll: true });
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

        // set a single output's yield to its 100%-fill value, leaving all other outputs untouched (V55)
        fillOutput(name, qty) {
            if (!this.form.outputs.some((o) => o.product && o.product.name === name)) return;

            this.updateYield(name, (+qty).$round4());
            this.fetch();
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
                    raw_sources: this.newRawSources,
                    import_notes: this.newImportNotes,
                    auto_package_recycle: this.newAutoPackageRecycle,
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
                raw_sources: this.newRawSources,
                import_notes: this.newImportNotes,
                auto_package_recycle: this.newAutoPackageRecycle,
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
                    raw_sources: this.newRawSources,
                    import_notes: this.newImportNotes,
                    auto_package_recycle: this.newAutoPackageRecycle,
                });
            }
            const name = prompt('Provide a name for your factory');

            this.$inertia.post('/factories/multi', {
                name,
                outputs: this.form.outputs,
                choices: this.allChosenRecipes,
                imports,
                raw_sources: this.newRawSources,
                import_notes: this.newImportNotes,
                auto_package_recycle: this.newAutoPackageRecycle,
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

        setBlueprintSize(name, val) {
            this.bpSizes = { ...this.bpSizes, [name]: clampSize(val) };
            saveSizes(store, this.bpSizes);
        },

        toggleBlueprintEnabled(name) {
            this.bpEnabled = { ...this.bpEnabled, [name]: !this.bpEnabled[name] };
            saveEnabled(store, this.newFactory?.id, this.bpEnabled);
        },

        setDesignerMk(mk) {
            this.bpDesigner = mk;
            saveDesignerMk(store, mk);
        },

        savePrefs() {
            store.setItem('diagrams', this.diagrams);
        },

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

        // Perfect Ratio Factory mode (§V56 / T96): scale every output by the
        // smallest whole factor S that makes every step's machine count a whole
        // integer at 100% clock. S = LCM of each step's exact fractional demand.
        // Blueprint-aware (§V81 / T123): a grouped step (effective multiple X > 1,
        // d.multiple = the applied/snapshot building_multiple, T88) scales to a whole
        // STAMP — S clears denominator(exact_buildings / X) — so num_buildings stays a
        // multiple of X; ungrouped (X=1) is identical to V56.
        applyPerfectRatio() {
            const steps = this.production__building_details.map((d) => ({
                count: d.exact_buildings,
                multiple: d.multiple || 1,
            }));
            const { ratio, blocked, reason, maxBuildings } = computePerfectRatio(steps);

            if (blocked) {
                const detail =
                    reason === 'buildings'
                        ? `the perfect ratio would need up to ${maxBuildings} of a single building`
                        : `the required scale factor (×${ratio}) is too large`;
                alert(
                    `Perfect Ratio not applied — ${detail}. Your factory would be impractically large. Try scaling a smaller portion manually.`
                );
                return;
            }

            if (ratio === 1) {
                alert('This factory is already in a perfect ratio — every building runs whole at 100% clock.');
                return;
            }

            this.Bus.emit('ScaleOutputs', { ratio });
        },
    },
};
</script>
