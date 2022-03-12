<template>
    <div @click="select" class="flex flex-1">
        <div class="flex">
            <cloud-image
                v-if='!slim'
                :public-id="product.name"
                width="32"
                crop="scale"
                class="mr-4"
            />
            <!-- product name -->
            <span class="text-lg whitespace-nowrap">
                {{ product.name }}
            </span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProductDetail',

    props: {
        product: {
            required: true
        },
        slim: {
            default: false
        }
    },

    methods: {
        select() {
            this.$emit('select', { product: this.product });
        },
    },
};
</script>

<style scoped></style>
