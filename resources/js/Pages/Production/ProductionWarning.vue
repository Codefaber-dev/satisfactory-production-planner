<template>
    <div
        v-if="Object.keys(overrides || {}).length"
        v-show="showWarnings"
        class="fixed bottom-4 right-4 z-[1000] my-4 cursor-pointer space-y-4 rounded-lg bg-slate-100 p-4 shadow dark:bg-slate-800 dark:shadow-rose-500"
        @click="showWarnings = false"
    >
        <span class="flex items-center pb-2 font-semibold">
            <span class="mr-2 flex-1">Recipes auto-adjusted to resolve a loop</span>
            <button @click="showWarnings = false" class="">⛔</button>
        </span>
        <div class="flex flex-col" v-for="(recipe, name) in overrides">
            <span class="inline rounded-lg bg-amber-200 p-2 dark:bg-amber-800">
                {{ name }} auto-sourced to break a circular dependency (change it or import {{ name }} to override).
            </span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'production-warning',
    props: {
        overrides: {},
        showWarnings: {},
    },
};
</script>
