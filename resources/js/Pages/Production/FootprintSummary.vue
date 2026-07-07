<script>
export default {
    name: 'footprint-summary',
    props: {
        footprint: {},
    },
};
</script>

<template>
    <div class="w-full max-w-xs text-left lg:w-48">
        <ul>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Foundations </span>
                <span class="flex-1 text-right">
                        {{ footprint.foundations }}
                        ( {{ footprint.length_foundations }}
                        x
                        {{ footprint.width_foundations }})
                    </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold">Walls</span>
                <span class="flex-1 text-right">
                        {{ footprint.walls }}
                        ({{ footprint.height_walls }}
                        x
                        {{ 2 * (footprint.length_foundations + footprint.width_foundations) }})
                    </span>
            </li>
            <li v-if="footprint.grouped" class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Blueprints </span>
                <span class="flex-1 text-right">
                        {{ footprint.blueprints }}
                        ({{ footprint.group_size }} ea)
                    </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold">{{ footprint.grouped ? 'Blueprint Rows' : 'Building Rows' }}</span>
                <span class="flex-1 text-right">
                        {{ footprint.rows }}
                    </span>
            </li>
            <li class="flex border-b border-gray-300">
                    <span class="ml-2 font-semibold">
                        {{ footprint.grouped ? 'Blueprints Per Row' : 'Buildings Per Row' }}
                    </span>
                <span class="flex-1 text-right">
                        {{ footprint.buildings_per_row }}
                    </span>
            </li>
            <li v-show="footprint.rows > 1" class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Row Spacing </span>
                <span class="flex-1 text-right"> {{ footprint.row_spacing }} m </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Top Offset </span>
                <span class="flex-1 text-right"> {{ footprint.top_offset }} m </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Left Offset </span>
                <span class="flex-1 text-right"> {{ footprint.left_offset }} m </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Belt Speed </span>
                <span class="flex-1 text-right">
                        {{ footprint.belt_speed }}
                    </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Input Max Rate </span>
                <span class="flex-1 text-right" :class="{ 'text-red-300': footprint.belt_utilization_in > 100 }">
                        {{ footprint.belt_load_in }} <br />
                        ({{ footprint.belt_utilization_in }}%)
                    </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Output Max Rate </span>
                <span class="flex-1 text-right" :class="{ 'text-red-300': footprint.belt_utilization_out > 100 }">
                        {{ footprint.belt_load_out }} <br />
                        ({{ footprint.belt_utilization_out }}%)
                    </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Power Shards </span>
                <span class="flex-1 text-right">
                        {{ footprint.power_shards }}
                    </span>
            </li>
            <li class="flex border-b border-gray-300">
                <span class="ml-2 font-semibold"> Somersloops </span>
                <span class="flex-1 text-right">
                        {{ footprint.somersloops }}
                    </span>
            </li>
        </ul>
    </div>
</template>