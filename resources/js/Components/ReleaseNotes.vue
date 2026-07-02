<template>
<div v-show='show && !!releaseNotes' class='fixed z-50 inset-0 flex flex-col bg-slate-800 bg-opacity-20'>
    <div class='max-w-[90vw] lg:max-w-screen-lg mx-auto my-[10vh] z-100 rounded-lg bg-gray-200 dark:bg-slate-900 shadow dark:shadow-emerald-500 prose dark:prose-invert'>
        <div v-html='releaseNotes' class='max-h-[70vh] overflow-y-auto p-6'>
        </div>

        <div class='mt-8 flex justify-end items-center p-6'>
            <label for='ack'> Don't show me again for this release
                <input @change='acknowledge' v-model='ack' id='ack' type='checkbox'>
            </label>
            <button @click='toggle' class='btn btn-emerald ml-4'>Got It</button>
        </div>
    </div>
</div>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
    name: 'ReleaseNotes',

    props: {
        releaseNotes: String,
        version: String,
    },

    data() {
        const acked = !!+window.localStorage.getItem(`ack-releaseNotes-${this.version}`);

        return {
            show: !acked,
            ack: acked,
        };
    },

    mounted() {
        // releaseNotes is an optional (lazy) Inertia prop — absent by default.
        // Un-acked visitors auto-show the modal, so fetch the content now.
        if (this.show) {
            this.load();
        }
    },

    methods: {
        // Partial-reload just the releaseNotes prop; server renders it on demand.
        load() {
            if (this.releaseNotes) {
                return;
            }

            router.reload({ only: ['releaseNotes'] });
        },

        toggle() {
            this.show = !this.show;

            if (this.show) {
                this.load();
            }
        },

        acknowledge() {
            window.localStorage.setItem(`ack-releaseNotes-${this.version}`, this.ack ? '1' : '0');
        },
    },
};
</script>

<style scoped>

</style>
