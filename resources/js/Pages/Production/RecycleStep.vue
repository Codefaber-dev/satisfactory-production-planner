<template>
    <tbody class="mb-4 block rounded-lg border border-emerald-300 dark:border-emerald-800 lg:mb-0 lg:table-row-group lg:rounded-none lg:border-0">
        <tr class="block border-t border-gray-200 dark:border-slate-700 lg:table-row">
            <!-- identifier -->
            <th class="block py-1 text-lg lg:table-cell lg:py-0">
                <div :id="identifier" class="flex flex-col items-center justify-center px-1">
                    {{ identifier }}
                </div>
            </th>

            <!-- the step: building + count + power, and the output -->
            <td class="block p-2 dark:text-slate-800 lg:table-cell lg:whitespace-nowrap">
                <div class="flex shrink-0 grow flex-col space-y-2 rounded-lg border border-emerald-500 bg-emerald-200 p-2 shadow-lg">
                    <div class="flex w-full items-center">
                        <cloud-image class="mr-2" :public-id="step.building" width="48" crop="scale" :alt="step.building" />
                        <div class="flex grow flex-col font-semibold">
                            <span>{{ step.name }}</span>
                            <span class="font-light">
                                {{ +(step.num_buildings || 0).toFixed(2) }}× {{ step.building }} [{{ Math.ceil(step.power || 0) }} MW]
                            </span>
                        </div>
                    </div>
                    <!-- output -->
                    <div class="rounded bg-lime-300 p-2 text-xs font-bold">
                        <template v-if="step.type === 'sink'">
                            ♻️ Output: {{ Math.round(step.output.points || 0).toLocaleString() }} points/min
                        </template>
                        <template v-else>
                            Output: {{ +(step.output.qty || 0).toFixed(2) }} {{ step.output.item }}/min →
                            <a
                                v-if="sinkIdentifier"
                                @click="$emit('flashDestination', sinkIdentifier)"
                                :href="`#${sinkIdentifier}`"
                                class="text-sky-700"
                            >AWESOME Sink ({{ sinkIdentifier }}) ➡️</a>
                        </template>
                    </div>
                </div>
            </td>

            <!-- inputs, each linking to its producing step -->
            <td class="block p-2 dark:text-slate-800 lg:table-cell lg:whitespace-nowrap">
                <div class="flex shrink-0 grow flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg">
                    <span class="mb-1 font-semibold">Inputs</span>
                    <div
                        class="my-1 flex items-center"
                        v-for="input in step.inputs"
                        :key="input.item"
                        data-test="recycle-input"
                    >
                        <cloud-image class="mr-2" :public-id="input.item" width="32" crop="scale" :alt="input.item" />
                        <div class="flex grow flex-col font-semibold">
                            <a
                                v-if="stepMap[input.item]"
                                @click="$emit('flashDestination', stepMap[input.item])"
                                :href="`#${stepMap[input.item]}`"
                                class="text-sky-700"
                            >{{ input.item }} ({{ stepMap[input.item] }}) ⬅️</a>
                            <span v-else>{{ input.item }}</span>
                            <span class="font-light">{{ +(input.qty || 0).toFixed(2) }}/min</span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>

        <!-- build diagram -->
        <tr class="block lg:table-row" v-if="step.footprint" v-show="diagrams">
            <td colspan="100" class="p-2">
                <build-diagram :footprint="step.footprint" />
            </td>
        </tr>
    </tbody>
</template>
<script>
import BuildDiagram from '@/Pages/Production/BuildDiagram';
import CloudImage from '../../Components/CloudImage.vue';

export default {
    name: 'RecycleStep',
    components: { CloudImage, BuildDiagram },
    emits: ['flashDestination'],
    props: {
        step: { type: Object, required: true },
        identifier: { type: String, default: '' },
        // map of material name → producing-step identifier (for input links)
        stepMap: { type: Object, default: () => ({}) },
        // identifier of the sink step (packaging output links here)
        sinkIdentifier: { type: String, default: '' },
        diagrams: { type: Boolean, default: false },
    },
};
</script>
