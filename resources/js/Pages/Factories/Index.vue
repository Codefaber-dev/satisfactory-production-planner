<template>
    <app-layout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100"
            >
                My Factories
            </h2>
        </template>

        <div class="py-12">
            <div
                class="mx-auto flex max-w-7xl flex-col space-x-10 sm:px-6 lg:px-8"
            >
                <div class="my-8 flex w-full">
                    <div class="flex flex-1 justify-end">
                        <inertia-link
                            as="button"
                            href="/dashboard"
                            class="btn btn-emerald"
                        >
                            New Factory
                        </inertia-link>
                    </div>
                </div>

                <factory v-for="factory in factories" v-bind="factory" />
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import Factory from '@/Components/Factory';

export default {
    name: 'Index',

    components: {
        AppLayout,
        Factory,
    },

    props: {
        factories: Array,
    },
};
</script>

<style scoped></style>
