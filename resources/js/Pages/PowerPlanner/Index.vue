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
                            v-model="output"
                            class="rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800 w-full"
                        />

                        <button type='submit' class='btn btn-emerald'>
                            Go
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';

export default {
    name: 'Index',

    components: {
        AppLayout
    },

    data() {
        return {
            output: 100,
        }
    },

    methods: {
        fetch() {
            this.$inertia.get(`/power/${this.output}`)
        }
    }
};
</script>

<style scoped>

</style>
