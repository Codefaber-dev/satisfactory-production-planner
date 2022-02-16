<template>
    <div
        v-if="Object.keys(overrides || {}).length"
        v-show="showWarnings"
        class="fixed bottom-4 right-4 my-4 cursor-pointer space-y-4 rounded-lg bg-slate-100 p-4 shadow dark:bg-slate-800 dark:shadow-rose-500"
        @click="showWarnings = false"
    >
        <span class="flex pb-2 font-semibold items-center">
            <span class="flex-1 mr-2">Circular Dependencies Found - Recipes Adjusted</span>
            <button @click="showWarnings = false" class="">
                ⛔
            </button>
        </span>
            <div
                class="flex flex-col"
                v-for="(recipe,name) in overrides"
            >
        <span
            class="inline rounded-lg bg-rose-300 p-2 dark:bg-rose-800"
        >
            Recipe for {{ name }} overridden.
        </span>
                <span
                    class="inline rounded-lg bg-gray-200 p-2 dark:bg-gray-800"
                >
        </span>
        </div>
    </div>
</template>

<script>
export default {
    name: "production-warning",
    props: {
        overrides: {},
        showWarnings: {}
    }
};
</script>
