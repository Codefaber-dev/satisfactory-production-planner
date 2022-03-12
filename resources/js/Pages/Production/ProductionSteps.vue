<template>
    <div
        class='flex flex-1 flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900'
    >
        <div
            class='flex space-x-4 items-center justify-center rounded-t-lg bg-gray-900 p-4 text-xl font-semibold text-white dark:bg-sky-700'
        >
            <span>Production Steps</span>
            <button
                @click="$emit('toggleDiagrams')"
                class="btn btn-emerald"
            >
                {{ diagrams ? "✅" : "⬜" }}
                Toggle Diagrams
            </button>
            <button
                @click="$emit('toggleEvenRows')"
                class="btn btn-emerald"
            >
                {{ even ? "✅" : "⬜" }}
                Force Even Rows
            </button>
        </div>
        <table>
            <tr>
                <th class='font-semibold'>Ingredient (Click To Toggle)</th>
                <th class='font-semibold'>Inputs</th>
                <th class='font-semibold'>Recipe</th>
                <th class='font-semibold'>Production</th>
            </tr>
            <tr v-show='Object.values(productionChecks).some(o => o)'>
                <th
                    class='bg-blue-200 p-2 text-center dark:bg-sky-600'
                    colspan='100'
                > {{ hideCompleted ? 'Hiding' : 'Showing' }}
                    {{ Object.values(productionChecks).filter(o => o).length }}
                    completed rows
                    <button @click='hideCompleted = !hideCompleted'
                            class='rounded bg-emerald-500 px-4 py-2 text-sm hover:bg-emerald-600 focus:bg-emerald-700'
                    >
                        Toggle Completed
                    </button>
                </th>
            </tr>
            <template v-for='(level, index) in resultsArray'>
                <tr>
                    <th class='bg-blue-200 py-2 dark:bg-slate-800' colspan='100'>
                        Level {{ index+1 }}
                    </th>
                </tr>

                <template v-for='(material,name) in level'>
                    <ProductionStep
                        v-for='prod in material.production'
                        v-show="!prod.imported && (
                             !hideCompleted ||
                             !productionChecks[name + '-' + (prod.recipe?.description || 'base')] )"
                        :class="[productionChecks[name + '-' + (prod.recipe?.description || 'base')] ? 'opacity-25' : 'opacity-100']"
                        :choices='choices' :diagrams='diagrams' :material='material'
                        :name='name' :new-imports='newImports' :production='prod'
                        :recipes='recipes' :all-materials='production.all_materials'
                        :overviews='overviews'
                      @toggle='toggle'
                      @setNewSubFavorite='setNewSubFavorite' />
                </template>
            </template>
        </table>
    </div>
</template>
<script>
import ProductionStep from '@/Pages/Production/ProductionStep';

export default {
    name: 'production-steps',
    components: { ProductionStep},
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
        overviews: {},
    },

    methods: {
        helpOverride() {
            alert('Your chosen recipe was overridden to avoid a circular dependency.');
        },

        setNewSubFavorite({ recipe }) {
            this.$emit('setNewSubFavorite', { recipe })
        },

        toggle(material) {
            this.$emit('toggle',material);
        }
    },

    computed: {
        resultsArray() {
            return Object.values(this.production.results).filter(o => Object.values(o).some(oo => !oo.raw && !oo.imported));
        },
    }
};
</script>
