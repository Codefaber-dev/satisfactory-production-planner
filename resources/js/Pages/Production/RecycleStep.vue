<template>
    <!-- V91: the terminal AWESOME Sink step, mirroring the ProductionStep row layout
         (identifier th / step cell / inputs cell / build-diagram row) so it reads as a
         peer of every production step — only the content differs (points/min output,
         recycled-item inputs, no recipe/clock controls). -->
    <tbody
        :class="shouldFlash ? ['dark:bg-slate-500', 'bg-slate-300'] : []"
        class="mb-4 block rounded-lg border border-gray-300 transition-all duration-300 dark:border-slate-700 lg:mb-0 lg:table-row-group lg:rounded-none lg:border-0"
    >
        <tr class="block border-t border-gray-200 dark:border-slate-700 lg:table-row">
            <!-- identifier -->
            <th class="block py-1 text-lg lg:table-cell lg:py-0">
                <div :id="identifier" class="flex flex-col items-center justify-center px-1">
                    {{ identifier }}
                </div>
            </th>

            <!-- step: building + count + power, and the points/min output -->
            <td class="block p-2 dark:text-slate-800 lg:table-cell lg:whitespace-nowrap">
                <div
                    class="flex shrink-0 grow flex-col items-center space-y-2 rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg lg:whitespace-nowrap"
                >
                    <div class="flex w-full">
                        <cloud-image class="mr-2" :public-id="step.name" width="48" crop="scale" :alt="step.name" />
                        <div class="mr-2 flex shrink-0 grow flex-col space-y-2 font-semibold">
                            <div class="flex items-center justify-between">
                                <span class="flex-1">{{ step.name }}</span>
                                <!-- V143: sink steps join the step-check mechanism -->
                                <span
                                    class="cursor-pointer rounded bg-emerald-400 p-1 hover:bg-emerald-500"
                                    @click="toggleCheck(checkKey)"
                                >
                                    {{ finished ? '✅' : '⬜' }}
                                </span>
                            </div>
                            <span class="font-light">
                                {{ +(step.num_buildings || 0).toFixed(2) }}× {{ step.building }} [{{
                                    Math.ceil(step.power || 0)
                                }}
                                MW]
                            </span>
                        </div>
                    </div>
                    <!-- output (terminal): total points/min sunk -->
                    <div
                        class="flex w-full shrink-0 grow flex-col space-y-1 rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg"
                    >
                        <span class="font-semibold"> Output </span>
                        <div class="my-2 rounded bg-lime-300 p-2 text-xs font-bold">
                            ♻️ {{ Math.round(step.output.points || 0).toLocaleString() }} points/min
                        </div>
                    </div>
                </div>
            </td>

            <!-- inputs, each linking back to its producing step -->
            <td class="block p-2 dark:text-slate-800 lg:table-cell lg:whitespace-nowrap">
                <div
                    class="flex w-full shrink-0 grow flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg"
                >
                    <span class="mb-1 font-semibold">Inputs</span>
                    <div
                        class="my-1 flex items-center"
                        v-for="input in step.inputs"
                        :key="input.item"
                        data-test="recycle-input"
                    >
                        <cloud-image class="mr-2" :public-id="input.item" width="48" crop="scale" :alt="input.item" />
                        <div class="flex grow flex-col font-semibold">
                            <a
                                v-if="stepMap[input.item]"
                                @click="$emit('flashDestination', stepMap[input.item])"
                                :href="`#${stepMap[input.item]}`"
                                class="text-sky-700"
                                >{{ input.item }} ({{ stepMap[input.item] }}) ⬅️</a
                            >
                            <span v-else>{{ input.item }}</span>
                            <span class="font-light">{{ +(input.qty || 0).toFixed(2) }}/min</span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>

        <!-- build diagram -->
        <tr class="block lg:table-row" v-if="step.footprint">
            <td class="block text-center lg:table-cell" colspan="100">
                <build-diagram
                    :footprint="step.footprint"
                    :title="step.name"
                    :materials="{ inputs: step.inputs.map((input) => input.item), outputs: [] }"
                    :details="sinkDetails"
                    :identifier="identifier"
                    :check-key="checkKey"
                />
            </td>
        </tr>
    </tbody>
</template>
<script>
import BuildDiagram from '@/Pages/Production/BuildDiagram';
import { isChecked, toggleCheck } from '@/stepChecks';
import CloudImage from '../../Components/CloudImage.vue';

export default {
    name: 'RecycleStep',
    components: { CloudImage, BuildDiagram },
    emits: ['flashDestination'],
    data() {
        return {
            shouldFlash: false,
        };
    },
    mounted() {
        // V95: the terminal sink step appears in QuickNav under its R.* identifier
        this.Bus.emit('RegisterQuickNav', { name: this.step.name, identifier: this.identifier });

        // flash parity with ProductionStep: highlight when a destination link
        // targeting this step is clicked
        this.Bus.on('flash', ({ dest }) => {
            if (dest === this.identifier) {
                this.shouldFlash = true;
                setTimeout(() => {
                    this.shouldFlash = false;
                }, 1200);
            }
        });
    },
    props: {
        step: { type: Object, required: true },
        identifier: { type: String, default: '' },
        // map of material name → producing-step identifier (for input links)
        stepMap: { type: Object, default: () => ({}) },
    },
    methods: {
        toggleCheck,
    },
    computed: {
        // V143: sink check key — '-sink' suffix, mirroring '<name>-<recipe>' on
        // production steps without colliding with a same-named recipe step
        checkKey() {
            return `${this.step.name}-sink`;
        },

        finished() {
            return isChecked(this.checkKey);
        },

        // sink equivalents of the modal step-details payload (V126)
        sinkDetails() {
            return {
                output: { name: this.step.name, qty: this.step.output?.points || 0, unit: 'points/min' },
                inputs: Object.fromEntries((this.step.inputs || []).map((input) => [input.item, input.qty])),
                building: this.step.building,
                num_buildings: this.step.num_buildings,
                power_usage: this.step.power,
                build_cost: this.step.build_cost || {},
                max_slots: 0,
            };
        },
    },
};
</script>
