<template>
    <div
        class="flex flex-1 flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900"
    >
        <div
            class="flex flex-wrap items-center justify-center gap-2 rounded-t-lg bg-gray-900 p-4 text-xl font-semibold text-white dark:bg-sky-700"
        >
            <span class="w-full text-center sm:w-auto">Production Steps</span>
            <!-- V112: Force Even Rows + Limit Belt Rates moved to the Plan Settings panel -->
            <!-- V110: type-ahead ingredient filter -->
            <StepFilter :options="filterOptions" v-model="filterItem" />
        </div>
        <div class="overflow-x-auto">
        <table class="block w-full p-2 lg:table lg:table-auto lg:p-0">
            <tr class="hidden lg:table-row">
                <th></th>
                <th class="font-semibold">Ingredient</th>
                <th class="font-semibold">Inputs</th>
                <th class="font-semibold">Byproducts</th>
                <th class="font-semibold">Recipe</th>
                <th class="font-semibold">Production</th>
            </tr>
            <tr class="block lg:table-row" v-show="Object.values(productionChecks).some((o) => o)">
                <th class="block bg-blue-200 p-2 text-center dark:bg-sky-600 lg:table-cell" colspan="100">
                    {{ hideCompleted ? 'Hiding' : 'Showing' }}
                    {{ Object.values(productionChecks).filter((o) => o).length }}
                    completed rows
                    <button
                        @click="hideCompleted = !hideCompleted"
                        class="rounded bg-emerald-500 px-4 py-2 text-sm hover:bg-emerald-600 focus:bg-emerald-700"
                    >
                        Toggle Completed
                    </button>
                </th>
            </tr>
            <!-- Level 0: raws render first as steps (V100), source-mode controls on the step (V101) -->
            <template v-if="Object.keys(rawSteps).length">
                <tr v-show="rawStepsVisible" class="block lg:table-row">
                    <th class="block bg-blue-200 py-2 text-2xl dark:bg-slate-800 lg:table-cell" colspan="100">Level 0</th>
                </tr>
                <template v-for="(material, name) in rawSteps">
                    <ProductionStep
                        v-for="prod in material.production"
                        v-show="stepMatchesFilter(prod, name)"
                        :key="name + '-raw-' + (prod.recipe?.description || 'leaf')"
                        :level-index="0"
                        :level-step-map="levelStepMap"
                        :step-index="rawStepIndex(name)"
                        :choices="choices"
                        :material="material"
                        :name="name"
                        :new-imports="newImports"
                        :import-notes="importNotes"
                        :recycling="recycling"
                        :production="prod"
                        :recipes="recipes"
                        :raw-sources="rawSources"
                        :extractors="production.extractors || []"
                        :all-materials="production.all_materials"
                        :byproducts-used="production.byproducts_used"
                        :overviews="overviews"
                        :somersloop-slots="somersloopSlots"
                        :cost-multiplier="costMultiplier"
                        :building-multiples="buildingMultiples"
                        :designer-mk="designerMk"
                        :applied-even="appliedEven"
                        @toggle="toggle"
                        @setNewSubFavorite="setNewSubFavorite"
                        @updateRawSource="(payload) => $emit('updateRawSource', payload)"
                    />
                </template>
            </template>

            <template v-for="(level, index) in resultsArray">
                <tr v-show="levelHasVisibleStep(level)" class="block lg:table-row">
                    <th class="block bg-blue-200 py-2 text-2xl dark:bg-slate-800 lg:table-cell" colspan="100">Level {{ index + 1 }}</th>
                </tr>

                <template v-for="(material, name) in level">
                    <ProductionStep
                        v-for="prod in material.production.filter((o) => o.recipe && !material.raw)"
                        v-show="
                            !prod.imported &&
                            (!hideCompleted || !productionChecks[name + '-' + (prod.recipe?.description || 'base')]) &&
                            stepMatchesFilter(prod, name)
                        "
                        :class="[
                            productionChecks[name + '-' + (prod.recipe?.description || 'base')]
                                ? 'opacity-25'
                                : 'opacity-100',
                        ]"
                        :finished="productionChecks[name + '-' + (prod.recipe?.description || 'base')]"
                        :level-index="index + 1"
                        :level-step-map="levelStepMap"
                        :step-index="getStepIndex(level, name)"
                        :choices="choices"
                        :material="material"
                        :name="name"
                        :new-imports="newImports"
                        :import-notes="importNotes"
                        :recycling="recycling"
                        :production="prod"
                        :recipes="recipes"
                        :all-materials="production.all_materials"
                        :byproducts-used="production.byproducts_used"
                        :overviews="overviews"
                        :somersloop-slots="somersloopSlots"
                        :cost-multiplier="costMultiplier"
                        :building-multiples="buildingMultiples"
                        :designer-mk="designerMk"
                        :applied-even="appliedEven"
                        @toggle="toggle"
                        @setNewSubFavorite="setNewSubFavorite"
                    />
                </template>
            </template>

            <!-- V88: terminal Recycling level — packaging + sink render as full build steps -->
            <template v-if="recycleSteps.length">
                <tr v-show="anyRecycleStepVisible" class="block lg:table-row">
                    <th class="block bg-emerald-200 py-2 text-2xl dark:bg-emerald-900 lg:table-cell" colspan="100">
                        Recycling (AWESOME Sink)
                    </th>
                </tr>
                <template v-for="(step, i) in recycleSteps" :key="`recycle-step-${i}`">
                    <!-- V90: auto-package fluid → a real ProductionStep, recipe locked
                         (no recipes[name] entry), output destination → the AWESOME Sink -->
                    <ProductionStep
                        v-if="step.type === 'package'"
                        v-show="recycleStepMatchesFilter(step)"
                        v-bind="packagePayload(step)"
                        data-test="recycle-step"
                        :identifier-override="recycleIdentifier(i)"
                        :level-step-map="packageDestinationMap"
                        :recipes="{}"
                        :synthetic="true"
                    />
                    <!-- V91: terminal AWESOME Sink step -->
                    <RecycleStep
                        v-else
                        v-show="recycleStepMatchesFilter(step)"
                        data-test="recycle-step"
                        :step="step"
                        :identifier="recycleIdentifier(i)"
                        :step-map="recycleStepMap"
                        @flashDestination="flashDestination"
                    />
                </template>
            </template>
        </table>
        </div>
    </div>
