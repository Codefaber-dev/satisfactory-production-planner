<template>
    <div
        v-if="production__warnings && production__warnings.length"
        v-show="showWarnings"
        class="fixed bottom-4 right-4 my-4 cursor-pointer space-y-4 rounded-lg bg-slate-100 p-4 shadow dark:bg-slate-800 dark:shadow-rose-500"
        @click="showWarnings = false"
    >
        <span class="flex pb-2 font-semibold">
            <span class="flex-1">Circular Dependencies Found - Imports Adjusted</span>
            <button @click="showWarnings = false" class="btn btn-rose">
                X
            </button>
        </span>
            <div
                class="flex flex-col"
                v-for="warning in production__warnings"
            >
        <span
            class="inline rounded-lg bg-rose-300 p-2 dark:bg-rose-800"
        >
        {{ warning.replace("Circular dependency found: ", "Problem: ") }}
        </span>
                <span
                    class="line rounded-lg bg-gray-200 p-2 dark:bg-gray-800"
                >
        Resolution: Import
        {{ warning.split("->").pop() }}
        </span>
        </div>
    </div>
</template>

<script>
export default {
    name: "production-warning",
    props: {
        production__warnings: {},
        showWarnings: {}
    }
};
</script>
