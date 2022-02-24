<template>
    <tbody>
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
                    <div
                        class='flex w-full flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg'
                    >
                        <span class='font-semibold'>
                            Destination
                        </span>
                        <div class='flex flex-col' v-for='(out_qty, mat) in material.outputs'>
                            <div class='flex' v-if='mat !== "final"'>
                                <cloud-image class='mr-2 inline-flex'
                                             :public-id='mat'
                                             width='32' crop='scale'
                                             :alt='mat' />
                                <span class='text-xs'>
                                    {{ mat }}
                                    <br>
                                    {{ +out_qty.toFixed(4) }} per min
                                    ({{ Math.round((100 * 100 * out_qty) / qty) / 100 }}%)
                                </span>
                            </div>
                            <template v-else>
                                <div class='text-xs font-bold p-2 my-2 rounded bg-lime-300'>
                                    Output {{ out_qty }} per min
                                    ({{ Math.round((100 * 100 * out_qty) / qty) / 100 }}%)
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </td>
        <td nowrap class='p-2'>
            <div v-if='recipe  && Object.keys(ingredients).length'
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
                            {{ Math.round((100 * 100 * in_qty) / allMaterials[name]) / 100 }}%
                        </span>
                    </span>
                </div>
            </div>
        </td>
        <td class='p-2'>
            <template v-if='recipe && recipes[name]'>
                <recipe-picker
                    @select='setNewSubFavorite'
                    :recipes='recipes[name]'
                    :selected='recipes[name].filter(o => o.id === recipe.id)[0]'
                    :choices='choices'
                ></recipe-picker>
            </template>
        </td>

        <td class='p-2'>
            <template v-if='recipe && recipes[name]'>
                <select @change='updateVariant' v-model='selectedVariantName'
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
                <div class='mt-2 flex space-x-2 items-center justify-end'>
                    <span>Variant</span>
                    <button v-for='(opt,mk) in overview.details' @click='updateVariant(mk)' :class='[selectedVariantName === mk ? "btn-gray" : "btn-emerald"]' class='btn-sm'>
                        {{ opt.variant }}
                    </button>
                </div>
                <div class='mt-2 flex space-x-2 items-center justify-end'>
                    <span>Max Clock</span>
                    <button @click='updateClock("c100")' :class='[selectedOverview === "c100" ? "btn-gray" : "btn-emerald"]' class='btn-sm'>100%</button>
                    <button @click='updateClock("c150")' :class='[selectedOverview === "c150" ? "btn-gray" : "btn-emerald"]' class='btn-sm'>150%</button>
                    <button @click='updateClock("c200")' :class='[selectedOverview === "c200" ? "btn-gray" : "btn-emerald"]' class='btn-sm'>200%</button>
                    <button @click='updateClock("c250")' :class='[selectedOverview === "c250" ? "btn-gray" : "btn-emerald"]' class='btn-sm'>250%</button>
                </div>
<!--                <select class='w-full rounded py-2 text-right shadow dark:bg-sky-800 mt-2' v-model='overview'>-->
<!--                    <option :value='overviews["c100"]'>-->
<!--                        No Overclock-->
<!--                    </option>-->
<!--                    <option :value='overviews["c150"]'>-->
<!--                        1 Power Shard-->
<!--                    </option>-->
<!--                    <option :value='overviews["c200"]'>-->
<!--                        2 Power Shards-->
<!--                    </option>-->
<!--                    <option :value='overviews["c250"]'>-->
<!--                        3 Power Shards-->
<!--                    </option>-->
<!--                </select>-->
            </template>
        </td>
    </tr>
    <tr v-if='recipe' v-show='diagrams'>
        <td class='text-center' colspan='100'>
            <build-diagram
                :footprint='footprint' />
        </td>
    </tr>
    </tbody>
</template>
<script>
import BuildDiagram from '@/Pages/Production/BuildDiagram';
import RecipePicker from '@/Components/RecipePicker';
import store from '@/store';

export default {
    name: 'ProductionStep',
    components: { BuildDiagram, RecipePicker },
    props: {
        choices: {},
        diagrams: {},
        name: {},
        newImports: {},
        recipes: {},
        production: {},
        allMaterials: {},
        material: {},
        overviews: {}
    },

    mounted() {
        setTimeout(this.emit,100);
    },

    data() {
        let key = `${this.production.recipe.product.name}|${this.production.recipe.description||this.production.recipe.product.name}`,
            clock = store.getItem(`${key}.clock`, this.overviews[key].clock),
            variant = store.getItem(`${key}.selected_variant_name`, this.overviews[key].selected_variant_name);

        return {
            key,
            selectedOverview: clock,
            selectedVariantName: variant,
            recipe: this.production.recipe,
            ingredients: this.production.ingredients,
            qty: this.production.qty,
            overridden: this.production.overridden,
        }
    },

    computed: {
        overview() {
            return this.overviews[this.key].overviews[this.selectedOverview];
        },

        footprint() {
            return this.overview.details[this.selectedVariantName].footprint
        },
    },

    methods: {
        setNewSubFavorite({recipe}) {
            this.$emit('setNewSubFavorite',{recipe});
        },

        updateClock(clock) {
            this.selectedOverview = clock;
            store.setItem(`${this.key}.clock`,this.selectedOverview);
            this.emit();
        },

        updateVariant(variant=null) {
            if (typeof variant === 'string') {
                this.selectedVariantName = variant;
            }
            store.setItem(`${this.key}.selected_variant_name`,this.selectedVariantName);
            this.emit();
        },

        emit() {
            this.Bus.emit('UpdateOverviews', {
                key: this.key,
                clock: this.selectedOverview,
                selected_variant_name: this.selectedVariantName
            })
        }
    }
};
</script>
