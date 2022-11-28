<template>
    <div
        class="flex flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900"
    >
        <div class="rounded-t-lg bg-gray-900 p-4 text-center text-xl font-semibold text-white dark:bg-sky-700">
            Summary
        </div>
        <table class="">
            <template v-if="Object.keys(rawMaterials).length">
                <tr class="bg-sky-300 dark:bg-sky-800">
                    <th class="text-lg font-semibold" colspan="3">Raw Materials (per min)</th>
                </tr>
                <tr v-for="(props, name) in rawMaterials">
                    <td colspan="2" class="p-2">
                        <cloud-image
                            class="inline-flex"
                            :public-id="`${name}.png`"
                            crop="scale"
                            quality="100"
                            width="32"
                            :alt="name"
                        />
                        {{ name }}
                    </td>
                    <td class="p-2 text-right">
                        <div class="flex justify-end space-x-2">
                            <button
                                @click="disabledRawMaterials[name] = !!!disabledRawMaterials[name]"
                                class="btn-sm btn-emerald"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                    />
                                </svg>
                            </button>
                            <input
                                :disabled="disabledRawMaterials[name]"
                                @input="rawUnchanged = false"
                                class="w-24 rounded bg-gray-200 p-2 text-right text-sm disabled:cursor-not-allowed disabled:bg-gray-600 dark:bg-sky-200 dark:text-slate-900 dark:disabled:bg-gray-600"
                                v-model="rawMaterials[name]"
                                :rel="name"
                                type="text"
                            />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">
                        <button @click="helpRawMaterials" class="btn btn-gray mb-4 mr-2">Help</button>
                        <button @click="$emit('fetchNewYield')" :disabled="rawUnchanged" class="btn btn-emerald mb-4">
                            Update Yield
                        </button>
                    </td>
                </tr>
            </template>

            <template v-if="Object.keys(production.intermediate_materials).length">
                <tr class="bg-sky-300 dark:bg-sky-800">
                    <th class="text-lg font-semibold" colspan="3">Intermediate Products</th>
                </tr>
                <tr v-for="(qty, name) in production.intermediate_materials">
                    <td colspan="2" class="p-2">
                        <div class="flex">
                            <cloud-image
                                class="mr-2 inline-flex"
                                :public-id="`${name}.png`"
                                crop="scale"
                                quality="100"
                                width="32"
                                :alt="name"
                            />
                            <div>
                                <span>{{ name }}</span>
                                <br />
                                <span class="italic">{{ qty }} per min</span>
                            </div>
                        </div>
                    </td>
                    <td class="p-2 text-right">
                        <div class="flex justify-end space-x-2 pr-2">
                            <label
                                :for="`importToggle${name.replace(/ /gi, '')}`"
                                class="flex cursor-pointer items-center"
                            >
                                <!-- Produce -->
                                <div class="mr-2 font-medium">Produce</div>
                                <!-- toggle -->
                                <div class="relative">
                                    <!-- input -->
                                    <input
                                        :id="`importToggle${name.replace(/ /gi, '')}`"
                                        v-model="newImports[name]"
                                        type="checkbox"
                                        class="sr-only"
                                    />
                                    <!-- line -->
                                    <div class="block h-8 w-14 rounded-full bg-gray-600 dark:bg-gray-200"></div>
                                    <!-- dot -->
                                    <div
                                        class="dot absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition dark:bg-gray-800"
                                    ></div>
                                </div>
                                <!-- import -->
                                <div class="ml-2 font-medium">Import</div>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">
                        <button @click="helpImport" class="btn btn-gray mr-2">Help</button>
                        <button @click="$emit('fetch')" class="btn btn-emerald">Recalculate</button>
                    </td>
                </tr>
            </template>

            <template v-if="Object.keys(production.byproducts).length">
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr class="bg-sky-300 dark:bg-sky-800">
                    <th class="text-lg font-semibold" colspan="3">Byproducts</th>
                </tr>
                <tr v-for="(qty, name) in production.byproducts">
                    <td v-if="qty > 0" colspan="2" class="p-2">
                        <div class="flex">
                            <cloud-image
                                class="mr-2 inline-flex"
                                :public-id="`${name}.png`"
                                crop="scale"
                                quality="100"
                                width="32"
                                :alt="name"
                            />
                            <div>
                                <span>{{ name }}</span>
                                <br />
                                <span class="italic">{{ +qty.toFixed(4) }} per min</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </template>

            <tr>
                <td>&nbsp;</td>
            </tr>

            <tr class="bg-sky-300 dark:bg-sky-700">
                <th class="text-lg font-semibold" colspan="3">Power Summary</th>
            </tr>
            <!--            <tr>-->
            <!--                <td-->
            <!--                    colspan='2'-->
            <!--                    class='whitespace-nowrap p-2'-->
            <!--                >-->
            <!--                    Energy Per Product (MJ)-->
            <!--                </td>-->
            <!--                <td class='p-2 text-right'>-->
            <!--                    {{ //Math.round(60 * production__total_power / newYield) }}-->
            <!--                </td>-->
            <!--            </tr>-->
            <tr>
                <td colspan="2" class="p-2">Total Power Used (MW)</td>
                <td class="p-2 text-right">
                    {{ Math.ceil(production__total_power) }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="p-2">Total Power Generated (MW)</td>
                <td class="p-2 text-right">
                    {{ Math.abs(Math.ceil(production__total_power_generated)) }}
                </td>
            </tr>
            <tr v-if="production__net_power < 0">
                <td colspan="2" class="p-2">Net Power Generated (MW)</td>
                <td class="p-2 text-right">
                    {{ Math.abs(Math.ceil(production__net_power)) }}
                </td>
            </tr>
            <tr v-if="production__net_power > 0">
                <td colspan="2" class="p-2">Net Power Used (MW)</td>
                <td class="p-2 text-right">
                    {{ Math.abs(Math.ceil(production__net_power)) }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="p-2">Coal Generator Equiv.</td>
                <td class="p-2 text-right">
                    {{ Math.abs(Math.ceil(production__net_power / 75)) }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="p-2">Fuel Generator Equiv.</td>
                <td class="p-2 text-right">
                    {{ Math.abs(Math.ceil(production__net_power / 150)) }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="p-2">Nuclear Power Plant Equiv.</td>
                <td class="p-2 text-right">
                    {{ Math.abs(Math.ceil(production__net_power / 2500)) }}
                </td>
            </tr>

            <tr>
                <td>&nbsp;</td>
            </tr>

            <tr class="bg-sky-300 dark:bg-sky-700">
                <th class="text-lg font-semibold" colspan="3">Parts List (Buildings)</th>
            </tr>
            <tr
                @click="buildingChecks[mat] = !buildingChecks[mat]"
                class="cursor-pointer"
                v-for="(num, mat) in production__building_summary.total_build_cost"
            >
                <td colspan="2" class="whitespace-nowrap p-2">
                    <cloud-image
                        class="mr-2 inline-flex"
                        :public-id="mat"
                        crop="scale"
                        quality="100"
                        width="24"
                        :alt="mat"
                    />
                    <input v-model="buildingChecks[mat]" type="checkbox" />
                    {{ mat }}
                </td>
                <td class="p-2 text-right">{{ num }}</td>
            </tr>
        </table>
        <building-summary
            :production__building_details="production__building_details"
            :production__building_summary="production__building_summary"
            :production__total_power="production__net_power"
        />
    </div>
</template>
<script>
import BuildingSummary from '@/Pages/Production/BuildingSummary.vue';

export default {
    name: 'production-summary',

    components: {
        BuildingSummary,
    },
    props: {
        buildingChecks: {},
        disabledRawMaterials: {},
        fetch: {},
        fetchNewYield: {},
        helpImport: {},
        helpRawMaterials: {},
        newImports: {},
        newYield: {},
        production: {},
        production__building_summary: {},
        production__building_details: {},
        production__total_power: {},
        production__total_power_generated: {},
        production__net_power: {},
        rawMaterials: {},
        rawUnchanged: {},
    },
};
</script>
