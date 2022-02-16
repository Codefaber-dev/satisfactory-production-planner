<template>
<div v-show='show' class='fixed inset-0 flex flex-col bg-slate-800 bg-opacity-20'>
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
export default {
    name: 'ReleaseNotes',

    props: {
        releaseNotes: String,
        version: String
    },

    data() {
        let acked = !! +window.localStorage.getItem(`ack-releaseNotes-${this.version}`)

        return {
            show: ! acked,
            ack: acked,
        }
    },

    methods: {
        toggle() {
            this.show = ! this.show;
        },

        acknowledge() {
            window.localStorage.setItem(`ack-releaseNotes-${this.version}`,this.ack ? "1" : "0");
        }
    }
};
</script>

<style scoped>

</style>
