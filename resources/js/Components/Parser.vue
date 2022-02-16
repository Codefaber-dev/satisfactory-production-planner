<template>
    <div>
        <template ref='content' v-if='false'>
            <slot></slot>
        </template>
        <div v-html='rendered'></div>
    </div>
</template>

<script>
import { marked } from 'marked';

export default {
    name: 'Parser',

    mounted() {
        console.log(this.$slots);
        this.rendered = marked.parse(this.$refs.content.innerText);
    },

    data() {
        return {
            rendered: ''
        }
    }
};
</script>

<style scoped>

</style>
