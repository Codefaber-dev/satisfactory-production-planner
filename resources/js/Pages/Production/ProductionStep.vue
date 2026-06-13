<template>
    <tbody
        :class="shouldFlash ? ['dark:bg-slate-500', 'bg-slate-300'] : []"
        class="mb-4 block rounded-lg border border-gray-300 transition-all duration-300 dark:border-slate-700 lg:mb-0 lg:table-row-group lg:rounded-none lg:border-0"
    >
        <tr class="block border-t border-gray-200 dark:border-slate-700 lg:table-row">
            <th class="block py-1 text-lg lg:table-cell lg:py-0">
                <div :id="identifier" class="flex flex-col items-center justify-center px-1">
                    {{ identifier }}
                </div>
            </th>
            <td class="block p-2 dark:text-slate-800 lg:table-cell lg:whitespace-nowrap">
                <div
                    class="flex shrink-0 grow flex-col items-center space-y-2 rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg lg:whitespace-nowrap"
                >
                    <div class="flex w-full">
                        <cloud-image class="mr-2" :public-id="name" width="48" crop="scale" :alt="name" />

                        <div class="mr-2 flex shrink-0 grow flex-col space-y-2">
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
                            <span class="font-light"> {{ formatQty(qty) }} </span>
                        </div>
                    </div>

                    <div
                        class="flex w-full shrink-0 grow flex-col space-y-1 rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg"
                    >
                        <span class="font-semibold"> Destination </span>
                        <div
                            class="flex shrink-0 grow flex-col space-y-1"
                            v-for="(out_qty, mat) in material.outputs"
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
                                <span class="text-xs lg:whitespace-nowrap">
                                    <a
                                        @click="flashDestination(levelStepMap[mat])"
                                        :href="`#${levelStepMap[mat]}`"
                                        class="text-sky-700"
                                        >{{ mat }} ({{ levelStepMap[mat] }}) ➡️
                                    </a>
                                    <br />
                                    {{ formatQtyWithPercentage(out_qty, qty) }}
                                </span>
                            </div>
                            <template v-else>
                                <div class="my-2 rounded bg-lime-300 p-2 text-xs font-bold">
                                    Output {{ formatQtyWithPercentage(out_qty, qty) }}
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </td>
            <td class="block p-2 dark:text-slate-800 lg:table-cell lg:whitespace-nowrap">
                <div class="flex w-full shrink-0 grow">
                    <div
                        v-if="recipe && Object.keys(ingredients).length"
                        class="flex shrink-0 grow flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg dark:text-slate-800 lg:whitespace-nowrap"
                    >
                        <div
                            class="my-2 flex shrink-0 grow items-center"
                            v-for="(in_qty, ingr) in ingredients"
                        >
                            <cloud-image class="mr-2" :public-id="ingr" width="48" crop="scale" :alt="ingr" />
                            <div class="flex grow flex-col font-semibold lg:whitespace-nowrap">
                                {{ ingr }}
                                <template v-if="usesByproduct(ingr)"> (Used Byproduct) </template>
                                <span v-if="newImports[ingr]" class="rounded-lg bg-green-300 px-2 py-1 text-xs">
                                    Imported
                                </span>
                                <span
                                    :class="
                                        rateModified
                                            ? 'self-start rounded bg-amber-300 px-2 py-0.5 font-semibold'
                                            : 'font-light'
                                    "
                                >
                                    {{ formatQty(in_qty) }}<template v-if="rateModified"> *</template>
                                    <template v-if="usesByproduct(ingr)"> ({{ getByproductUsed(ingr) }}) </template>
                                </span>
                                <span class="font-light italic">
                                    {{ Math.round((100 * 100 * in_qty) / getDenominator(ingr)) / 100 }}%
                                </span>
                            </div>
                        </div>

                        <details v-if="rateModified" class="mt-2 rounded border border-amber-500 bg-amber-100 p-2 text-xs">
                            <summary class="cursor-pointer font-semibold">* Net rate explained</summary>
                            <div class="mt-1 flex flex-col space-y-1">
                                <span v-for="(in_qty, ingr) in ingredients" :key="ingr">
                                    {{ ingr }}: {{ rateBreakdown(ingr, in_qty) }}
                                </span>
                            </div>
                        </details>
                    </div>
                </div>
            </td>
            <td
                class="p-2 dark:text-slate-800 lg:table-cell lg:whitespace-nowrap"
                :class="Object.keys(production.byproducts).length ? 'block' : 'hidden'"
            >
                <div
                    v-if="Object.keys(production.byproducts).length"
                    class="flex w-full shrink-0 grow flex-col rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg"
                >
                    <span class="mb-1 font-semibold"> Byproducts </span>

                    <div
                        class="flex shrink-0 grow flex-col"
                        v-for="(qty, byproduct) in production.byproducts"
                    >
                        <div class="flex w-full">
                            <cloud-image class="mr-2" :public-id="byproduct" width="48" crop="scale" :alt="byproduct" />

                            <div class="mr-2 flex shrink-0 grow flex-col space-y-2">
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
                            class="mt-2 flex w-full shrink-0 grow flex-col space-y-1 rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg"
                        >
                            <span class="font-semibold"> Destination </span>
                            <div
                                class="flex shrink-0 grow flex-col"
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
                                    <span class="text-xs lg:whitespace-nowrap">
                                        <a
                                            @click="flashDestination(levelStepMap[mat])"
                                            :href="`#${levelStepMap[mat]}`"
                                            class="text-sky-700"
                                            >{{ mat }} ({{ levelStepMap[mat] }}) ➡️
                                        </a>
                                        <br />
                                        {{ formatQtyWithPercentage(out_qty, qty) }}
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
            <td class="p-2 lg:table-cell" :class="recipe && recipes[name] ? 'block' : 'hidden'">
                <template v-if="recipe && recipes[name]">
                    <recipe-picker
                        @select="setNewSubFavorite"
                        :recipes="recipes[name]"
                        :selected="recipes[name].filter((o) => o.id === recipe.id)[0]"
                        :choices="choices"
                    ></recipe-picker>
                </template>
            </td>

            <td class="block p-2 lg:table-cell">
                <template v-if="recipe && recipes[name]">
                    <span class="flex flex-wrap items-center justify-end gap-2">
                        {{ overview.details[selectedVariantName].num_buildings }}x {{ selectedVariantName }} @{{ overview.details[selectedVariantName].clock_speed }}% [{{ Math.round(overview.details[selectedVariantName].power_usage) }}
                            MW]
                    </span>
