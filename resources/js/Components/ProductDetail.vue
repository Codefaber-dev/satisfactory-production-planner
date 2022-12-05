<template>
    <div @click="select" class="flex flex-1">
        <div class="flex">
            <cloud-image v-if="!slim" :public-id="product.name" width="32" crop="scale" class="mr-4" />
            <!-- product name -->
            <span class="whitespace-nowrap text-lg">
                {{ name }}
            </span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProductDetail',

    props: {
        product: {
            required: true,
        },
        slim: {
            default: false,
        },
    },

    methods: {
        select() {
            this.$emit('select', { product: this.product });
        },
    },

    computed: {
        name() {
            if (this.product?.is_ficsmas) {
                return this.product.name.replace('FICSMAS', '').trim() + ' (FICSMAS)';
            }
            return this.product.name;
        },
    },
};
</script>

<style scoped></style>
