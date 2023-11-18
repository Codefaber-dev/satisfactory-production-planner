<template>
    <div class="flex-col border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900">
        <table class="w-full">
            <tbody class="border-b border-gray-200 dark:border-slate-800">
                <tr class="bg-sky-300 dark:bg-sky-700">
                    <th class="text-lg font-semibold" colspan="4">Building Summary</th>
                </tr>
                <tr>
                    <td class="p-2">
                        <div class="flex items-center">
                            <cloud-image public-id="PowerShard" height="36" width="36"></cloud-image>
                            Power Shards
                        </div>
                    </td>
                    <td colspan="2" class="p-2 text-right">
                        {{ powerShards }}
                    </td>
                </tr>
            </tbody>
            <tbody
                class="border-b border-gray-200 dark:border-slate-800"
                v-for="(o, bldg) in production__building_summary.variants"
            >
                <tr>
                    <td class="p-2">{{ o.num_buildings }}x {{ bldg }}</td>
                    <td class="p-2 text-right">{{ Math.round(o.power_usage) }} MW</td>
                    <td nowrap="" class="p-2 text-right">
                        <div class="flex flex-col">
                            <div :key="mat" v-for="(num, mat) in o.build_cost">{{ mat }} {{ num }}</div>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tbody class="rounded-b-lg bg-blue-200 font-bold dark:bg-gray-900">
                <tr>
                    <td class="p-2">Totals</td>
                    <td class="p-2 text-right">{{ Math.round(production__total_power) }} MW</td>
                    <td nowrap="" class="p-2 text-right">
                        <div class="flex flex-col">
                            <div :key="mat" v-for="(num, mat) in production__building_summary.total_build_cost">
                                {{ mat }} {{ num }}
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
export default {
    name: 'building-summary',
    props: {
        production__building_summary: {},
        production__building_details: {},
        production__total_power: {},
    },

    computed: {
        powerShards() {
            return this.production__building_details.map((o) => +o.footprint.power_shards).reduce((a, b) => a + b, 0);
        },
    },
};
</script>