<!--                    <select-->
<!--                        @change="updateVariant"-->
<!--                        v-model="selectedVariantName"-->
<!--                        class="w-full rounded py-2 text-right shadow dark:bg-sky-800"-->
<!--                    >-->
<!--                        <option class="text-right" :value="mk" v-for="(opt, mk) in overview.details">-->
<!--                            {{ opt.num_buildings }}x {{ mk }} @{{ opt.clock_speed }}% [{{ Math.round(opt.power_usage) }}-->
<!--                            MW]-->
<!--                        </option>-->
<!--                    </select>-->
<!--                    <div class="mt-2 flex flex-wrap items-center justify-end gap-2">-->
<!--                        <span>Variant</span>-->
<!--                        <button-->
<!--                            v-for="(opt, mk) in overview.details"-->
<!--                            @click="updateVariant(mk)"-->
<!--                            :class="[selectedVariantName === mk ? 'btn-gray' : 'btn-emerald']"-->
<!--                            class="btn-sm"-->
<!--                        >-->
<!--                            {{ opt.variant }}-->
<!--                        </button>-->
<!--                    </div>-->
                    <div class="mt-2 flex flex-wrap items-center justify-end gap-2">
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
                    <div class="mt-2 flex flex-wrap items-center justify-end gap-2">
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
                    <div v-if="maxSlots > 0" class="mt-2 flex flex-wrap items-center justify-end gap-2">
                        <span>Somersloops</span>
                        <button
                            v-for="n in maxSlots + 1"
                            :key="n - 1"
                            @click="setSomersloopSlots(n - 1)"
                            :class="[currentSomersloopSlots === n - 1 ? 'btn-gray' : 'btn-emerald']"
                            class="btn-sm"
                        >
                            {{ n - 1 }}
                        </button>
                    </div>
                </template>
            </td>
        </tr>
        <tr class="block lg:table-row" v-if="recipe" v-show="diagrams">
            <td class="block text-center lg:table-cell" colspan="100">
                <build-diagram :footprint="footprint" />
            </td>
        </tr>
    </tbody>
</template>
<script>
import BuildDiagram from '@/Pages/Production/BuildDiagram';
import RecipePicker from '@/Components/RecipePicker';
import store from '@/store';
import { DESIGNER_DIMS, groupedFootprint } from '@/blueprintFootprint';
import CloudImage from '../../Components/CloudImage.vue';

