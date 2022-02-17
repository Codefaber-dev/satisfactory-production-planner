<template>
    <app-layout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100"
            >
                Power Build Planner
            </h2>
        </template>

        <div class="py-12 flex flex-col space-y-8">
            <div class="mx-auto flex max-w-7xl space-x-10 sm:px-6 lg:px-8">
                <!-- left column -->
                <form
                    class="w-full"
                    @submit.prevent="fetch"
                >
                    <div
                        class="flex w-full flex-col justify-center space-y-4 bg-white p-4 shadow-xl dark:bg-gray-800 sm:rounded-lg"
                    >
                        <span class="font-semibold">
                            Enter desired total power output in MW
                        </span>
                        <input
                            type="number"
                            step="1"
                            min="0"
                            v-model="newOutput"
                            class="rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800 w-full"
                        />

                        <button type='submit' class='btn btn-emerald'>
                            Go
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if='plans && plans.length'>
            <div class='flex flex-col items-start justify-center lg:flex-row space-y-8 lg:space-y-0 lg:space-x-8 px-4'>
                <div v-for='plan in plans' class='flex flex-col items-center w-full lg:w-72 space-y-4 justify-center rounded-lg bg-sky-300 dark:bg-sky-700 shadow dark:shadow-sky-400 p-4 '>
                    <div class='font-semibold text-xl'>
                        {{ plan.num }}x {{ plan.name }}s
                    </div>
                    <cloud-image :public-id='plan.image' height='128' width='128' />
                    <div>
                        Gross Power {{ plan.output }} MW
                    </div>
                    <div class='flex flex-col w-full border border-sky-500 rounded p-4'>
                        <span class='font-semibold'>Fuel Options</span>
                        <ul>
                            <li class='flex items-center my-2 border border-sky-200 dark:border-sky-600 rounded bg-sky-200 dark:bg-sky-600' v-for='(num, fuel) in plan.fuel'>
                                <cloud-image :public-id='fuel' height='48' width='48' class='mr-2' />
                                <div class='flex flex-col flex-1 space-y-1 bg-sky-300 dark:bg-sky-700 p-2 rounded'>
                                    <span>{{ fuel }}</span>
                                    <span>{{ +num.toFixed(2) }} per min</span>
                                    <div v-if='plan.buildable_fuels.indexOf(fuel) > -1'>
                                        <inertia-link class='btn-sm btn-emerald' :href='`/dashboard/${fuel}/${num.toFixed(2)}/${fuel}/mk1`'>
                                            Build
                                        </inertia-link>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div v-if='plan.other && Object.keys(plan.other).length' class='flex flex-col w-full border border-sky-500 rounded p-4'>
                        <span class='font-semibold'>Other Inputs</span>
                        <ul>
                            <li  class='flex items-center my-2' v-for='(num, ing) in plan.other'>
                                <cloud-image :public-id='ing' height='48' width='48' class='mr-2' />
                                {{ ing }} <br>
                                {{ +num.toFixed(2) }} per min
                            </li>
                        </ul>
                    </div>
                    <div v-if='plan.waste && Object.keys(plan.waste).length' class='flex flex-col w-full border border-sky-500 rounded p-4'>
                        <span class='font-semibold'>Byproducts</span>
                        <ul>
                            <li class='flex items-center my-2' v-for='(num, ing) in plan.waste'>
                                <cloud-image :public-id='ing' height='48' width='48' class='mr-2' />
                                {{ ing }} <br>
                                {{ +num.toFixed(2) }} per min
                            </li>
                        </ul>
                    </div>
                    <div class='flex flex-col w-full border border-sky-500 rounded p-4'>
                        <span class='font-semibold'>Build Cost</span>
                        <ul>
                            <li class='flex items-center my-2' v-for='(num, mat) in plan.build_cost'>
                                <cloud-image :public-id='mat' height='48' width='48' class='mr-2' />
                                {{ mat }} <br>
                                {{ +num.toFixed(2) }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import CloudImage from '@/Components/CloudImage';

export default {
    name: 'Show',

    props: {
        plans: Array,
        output: Number
    },

    components: {
        AppLayout,
        CloudImage,
    },

    data() {
        return {
            newOutput: this.output,
        }
    },

    methods: {
        fetch() {
            this.$inertia.get(`/power/${this.newOutput}`)
        }
    }
};
</script>

<style scoped>

</style>
