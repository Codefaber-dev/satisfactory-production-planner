<template>
    <div
        class='flex flex-1 flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900'
    >
        <div
            class='rounded-t-lg bg-gray-900 p-4 text-center text-xl font-semibold text-white dark:bg-sky-700'
        >
            Production Steps
        </div>
        <table>
            <tr>
                <th class='font-semibold'>Ingredient</th>
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
            <template v-for='(level, index) in production.results'>
                <template v-if='index>1'>
                    <tr>
                        <th class='bg-blue-200 py-2 dark:bg-slate-800' colspan='100'>
                            Tier {{ index-1 }}
                        </th>
                    </tr>

                    <template v-for='(material,name) in level'>
                        <tbody
                            v-if='index == 1'
                            v-show='
                                !hideCompleted ||
                                !productionChecks[name]
                            '
                            :class="[productionChecks[name] ? 'opacity-25' : 'opacity-100']"
                        >
                        <tr class='border-t border-gray-200 dark:border-slate-700'>
                            <td class='p-2'>
                                <div
                                    @click="$emit('toggle',name)"
                                    class='flex cursor-pointer items-center rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg dark:text-slate-800'
                                >
                                    <cloud-image class='mr-2' :public-id='name' width='48' crop='scale' :alt='name' />

                                    <div class='flex w-full flex-col space-y-2'>
                                        <span class='font-semibold'>
                                            {{ name }}
                                        </span>
                                        <span class='font-light'>
                                            {{ material.total }} per min
                                        </span>
                                        <div
                                            class='flex w-full flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg'
                                        >
                                            <span class='font-semibold'>
                                                Destination
                                            </span>
                                            <span v-for='(out_qty, mat) in material.outputs'>
                                                <cloud-image class='mr-2 inline-flex' :public-id='mat' width='32' crop='scale' :alt='mat' />
                                                <span class='text-xs'>
                                                    {{ mat }}
                                                    ({{ Math.round((100 * 100 * out_qty) / material.total) / 100 }}%)
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>

                        <!-- level > 1 -->
                        <tbody
                            v-else
                            v-for='{recipe,qty,ingredients,variant,overview,overridden,imported} in material.production'
                            v-show="
                                !imported && (
                                    !hideCompleted ||
                                    !productionChecks[name + '-' + (recipe?.description || 'base')] )
                            "
                            :class="[productionChecks[name + '-' + (recipe?.description || 'base')] ? 'opacity-25' : 'opacity-100']"
                        >
                        <tr class='border-t border-gray-200 dark:border-slate-700'>
                            <td class='p-2'>
                                <div @click="$emit('toggle',name + '-' + (recipe?.description || 'base'))"
                                    class='flex cursor-pointer items-center rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg dark:text-slate-800'
                                >
                                    <cloud-image
                                        class='mr-2'
                                        :public-id='name'
                                        width='48'
                                        crop='scale'
                                        :alt='name'
                                    />

                                    <div class='flex w-full flex-col space-y-2'>
                                        <span class='font-semibold'>
                                            {{ name }}
                                            <span v-if='overridden'
                                                  class='rounded-lg bg-amber-300 px-2 py-1 text-xs'>
                                                Override
                                            </span>

                                        </span>
                                        <span class='font-light'>
                                            {{ qty }} per min
                                        </span>
                                        <div v-if='name !== newProduct.name'
                                             class='flex w-full flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg'
                                        >
                                            <span class='font-semibold'>
                                                Destination
                                            </span>
                                            <span v-for='(out_qty, mat) in material.outputs'>
                                                <cloud-image class='mr-2 inline-flex'
                                                             :public-id='mat'
                                                             width='32' crop='scale'
                                                             :alt='mat' />
                                                <span class='text-xs'>
                                                    {{ mat }}
                                                    ({{ Math.round((100 * 100 * out_qty) / qty) / 100 }}%)
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td nowrap class='p-2'>
                                <div v-if='recipe && Object.keys(ingredients).length'
                                     class='rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg dark:text-slate-800'>
                                    <div
                                        class='my-2 flex items-center'
                                        v-for='(in_qty, name) in ingredients'
                                    >
                                        <cloud-image class='mr-2' :public-id='name' width='48'
                                                     crop='scale' :alt='name' />
                                        <span class='font-semibold'>
                                            {{ name }}
                                            <span v-if='newImports[name]'
                                                  class='rounded-lg bg-green-300 px-2 py-1 text-xs'>
                                                Imported
                                            </span>
                                            <br />
                                            <span class='font-light'>
                                                {{ Math.round(10000 * in_qty) / 10000 }}
                                                per min
                                            </span>
                                            <br />
                                            <span class='font-light italic'>
                                                {{ Math.round((100 * 100 * in_qty) / production.all_materials[name]) / 100
                                                }}%
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class='p-2'>
                                <template v-if='recipe'>
                                    <template v-if='recipes[name]'>
                                        <!-- material is end product -->
                                        <template v-if='name === newProduct.name'>
                                            <div
                                                class='flex flex-col'
                                            >
                                                <recipe-picker @select='selectNewRecipe'
                                                               :recipes='recipes[newProduct.name]'
                                                               :selected='newRecipe'
                                                ></recipe-picker>
                                            </div>
                                        </template>

                                        <!-- everything else -->
                                        <template v-else>
                                            <recipe-picker
                                                @select='selectNewSubRecipe'
                                                :recipes='recipes[name]'
                                                :selected='recipes[name].filter(o => o.id === recipe.id)[0]'
                                            ></recipe-picker>
                                        </template>
                                    </template>
                                </template>
                            </td>

                            <td class='p-2'>
                                <template v-if='recipe && recipes[name]'>
                                    <select v-model='overview.selected_variant_name'
                                            class='w-full rounded py-2 text-right shadow dark:bg-sky-800'
                                    >
                                        <option
                                            class='text-right'
                                            :value='mk'
                                            v-for='(opt, mk) in overview.details'>
                                            {{ opt.num_buildings }}x {{ mk }} @{{ opt.clock_speed }}%
                                            [{{ Math.round(opt.power_usage) }} MW]
                                        </option>
                                    </select>
                                </template>
                            </td>
                        </tr>
                        <tr v-if='recipe' v-show='diagrams'>
                            <td class='text-center' colspan='100'>
                                <build-diagram
                                    :footprint='overview.details[overview.selected_variant_name].footprint' />
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </template>
            </template>
        </table>
    </div>
</template>
<script>
import BuildDiagram from '@/Pages/Production/BuildDiagram';
import RecipePicker from '@/Components/RecipePicker';

export default {
    name: 'production-steps',
    components: { BuildDiagram, RecipePicker },
    props: {
        diagrams: {},
        hideCompleted: {},
        newImports: {},
        newProduct: {},
        newRecipe: {},
        production: {},
        productionChecks: {},
        recipes: {},
        toggleProductionCheck: {},
    },

    methods : {
        helpOverride() {
            alert('Your chosen recipe was overridden to avoid a circular dependency.');
        },

        selectNewRecipe(recipe) {
            this.$emit('SelectNewRecipe',recipe);
        },

        selectNewSubRecipe(recipe) {
            this.$emit('SelectNewSubRecipe',recipe)
        }
    }
};
</script>