export default {
    name: 'ProductionStep',
    components: { CloudImage, BuildDiagram, RecipePicker },
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
        appliedEven: {
            type: Boolean,
            default: false,
        },
    },

    mounted() {
        setTimeout(this.emit, 500);

        this.Bus.on('flash', ({ dest }) => {
            if (dest === this.identifier) {
                console.log('flashing now');
                this.shouldFlash = true;
                setTimeout(() => {
                    this.shouldFlash = false;
                }, 1200);
            }
        });

        this.Bus.emit('RegisterQuickNav', {
            name: this.name,
            identifier: this.identifier,
        });
    },

    data() {
        const key = `${this.production.recipe.product.name}|${
            this.production.recipe.description || this.production.recipe.product.name
        }`;
        const clock = store.getItem(`${key}.clock`, this.overviews?.[key]?.clock);
        const variant = store.getItem(`${key}.selected_variant_name`, this.overviews?.[key]?.selected_variant_name);

        return {
            key,
            selectedOverview: clock,
            selectedVariantName: variant,
            recipe: this.production.recipe,
            qty: this.production.qty,
            overridden: this.production.overridden,
            shouldFlash: false,
        };
    },

    computed: {
        ingredients() {
            return this.production.ingredients;
        },

        overview() {
            return this.overviews[this.key].overviews[this.selectedOverview];
        },

        footprint() {
            const base = this.overview.details[this.selectedVariantName].footprint;
            const groupSize = this.buildingMultiples[this.overview.building];

            if (!groupSize) {
                return base;
            }

            return groupedFootprint(
                base,
                groupSize,
                DESIGNER_DIMS[this.designerMk] ?? DESIGNER_DIMS.mk1,
                this.appliedEven
            );
        },

        stepLetter() {
            return Array.from('ABCDEFGHIJKLMNOPQRSTUVWXYZ')[this.stepIndex];
        },

        identifier() {
            return `${this.levelIndex}.${this.stepLetter}`;
        },

        canMaximize() {
            return this.overview.selected_variant.clock_speed < this.overview.selected_variant.max_clock_speed;
        },

        maxSlots() {
            const first = Object.values(this.overview.details)[0];
            return first ? (first.max_slots ?? 0) : 0;
        },

        currentSomersloopSlots() {
            return Number.parseInt(this.somersloopSlots[this.key] ?? 0) || 0;
        },

        sloopFactor() {
            if (this.maxSlots <= 0 || this.currentSomersloopSlots <= 0) {
                return 1;
            }
            return 1 / (1 + this.currentSomersloopSlots / this.maxSlots);
        },

        rateModified() {
            return this.costMultiplier !== 1 || this.sloopFactor !== 1;
        },
    },

    methods: {
        setSomersloopSlots(slots) {
            this.Bus.emit('UpdateSomersloopSlots', { key: this.key, slots });
        },

        maximizeOutput(scale) {
            const qty = this.overview.qty;
            const num_buildings = this.overview.selected_variant.num_buildings;
            const max_clock_speed = this.overview.selected_variant.max_clock_speed;
            const base_per_min = this.recipe.base_per_min;
            const newQty = ((base_per_min * num_buildings * max_clock_speed) / 100).$round4();
            const ratio = newQty / qty;
            const delta = newQty - qty;

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
            return Object.hasOwn(this.byproductsUsed, ingr) && Object.hasOwn(this.byproductsUsed[ingr], this.name);
        },

        getByproductUsed(ingr) {
            return this.byproductsUsed?.[ingr]?.[this.name]?.$round4() || 0;
        },

        getTotalByproductUsed(ingr) {
            return Object.values(this.byproductsUsed?.[ingr] || {}).sum();
        },

        getDenominator(name) {
            const byp = this.getTotalByproductUsed(name);

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

        formatPercentage(num, den) {
            return Math.round((100 * 100 * num) / den) / 100;
        },

        formatQtyWithPercentage(num, den) {
            return `${this.formatQty(num)} (${this.formatPercentage(num, den)}%)`;
        },

        formatQty(num) {
            return `${+num.$round4()} per min`;
        },

        nominalRate(ingr) {
            const found = (this.recipe?.ingredients || []).find((o) => o.name === ingr);

            if (!found) {
                return null;
            }

            return (this.qty / this.recipe.base_per_min) * found.pivot.base_qty;
        },

        rateBreakdown(ingr, netQty) {
            const nominal = this.nominalRate(ingr);

            if (nominal === null) {
                return this.formatQty(netQty);
            }

            const parts = [`${+nominal.$round4()}`];

            if (this.costMultiplier !== 1) {
                parts.push(`× ${this.costMultiplier} (cost mult)`);
            }

            if (this.sloopFactor !== 1) {
                parts.push(`× ${+this.sloopFactor.$round4()} (sloops)`);
            }

            return `${parts.join(' ')} = ${this.formatQty(netQty)}`;
        },
    },
};
</script>
