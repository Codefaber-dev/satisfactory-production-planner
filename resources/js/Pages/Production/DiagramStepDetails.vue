<template>
    <!-- full plan-step details for the diagram modal title block: bordered
         compartments with tiny uppercase labels, engineering-drawing style in blueprint colors
         (V126/V128). Displays the overview numbers as-is — multipliers are
         already baked in. -->
    <div class="flex flex-wrap content-start items-stretch text-left" data-step-details>
        <section v-if="details.output" class="grow border-b border-r border-blue-200/40 px-2 py-1" data-details-output>
            <h3 class="text-[9px] font-normal uppercase tracking-widest text-blue-300">Output</h3>
            <ul>
                <li>
                    {{ qty(details.output.qty) }} {{ details.output.unit || 'per min' }} —
                    <material-name :name="details.output.name" />
                </li>
            </ul>
        </section>

        <section
            v-if="entries(details.byproducts).length"
            class="grow border-b border-r border-blue-200/40 px-2 py-1"
            data-details-byproducts
        >
            <h3 class="text-[9px] font-normal uppercase tracking-widest text-blue-300">Byproducts</h3>
            <ul>
                <li v-for="[name, amount] in entries(details.byproducts)" :key="name">
                    {{ qty(amount) }} per min — <material-name :name="name" />
                </li>
            </ul>
        </section>

        <section
            v-if="entries(details.inputs).length"
            class="grow border-b border-r border-blue-200/40 px-2 py-1"
            data-details-inputs
        >
            <h3 class="text-[9px] font-normal uppercase tracking-widest text-blue-300">Inputs</h3>
            <ul>
                <li v-for="[name, amount] in entries(details.inputs)" :key="name">
                    {{ qty(amount) }} per min — <material-name :name="name" />
                </li>
            </ul>
        </section>

        <section
            v-if="details.num_buildings"
            class="grow border-b border-r border-blue-200/40 px-2 py-1"
            data-details-building
        >
            <h3 class="text-[9px] font-normal uppercase tracking-widest text-blue-300">Machines</h3>
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

        <section
            v-if="entries(details.build_cost).length"
            class="grow border-b border-r border-blue-200/40 px-2 py-1"
            data-details-build-cost
        >
            <h3 class="text-[9px] font-normal uppercase tracking-widest text-blue-300">Build Cost</h3>
            <ul>
                <li v-for="[name, amount] in entries(details.build_cost)" :key="name">
                    {{ Math.ceil(amount) }}× {{ name }}
                </li>
            </ul>
        </section>
    </div>
</template>
<script>
import { h } from 'vue';
import { diagramRegistry } from '@/diagramRegistry';

export default {
    name: 'diagram-step-details',
    components: {
        // material name as a jump link when another registered step produces it
        // (V130); imports/raw leaves have no registry entry => plain text
        MaterialName: {
            name: 'material-name',
            props: { name: { type: String, required: true } },
            render() {
                const producer = this.$parent.producerOf(this.name);

                if (!producer) {
                    return h('span', this.name);
                }

                return h(
                    'button',
                    {
                        type: 'button',
                        class: 'underline decoration-dotted underline-offset-2 hover:text-blue-50',
                        'data-material-link': '',
                        onClick: () => this.$parent.$emit('navigate', producer),
                    },
                    `${this.name} (${producer.identifier})`
                );
            },
        },
    },
    props: {
        details: {
            type: Object,
            required: true,
        },
        // this step's identifier — a material never links back to itself (V130)
        identifier: {
            type: String,
            default: '',
        },
    },
    computed: {
        // variant keys can be the composite "Building (mk)" details key — never
        // repeat the building name inside its own parenthetical
        showVariant() {
            return this.details.variant && !this.details.variant.includes(this.details.building);
        },
    },
    emits: ['navigate'],
    methods: {
        producerOf(name) {
            return diagramRegistry.find((entry) => entry.name === name && entry.identifier !== this.identifier) ?? null;
        },
        entries(map) {
            return Object.entries(map || {});
        },
        qty(value) {
            return (+Number(value || 0).toFixed(2)).toLocaleString();
        },
    },
};
</script>
