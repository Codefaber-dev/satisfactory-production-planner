<template>
    <div
        class="flex flex-1 flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900"
    >
        <div
            class="flex flex-wrap items-center justify-center gap-2 rounded-t-lg bg-gray-900 p-4 text-xl font-semibold text-white dark:bg-sky-700"
        >
            <span class="w-full text-center sm:w-auto">Production Steps</span>
            <button @click="$emit('toggleDiagrams')" class="btn btn-emerald">
                {{ diagrams ? '✅' : '⬜' }}
                Toggle Diagrams
            </button>
            <button @click="$emit('toggleEvenRows')" class="btn btn-emerald">
                {{ even ? '✅' : '⬜' }}
                Force Even Rows
            </button>
            <button
                v-text="
                    `Limit Belt Rates: ${
                        speedLimit === 'both' ? 'Inputs & Outputs' : speedLimit === 'inputs' ? 'Inputs' : 'Outputs'
                    }`
                "
                @click="$emit('toggleSpeedLimit')"
                class="btn btn-emerald"
            ></button>
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
            <!-- Level 0: raws render first as steps (V72), source-mode controls on the step (V73) -->
            <template v-if="Object.keys(rawSteps).length">
                <tr class="block lg:table-row">
                    <th class="block bg-blue-200 py-2 text-2xl dark:bg-slate-800 lg:table-cell" colspan="100">Level 0</th>
                </tr>
                <template v-for="(material, name) in rawSteps">
                    <ProductionStep
                        v-for="prod in material.production"
                        :key="name + '-raw-' + (prod.recipe?.description || 'leaf')"
                        :level-index="0"
                        :level-step-map="levelStepMap"
                        :step-index="rawStepIndex(name)"
                        :choices="choices"
                        :diagrams="diagrams"
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
                <tr class="block lg:table-row">
                    <th class="block bg-blue-200 py-2 text-2xl dark:bg-slate-800 lg:table-cell" colspan="100">Level {{ index + 1 }}</th>
                </tr>

                <template v-for="(material, name) in level">
                    <ProductionStep
                        v-for="prod in material.production.filter((o) => o.recipe && !material.raw)"
                        v-show="
                            !prod.imported &&
                            (!hideCompleted || !productionChecks[name + '-' + (prod.recipe?.description || 'base')])
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
                        :diagrams="diagrams"
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
                <tr class="block lg:table-row">
                    <th class="block bg-emerald-200 py-2 text-2xl dark:bg-emerald-900 lg:table-cell" colspan="100">
                        Recycling (AWESOME Sink)
                    </th>
                </tr>
                <RecycleStep
                    v-for="(step, i) in recycleSteps"
                    :key="`recycle-step-${i}`"
                    data-test="recycle-step"
                    :step="step"
                    :identifier="recycleIdentifier(i)"
                    :step-map="recycleStepMap"
                    :sink-identifier="sinkIdentifier"
                    :diagrams="diagrams"
                    @flashDestination="flashDestination"
                />
            </template>
        </table>
        </div>
    </div>
</template>
<script>
import ProductionStep from '@/Pages/Production/ProductionStep';
import RecycleStep from '@/Pages/Production/RecycleStep';

export default {
    name: 'production-steps',
    components: { ProductionStep, RecycleStep },
    props: {
        diagrams: {},
        hideCompleted: {},
        newImports: {},
        newProduct: {},
        newRecipe: {},
        production: {},
        productionChecks: {},
        recipes: {},
        choices: {},
        even: {},
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
        speedLimit: {
            type: String,
            default: 'both',
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
            console.log({ level, name });
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
    },

    computed: {
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

        resultsArray() {
            return Object.values(this.production.results).filter((o) =>
                Object.values(o).some((oo) => !oo.raw && !oo.imported)
            );
        },

        // every raw material, rendered as a Level-0 step (V72)
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
                }
            }
            return ret;
        },
    },
};
</script>
