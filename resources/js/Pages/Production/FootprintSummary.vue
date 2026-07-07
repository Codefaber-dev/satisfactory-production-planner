<script>
export default {
    name: 'footprint-summary',
    props: {
        footprint: {},
    },
    computed: {
        // label/value pairs for the spec-sheet table — values pre-composed so
        // every row renders on one line (no wrapping inside a cell)
        rows() {
            const f = this.footprint;

            return [
                ['Foundations', `${f.foundations} (${f.length_foundations} x ${f.width_foundations})`],
                ['Walls', `${f.walls} (${f.height_walls} x ${2 * (f.length_foundations + f.width_foundations)})`],
                f.grouped ? ['Blueprints', `${f.blueprints} (${f.group_size} ea)`] : null,
                [f.grouped ? 'Blueprint Rows' : 'Building Rows', `${f.rows}`],
                [f.grouped ? 'Blueprints Per Row' : 'Buildings Per Row', `${f.buildings_per_row}`],
                f.rows > 1 ? ['Row Spacing', `${f.row_spacing} m`] : null,
                ['Top Offset', `${f.top_offset} m`],
                ['Left Offset', `${f.left_offset} m`],
                ['Belt Speed', `${f.belt_speed}`],
                ['Input Max Rate', `${f.belt_load_in} (${f.belt_utilization_in}%)`, f.belt_utilization_in > 100],
                ['Output Max Rate', `${f.belt_load_out} (${f.belt_utilization_out}%)`, f.belt_utilization_out > 100],
                ['Power Shards', `${f.power_shards}`],
                ['Somersloops', `${f.somersloops}`],
            ].filter(Boolean);
        },
    },
};
</script>

<template>
    <!-- two-column spec sheet: fixed label column, values start on a shared
         left edge, one line per row (never wraps) -->
    <div class="w-full text-left lg:w-64">
        <ul>
            <li
                v-for="[label, value, over] in rows"
                :key="label"
                class="grid grid-cols-[9rem_1fr] gap-x-2 whitespace-nowrap border-b border-gray-300"
            >
                <span class="font-semibold">{{ label }}</span>
                <span :class="{ 'text-red-300': over }">{{ value }}</span>
            </li>
        </ul>
    </div>
</template>