</template>
<script>
import ProductionStep from '@/Pages/Production/ProductionStep';
import RecycleStep from '@/Pages/Production/RecycleStep';
import StepFilter from '@/Pages/Production/StepFilter';

export default {
    name: 'production-steps',
    components: { ProductionStep, RecycleStep, StepFilter },

    data() {
        return {
            // V110: selected type-ahead filter item (null = no filter, display-only, not persisted)
            filterItem: null,
        };
    },
    props: {
        hideCompleted: {},
        newImports: {},
        newProduct: {},
        newRecipe: {},
        production: {},
        productionChecks: {},
        recipes: {},
        choices: {},
        appliedEven: {
            type: Boolean,
            default: false,
        },
        overviews: {},
        somersloopSlots: {
            default: () => ({}),
        },
        costMultiplier: {
            type: Number,
            default: 1.0,
        },
        buildingMultiples: {
            default: () => ({}),
        },
        designerMk: {
            type: String,
            default: 'mk1',
        },
        // current raw_sources map (raw → {mode,...}), V59
        rawSources: {
            default: () => ({}),
        },
        // V64: map ingredient → import note
        importNotes: {
            default: () => ({}),
        },
        // V66/V67: recycling result { points, recycled, packaged, waste }
        recycling: {
            default: null,
        },
    },

    methods: {
        rawStepIndex(name) {
            return Object.keys(this.rawSteps).indexOf(name);
        },

        helpOverride() {
            alert('Your chosen recipe was overridden to avoid a circular dependency.');
        },

        setNewSubFavorite({ recipe }) {
            this.$emit('setNewSubFavorite', { recipe });
        },

        toggle(material) {
            this.$emit('toggle', material);
        },

        getStepIndex(level, name) {
            return Object.keys(level)
                .map((key) => {
                    return { key, ...level[key] };
                })
                .filter((row) => row.production.filter((o) => o.recipe).length)
                .filter((row) => !row.imported)
                .findIndex((row) => row.key === name);
        },

        stepLetter(index) {
            return Array.from('ABCDEFGHIJKLMNOPQRSTUVWXYZ')[index];
        },

        // V88: identifier for a recycling step (terminal "R" level)
        recycleIdentifier(index) {
            return `R.${this.stepLetter(index)}`;
        },

        flashDestination(dest) {
            this.Bus.emit('flash', { dest });
        },

        // V110: a step matches when the selected item is its output product,
        // one of its byproducts, or one of its ingredient inputs
        stepMatchesFilter(prod, name) {
            if (!this.filterItem) {
                return true;
            }

            return (
                name === this.filterItem ||
                this.filterItem in (prod.ingredients || {}) ||
                this.filterItem in (prod.byproducts || {})
            );
        },

        // V110: recycle-step variant — descriptors carry inputs[]/output, not a prod shape
        recycleStepMatchesFilter(step) {
            if (!this.filterItem) {
                return true;
            }

            return (step.inputs || []).some((i) => i.item === this.filterItem) || step.output?.item === this.filterItem;
        },

        // V110: hide a level header when the filter leaves it no visible steps
        levelHasVisibleStep(level) {
            return Object.entries(level).some(([name, material]) =>
                material.production.some(
                    (prod) => prod.recipe && !material.raw && !prod.imported && this.stepMatchesFilter(prod, name)
                )
            );
        },

        // V90/V93: adapt a backend `package` recycling descriptor into ProductionStep
        // props. The real 4-clock overview (merged into the plan overviews map, T132)
        // drives the build diagram + clock/shard controls; omitting a recipes[name]
        // entry locks only the recipe *pick* (no RecipePicker).
        packagePayload(step) {
            return {
                name: step.name,
                production: {
                    // description matches the backend overview key `product|recipe.description ?? product`
                    recipe: { product: { name: step.name }, description: step.recipe ?? null },
                    qty: step.output.qty,
                    ingredients: Object.fromEntries((step.inputs || []).map((i) => [i.item, i.qty])),
                    byproducts: {},
                    imported: false,
                    overridden: false,
                },
                material: { raw: false, outputs: { [this.sinkName]: step.output.qty } },
                // reuse the real backend overview set, not a hand-rolled synthetic one
                overviews: this.overviews,
                choices: {},
                newImports: {},
                byproductsUsed: {},
                allMaterials: {},
            };
        },
    },

    computed: {
        // V110: type-ahead options = every ingredient + product of the displayed plan —
        // union over rendered steps of output product + byproducts + input ingredients
        filterOptions() {
            const names = new Set();

            const addProd = (name, prod) => {
                names.add(name);
                Object.keys(prod.ingredients || {}).forEach((n) => names.add(n));
                Object.keys(prod.byproducts || {}).forEach((n) => names.add(n));
            };

            for (const [name, material] of Object.entries(this.rawSteps)) {
                material.production.forEach((prod) => addProd(name, prod));
            }

            for (const level of this.resultsArray) {
                for (const [name, material] of Object.entries(level)) {
                    material.production
                        .filter((o) => o.recipe && !material.raw && !o.imported)
                        .forEach((prod) => addProd(name, prod));
                }
            }

            for (const step of this.recycleSteps) {
                (step.inputs || []).forEach((i) => names.add(i.item));
                if (step.output?.item) {
                    names.add(step.output.item);
                }
            }

            return [...names].sort((a, b) => a.localeCompare(b));
        },

        // V110: Level 0 header hides when the filter leaves no visible raw step
        rawStepsVisible() {
            return Object.entries(this.rawSteps).some(([name, material]) =>
                material.production.some((prod) => this.stepMatchesFilter(prod, name))
            );
        },

        // V110: Recycling header hides when the filter leaves no visible recycle step
        anyRecycleStepVisible() {
            return this.recycleSteps.some((step) => this.recycleStepMatchesFilter(step));
        },

        // V88: full recycling build steps (packaging + sink) from the backend
        recycleSteps() {
            return this.production.recycling_steps || [];
        },

        // material name → producing-step identifier, incl. packaging steps (so the sink's
        // packaged inputs link to their Packager step), layered over levelStepMap
        recycleStepMap() {
            const map = { ...this.levelStepMap };
            this.recycleSteps.forEach((step, i) => {
                if (step.type === 'package') {
                    map[step.name] = this.recycleIdentifier(i);
                }
            });
            return map;
        },

        sinkIdentifier() {
            const i = this.recycleSteps.findIndex((s) => s.type === 'sink');
            return i >= 0 ? this.recycleIdentifier(i) : '';
        },

        // name of the terminal sink step (the destination a packaging step's output links to)
        sinkName() {
            const sink = this.recycleSteps.find((s) => s.type === 'sink');
            return sink ? sink.name : 'AWESOME Sink';
        },

        // V90: levelStepMap + the sink, so a packaging ProductionStep's output destination links to it
        packageDestinationMap() {
            return { ...this.levelStepMap, [this.sinkName]: this.sinkIdentifier };
        },

        resultsArray() {
            return Object.values(this.production.results).filter((o) =>
                Object.values(o).some((oo) => !oo.raw && !oo.imported)
            );
        },

        // every raw material, rendered as a Level-0 step (V100)
        rawSteps() {
            const raws = {};

            for (const level of Object.values(this.production.results)) {
                for (const [name, material] of Object.entries(level)) {
                    if (material.raw) {
                        raws[name] = material;
                    }
                }
            }

            return raws;
        },

        levelStepMap() {
            const ret = {};

            // raws live at Level 0
            for (const [rawIndex, name] of Object.keys(this.rawSteps).entries()) {
                ret[name] = `0.${this.stepLetter(rawIndex)}`;
            }

            for (const [levelIndex, level] of this.resultsArray.entries()) {
                for (const mat of Object.keys(level)) {
                    ret[mat] = `${levelIndex + 1}.${this.stepLetter(this.getStepIndex(level, mat))}`;

                    // if (Object.keys(mat.byproduct_outputs).length) {
                    //     ret
                    // }
                }
            }
            return ret;
        },
    },
};
</script>
