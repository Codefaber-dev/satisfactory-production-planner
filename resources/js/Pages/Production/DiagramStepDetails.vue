<template>
    <!-- full plan-step details for the diagram modal side panel: blueprint-styled
         (mono/uppercase headers, light-blue line work) to match the modal (V126/V120).
         Displays the overview numbers as-is — multipliers are already baked in. -->
    <div class="space-y-3 border-t border-blue-200/40 pt-3 text-left text-blue-100" data-step-details>
        <section v-if="details.output" data-details-output>
            <h3 class="mb-1 font-bold uppercase tracking-widest text-blue-50">Output</h3>
            <ul>
                <li>{{ qty(details.output.qty) }} {{ details.output.unit || 'per min' }} — {{ details.output.name }}</li>
            </ul>
        </section>

        <section v-if="entries(details.byproducts).length" data-details-byproducts>
            <h3 class="mb-1 font-bold uppercase tracking-widest text-blue-50">Byproducts</h3>
            <ul>
                <li v-for="[name, amount] in entries(details.byproducts)" :key="name">
                    {{ qty(amount) }} per min — {{ name }}
                </li>
            </ul>
        </section>

        <section v-if="entries(details.inputs).length" data-details-inputs>
            <h3 class="mb-1 font-bold uppercase tracking-widest text-blue-50">Inputs</h3>
            <ul>
                <li v-for="[name, amount] in entries(details.inputs)" :key="name">
                    {{ qty(amount) }} per min — {{ name }}
                </li>
            </ul>
        </section>

        <section v-if="details.num_buildings" data-details-building>
            <h3 class="mb-1 font-bold uppercase tracking-widest text-blue-50">Machines</h3>
            <ul>
                <li>
                    {{ details.num_buildings }}× {{ details.building
                    }}<template v-if="showVariant"> ({{ details.variant }})</template>
                    <template v-if="details.clock_speed"> @{{ qty(details.clock_speed) }}%</template>
                    [{{ Math.ceil(details.power_usage || 0) }} MW]
                </li>
                <li v-if="details.max_slots > 0" data-details-sloops>
                    Somersloops: {{ details.slots || 0 }}/{{ details.max_slots }} per machine
                </li>
                <li v-if="details.energy_per_item" data-details-energy>
                    {{ qty(details.energy_per_item) }} MJ per item — {{ qty(details.total_energy) }} MJ total
                </li>
            </ul>
        </section>

        <section v-if="entries(details.build_cost).length" data-details-build-cost>
            <h3 class="mb-1 font-bold uppercase tracking-widest text-blue-50">Build Cost</h3>
            <ul>
                <li v-for="[name, amount] in entries(details.build_cost)" :key="name">
                    {{ Math.ceil(amount) }}× {{ name }}
                </li>
            </ul>
        </section>
    </div>
</template>
<script>
export default {
    name: 'diagram-step-details',
    props: {
        details: {
            type: Object,
            required: true,
        },
    },
    computed: {
        // variant keys can be the composite "Building (mk)" details key — never
        // repeat the building name inside its own parenthetical
        showVariant() {
            return this.details.variant && !this.details.variant.includes(this.details.building);
        },
    },
    methods: {
        entries(map) {
            return Object.entries(map || {});
        },
        qty(value) {
            return (+Number(value || 0).toFixed(2)).toLocaleString();
        },
    },
};
</script>
