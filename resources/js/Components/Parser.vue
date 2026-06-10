<template>
    <div>
        <div ref='content' style='display:none'>
            <slot></slot>
        </div>
        <div v-html='rendered'></div>
    </div>
</template>

<script>
import { marked } from 'marked';

export default {
    name: 'Parser',

    mounted() {
        const text = this.$refs.content?.textContent ?? '';
        this.rendered = marked.parse(text.trim());
    },

    data() {
        return {
            rendered: '',
        };
    },
};
</script>

<style scoped>

</style>
