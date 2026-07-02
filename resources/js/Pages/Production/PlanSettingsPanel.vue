<template>
    <div class="rounded border border-sky-700 p-3 mb-2">
        <h2 class="mb-1 font-bold text-xl">
            Plan Settings
        </h2>
        <div class="mb-4 flex flex-wrap gap-3">
            <div
                data-test="plan-setting-card"
                class="flex min-w-40 flex-1 items-center space-x-3 rounded border border-sky-700 p-3 shadow dark:bg-sky-700"
            >
                <RocketLaunchIcon alt="Belt Speed" class="h-10 w-10 shrink-0"/>
                <div class="flex w-full flex-col space-y-1">
                    <label class="whitespace-nowrap text-xl font-bold">Belt Speed</label>
                    <select
                        :value="beltSpeed"
                        @change="$emit('update:beltSpeed', $event.target.value)"
                        class="w-full rounded py-2 px-1 shadow dark:bg-sky-800"
                    >
                        <option value="60">60 per min (mk1)</option>
                        <option value="120">120 per min (mk2)</option>
                        <option value="270">270 per min (mk3)</option>
                        <option value="480">480 per min (mk4)</option>
                        <option value="780">780 per min (mk5)</option>
                        <option value="1200">1200 per min (mk6)</option>
                    </select>
                </div>
            </div>
            <div
                data-test="plan-setting-card"
                class="flex min-w-40 flex-1 items-center space-x-3 rounded border border-sky-700 p-3 shadow dark:bg-sky-700"
            >
                <QueueListIcon alt="Recipe Cost" class="h-10 w-10 shrink-0"/>
                <div class="flex w-full flex-col space-y-1">
                    <label class="whitespace-nowrap text-xl font-bold">Recipe Cost ×</label>
                    <input
                        type="number"
                        step="0.1"
                        min="0.1"
                        max="10"
                        :value="costMultiplier"
                        @input="emitNumber('update:costMultiplier', $event)"
                        class="w-full rounded py-2 px-1 shadow dark:bg-sky-800"
                        title="Recipe cost multiplier (1.0 = default)"
                    />
                </div>
            </div>
            <div
                data-test="plan-setting-card"
                class="flex min-w-40 flex-1 items-center space-x-3 rounded border border-sky-700 p-3 shadow dark:bg-sky-700"
            >
                <BuildingOffice2Icon alt="Building Cost" class="h-10 w-10 shrink-0"/>
                <div class="flex w-full flex-col space-y-1">
                    <label class="whitespace-nowrap text-xl font-bold">Building Cost ×</label>
                    <input
                        type="number"
                        step="0.1"
                        min="0.1"
                        max="10"
                        :value="buildingCostMultiplier"
                        @input="emitNumber('update:buildingCostMultiplier', $event)"
                        class="w-full rounded py-2 px-1 shadow dark:bg-sky-800"
                        title="Building cost multiplier (1.0 = default)"
                    />
                </div>
            </div>
            <div
                data-test="plan-setting-card"
                class="flex min-w-40 flex-1 items-center space-x-3 rounded border border-sky-700 p-3 shadow dark:bg-sky-700"
            >
                <PowerIcon alt="Power Cost" class="h-10 w-10 shrink-0"/>
                <div class="flex w-full flex-col space-y-1">
                    <label class="whitespace-nowrap text-xl font-bold">Power Cost ×</label>
                    <input
                        type="number"
                        step="0.1"
                        min="0.1"
                        max="10"
                        :value="powerMultiplier"
                        @input="emitNumber('update:powerMultiplier', $event)"
                        class="w-full rounded py-2 px-1 shadow dark:bg-sky-800"
                        title="Power cost multiplier (1.0 = default)"
                    />
                </div>
            </div>
        </div>
        <template v-if="uniqueBuildings.length">
            <h2 class="mb-1 font-bold text-xl">
                Blueprint Mode
            </h2>
            <p class="mb-2 font-medium text-slate-700 dark:text-slate-100">
                When enabled, production will be divided evenly into groups of the specified number of buildings
            </p>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-100">Blueprint Designer</span>
                <button
                    v-for="(dim, mk) in designerDims"
                    :key="mk"
                    type="button"
                    @click="$emit('setDesignerMk', mk)"
                    :class="[bpDesigner === mk ? 'btn-gray' : 'btn-emerald']"
                    class="btn-sm"
                >
                    Mk.{{ mk.slice(2) }} ({{ dim }}m)
                </button>
            </div>
            <div class="flex flex-wrap gap-3">
                <div v-for="building in uniqueBuildings" :key="building" class="flex items-center space-x-1">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            type="checkbox"
                            :id="`bp-toggle-${building}`"
                            :checked="!!bpEnabled[building]"
                            @change="$emit('toggleBlueprintEnabled', building)"
                            class="peer sr-only"
                        />
                        <span
                            class="h-5 w-9 rounded-full bg-gray-400 transition-colors after:absolute after:left-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-emerald-500 peer-checked:after:translate-x-4 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400 dark:bg-sky-900"
                        ></span>
                    </label>
                    <label :for="`bp-toggle-${building}`" class="cursor-pointer whitespace-nowrap">
                        {{ building }}
                    </label>
                    <input
                        type="number"
                        min="1"
                        max="40"
                        step="1"
                        :value="bpSizes[building] || 1"
                        :disabled="!bpEnabled[building]"
                        @change="$emit('setBlueprintSize', building, $event.target.value)"
                        class="w-14 rounded py-1 px-1 text-sm shadow disabled:opacity-40 dark:bg-sky-800"
                    />
                </div>
            </div>
        </template>
        <div class="mt-3">
            <button :disabled="working" @click="$emit('apply')" class="btn btn-emerald">
                Update
            </button>
        </div>
    </div>
</template>

<script>
import { BuildingOffice2Icon, PowerIcon, QueueListIcon, RocketLaunchIcon } from '@heroicons/vue/24/outline';

// Presentational Plan Settings + Blueprint Mode panel, extracted from Show.vue
// (T123). All state lives in Show; this component only renders and emits.
export default {
    name: 'PlanSettingsPanel',

    components: {
        RocketLaunchIcon,
        QueueListIcon,
        BuildingOffice2Icon,
        PowerIcon,
    },

    props: {
        beltSpeed: [String, Number],
        costMultiplier: Number,
        buildingCostMultiplier: Number,
        powerMultiplier: Number,
        uniqueBuildings: { type: Array, default: () => [] },
        designerDims: { type: Object, default: () => ({}) },
        bpDesigner: String,
        bpEnabled: { type: Object, default: () => ({}) },
        bpSizes: { type: Object, default: () => ({}) },
        working: Boolean,
    },

    emits: [
        'update:beltSpeed',
        'update:costMultiplier',
        'update:buildingCostMultiplier',
        'update:powerMultiplier',
        'setDesignerMk',
        'toggleBlueprintEnabled',
        'setBlueprintSize',
        'apply',
    ],

    methods: {
        // mirror v-model.number: emit a number when parseable, raw value otherwise
        emitNumber(event, domEvent) {
            const parsed = Number.parseFloat(domEvent.target.value);
            this.$emit(event, Number.isNaN(parsed) ? domEvent.target.value : parsed);
        },
    },
};
</script>
