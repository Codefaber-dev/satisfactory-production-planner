<template>
    <div>
        <div ref='content' style='display:none'>
            <slot></slot>
        </div>
        <div v-html='rendered'></div>
    </div>
</template>

<script>
import MarkdownIt from 'markdown-it';

const markdown = new MarkdownIt();

export default {
    name: 'Parser',

    mounted() {
        const text = this.$refs.content?.textContent ?? '';
        this.rendered = markdown.render(text.trim());
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
