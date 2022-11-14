<template>
    <tbody :class="shouldFlash ? ['dark:bg-slate-500', 'bg-slate-300'] : []" class="transition-all duration-300">
        <tr class="border-t border-gray-200 dark:border-slate-700">
            <th class="text-lg">
                <div :id="identifier" class="flex flex-col items-center justify-center px-1">
                    {{ identifier }}
                </div>
            </th>
            <td class="whitespace-nowrap p-2 dark:text-slate-800">
                <div
                    class="flex flex-shrink-0 flex-grow flex-col items-center space-y-2 whitespace-nowrap rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg"
                >
                    <div class="flex w-full">
                        <cloud-image class="mr-2" :public-id="name" width="48" crop="scale" :alt="name" />

                        <div class="mr-2 flex flex-shrink-0 flex-grow flex-col space-y-2">
                            <div class="flex items-center justify-between font-semibold">
                                <span class="flex-1">{{ name }}</span>
                                <span v-if="overridden" class="rounded-lg bg-amber-300 px-2 py-1 text-xs">
                                    Override
                                </span>
                                <span
                                    class="cursor-pointer rounded bg-emerald-400 p-1 hover:bg-emerald-500"
                                    @click="$emit('toggle', name + '-' + (recipe?.description || 'base'))"
                                >
                                    {{ finished ? '✅' : '⬜' }}
                                </span>
                            </div>
                            <span class="font-light"> {{ qty }} </span>
                        </div>
                    </div>

                    <div
                        class="flex w-full flex-shrink-0 flex-grow flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg"
                    >
                        <span class="font-semibold"> Destination </span>
                        <div class="flex flex-shrink-0 flex-grow flex-col" v-for="(out_qty, mat) in material.outputs">
                            <div class="flex" v-if="mat !== 'final'">
                                <div class="mr-2 flex">
                                    <cloud-image
                                        class="inline-flex"
                                        :public-id="mat"
                                        width="32"
                                        crop="scale"
                                        :alt="mat"
                                    />
                                </div>
                                <span class="whitespace-nowrap text-xs">
                                    <a
                                        @click="flashDestination(levelStepMap[mat])"
                                        :href="`#${levelStepMap[mat]}`"
                                        class="text-sky-700"
                                        >{{ mat }} ({{ levelStepMap[mat] }}) ➡️
                                    </a>
                                    <br />
                                    {{ +out_qty.toFixed(4) }} ({{ Math.round((100 * 100 * out_qty) / qty) / 100 }}%)
                                </span>
                            </div>
                            <template v-else>
                                <div class="my-2 rounded bg-lime-300 p-2 text-xs font-bold">
                                    Output {{ +out_qty.toFixed(4) }} ({{
                                        Math.round((100 * 100 * out_qty) / qty) / 100
                                    }}%)
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </td>
            <td nowrap class="p-2 dark:text-slate-800">
                <div class="flex w-full flex-shrink-0 flex-grow">
                    <div
                        v-if="recipe && Object.keys(ingredients).length"
                        class="flex flex-shrink-0 flex-grow flex-col whitespace-nowrap rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg dark:text-slate-800"
                    >
                        <div
                            class="my-2 flex flex-shrink-0 flex-grow items-center"
                            v-for="(in_qty, ingr) in ingredients"
                        >
                            <cloud-image class="mr-2" :public-id="ingr" width="48" crop="scale" :alt="ingr" />
                            <div class="flex flex-grow flex-col whitespace-nowrap font-semibold">
                                {{ ingr }}
                                <template v-if="usesByproduct(ingr)"> (Used Byproduct) </template>
                                <span v-if="newImports[ingr]" class="rounded-lg bg-green-300 px-2 py-1 text-xs">
                                    Imported
                                </span>
                                <br />
                                <span class="font-light">
                                    {{ in_qty.$round4() }}
                                    <template v-if="usesByproduct(ingr)"> ({{ getByproductUsed(ingr) }}) </template>
                                </span>
                                <br />
                                <span class="font-light italic">
                                    {{ Math.round((100 * 100 * in_qty) / getDenominator(ingr)) / 100 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
            <td nowrap class="p-2 dark:text-slate-800">
                <div
                    v-if="Object.keys(production.byproducts).length"
                    class="flex w-full flex-shrink-0 flex-grow flex-col rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg"
                >
                    <span class="mb-1 font-semibold"> Byproducts </span>

                    <div
                        class="flex flex-shrink-0 flex-grow flex-col"
                        v-for="(qty, byproduct) in production.byproducts"
                    >
                        <div class="flex w-full">
                            <cloud-image class="mr-2" :public-id="byproduct" width="48" crop="scale" :alt="byproduct" />

                            <div class="mr-2 flex flex-shrink-0 flex-grow flex-col space-y-2">
                                <span class="font-semibold">
                                    {{ byproduct }}
                                    <span v-if="overridden" class="rounded-lg bg-amber-300 px-2 py-1 text-xs">
                                        Override
                                    </span>
                                </span>
                                <span class="font-light"> {{ +qty.toFixed(4) }} </span>
                            </div>
                        </div>
                        <div
                            v-if="Object.keys(byproductsUsed).includes(byproduct)"
                            class="mt-2 flex w-full flex-shrink-0 flex-grow flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg"
                        >
                            <span class="font-semibold"> Destination </span>
                            <div
                                class="flex flex-shrink-0 flex-grow flex-col"
                                v-for="(out_qty, mat) in byproductsUsed[byproduct]"
                            >
                                <div class="flex" v-if="mat !== 'final'">
                                    <div class="mr-2 flex">
                                        <cloud-image
                                            class="inline-flex"
                                            :public-id="mat"
                                            width="32"
                                            crop="scale"
                                            :alt="mat"
                                        />
                                    </div>
                                    <span class="whitespace-nowrap text-xs">
                                        {{ mat }}
                                        <br />
                                        {{ +out_qty.toFixed(4) }} ({{ Math.round((100 * 100 * out_qty) / qty) / 100 }}%)
                                    </span>
                                </div>
                                <template v-else>
                                    <div class="my-2 rounded bg-lime-300 p-2 text-xs font-bold">
                                        Output {{ +out_qty.toFixed(4) }} ({{
                                            Math.round((100 * 100 * out_qty) / qty) / 100
                                        }}%)
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
            <td class="p-2">
                <template v-if="recipe && recipes[name]">
                    <recipe-picker
                        @select="setNewSubFavorite"
                        :recipes="recipes[name]"
                        :selected="recipes[name].filter((o) => o.id === recipe.id)[0]"
                        :choices="choices"
                    ></recipe-picker>
                </template>
            </td>

            <td class="p-2">
                <template v-if="recipe && recipes[name]">
                    <select
                        @change="updateVariant"
                        v-model="selectedVariantName"
                        class="w-full rounded py-2 text-right shadow dark:bg-sky-800"
                    >
                        <option class="text-right" :value="mk" v-for="(opt, mk) in overview.details">
                            {{ opt.num_buildings }}x {{ mk }} @{{ opt.clock_speed }}% [{{ Math.round(opt.power_usage) }}
                            MW]
                        </option>
                    </select>
                    <div class="mt-2 flex items-center justify-end space-x-2">
                        <span>Variant</span>
                        <button
                            v-for="(opt, mk) in overview.details"
                            @click="updateVariant(mk)"
                            :class="[selectedVariantName === mk ? 'btn-gray' : 'btn-emerald']"
                            class="btn-sm"
                        >
                            {{ opt.variant }}
                        </button>
                    </div>
                    <div class="mt-2 flex items-center justify-end space-x-2">
                        <span>Max Clock</span>
                        <button
                            @click="updateClock('c100')"
                            :class="[selectedOverview === 'c100' ? 'btn-gray' : 'btn-emerald']"
                            class="btn-sm"
                        >
                            100%
                        </button>
                        <button
                            @click="updateClock('c150')"
                            :class="[selectedOverview === 'c150' ? 'btn-gray' : 'btn-emerald']"
                            class="btn-sm"
                        >
                            150%
                        </button>
                        <button
                            @click="updateClock('c200')"
                            :class="[selectedOverview === 'c200' ? 'btn-gray' : 'btn-emerald']"
                            class="btn-sm"
                        >
                            200%
                        </button>
                        <button
                            @click="updateClock('c250')"
                            :class="[selectedOverview === 'c250' ? 'btn-gray' : 'btn-emerald']"
                            class="btn-sm"
                        >
                            250%
                        </button>
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
                    <div class="mt-2 flex items-center justify-end space-x-2">
                        <span>Maximize Clock Speed</span>
                        <button
                            @click="maximizeOutput(false)"
                            class="btn-sm"
                            :disabled="!canMaximize"
                            :class="[canMaximize ? 'btn-emerald' : 'btn-gray opacity-20']"
                            v-show="!material.outputs?.final"
                        >
                            Output Extra
                        </button>
                        <button
                            @click="maximizeOutput(true)"
                            :disabled="!canMaximize"
                            :class="[canMaximize ? 'btn-emerald' : 'btn-gray opacity-20']"
                            class="btn-sm"
                        >
                            Scale Up Factory
                        </button>
                    </div>
                </template>
            </td>
        </tr>
        <tr v-if="recipe" v-show="diagrams">
            <td class="text-center" colspan="100">
                <build-diagram :footprint="footprint" />
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
        overviews: {},
        byproductsUsed: {},
        stepIndex: {},
        levelIndex: {},
        levelStepMap: {},
        finished: {},
    },

    mounted() {
        setTimeout(this.emit, 500);

        this.Bus.on('flash', ({ dest }) => {
            if (dest === this.identifier) {
                console.log('flashing now');
                this.shouldFlash = true;
                setTimeout(() => (this.shouldFlash = false), 1200);
            }
        });
    },

    data() {
        let key = `${this.production.recipe.product.name}|${
                this.production.recipe.description || this.production.recipe.product.name
            }`,
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
            shouldFlash: false,
        };
    },

    computed: {
        overview() {
            return this.overviews[this.key].overviews[this.selectedOverview];
        },

        footprint() {
            return this.overview.details[this.selectedVariantName].footprint;
        },

        stepLetter() {
            return Array.from('ABCDEFGHIJKLMNOPQRSTUVWXYZ')[this.stepIndex];
        },

        identifier() {
            return this.levelIndex + '.' + this.stepLetter;
        },

        canMaximize() {
            return this.overview.selected_variant.clock_speed < this.overview.selected_variant.max_clock_speed;
        },
    },

    methods: {
        maximizeOutput(scale) {
            let qty = this.overview.qty,
                num_buildings = this.overview.selected_variant.num_buildings,
                max_clock_speed = this.overview.selected_variant.max_clock_speed,
                base_per_min = this.recipe.base_per_min,
                newQty = ((base_per_min * num_buildings * max_clock_speed) / 100).$round4(),
                ratio = newQty / qty,
                delta = newQty - qty;

            console.log({
                qty,
                max_clock_speed,
                newQty,
                ratio,
                delta,
            });

            // add additional output
            if (!scale) {
                console.log('Adding Output', {
                    product: this.name,
                    recipe: this.recipe,
                    qty: delta,
                });

                this.Bus.emit('AddOutput', {
                    product: this.name,
                    recipe: this.recipe,
                    qty: delta,
                });
            }

            // scale the entire factory
            else {
                this.Bus.emit('ScaleOutputs', {
                    ratio,
                });
            }
        },

        flashDestination(dest) {
            console.log('emitting flash');
            this.Bus.emit('flash', { dest });
        },

        usesByproduct(ingr) {
            return this.byproductsUsed.hasOwnProperty(ingr) && this.byproductsUsed[ingr].hasOwnProperty(this.name);
        },

        getByproductUsed(ingr) {
            return this.byproductsUsed?.[ingr]?.[this.name]?.$round4() || 0;
        },

        getDenominator(name) {
            let byp = this.getByproductUsed(name);

            return byp + (+this.allMaterials?.[name] || 0);
        },

        setNewSubFavorite({ recipe }) {
            this.$emit('setNewSubFavorite', { recipe });
        },

        updateClock(clock) {
            this.selectedOverview = clock;
            store.setItem(`${this.key}.clock`, this.selectedOverview);
            this.emit();
        },

        updateVariant(variant = null) {
            if (typeof variant === 'string') {
                this.selectedVariantName = variant;
            }
            store.setItem(`${this.key}.selected_variant_name`, this.selectedVariantName);
            this.emit();
        },

        emit() {
            this.Bus.emit('UpdateOverviews', {
                key: this.key,
                clock: this.selectedOverview,
                selected_variant_name: this.selectedVariantName,
            });
        },
    },
};
</script>
