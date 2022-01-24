<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                My Factories
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col space-x-10">
                <div class="flex my-8 w-full">
                    <div class="flex flex-1 justify-end">
                        <inertia-link as="button" href="/dashboard" class="btn btn-emerald">
                            New Factory
                        </inertia-link>
                    </div>
                </div>


                <factory v-for="factory in factories" v-bind="factory"/>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import Factory from '@/Components/Factory';

export default {
    name: "Index",

    components: {
        AppLayout,
        Factory
    },

    props: {
        factories: Array
    }
}
</script>

<style scoped>

</style>
