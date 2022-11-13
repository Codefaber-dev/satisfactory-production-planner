<template>
    <div
        class="group relative flex cursor-pointer items-center justify-between rounded border border-gray-400 px-4 py-2 shadow transition-all hover:border-blue-300 dark:border-slate-500 dark:shadow-slate-800 dark:hover:border-sky-800 dark:hover:shadow-sky-800"
    >
        <div @click="toggle" class="absolute inset-0 h-full w-full bg-transparent"></div>

        <!-- selected recipe -->
        <product-detail :slim="true" :product="selected"></product-detail>
        <!-- dropdown arrow -->
        <div
            class="pointer-events-none text-lg font-bold transition-all group-hover:text-blue-300"
            :style="{ transform: `rotate(${showMenu ? 180 : 0}deg)` }"
        >
            🔽
        </div>

        <!-- menu -->
        <div
            v-show="showMenu"
            style="top: 100%"
            class="absolute left-0 z-50 flex max-h-[400px] w-full flex-col overflow-y-auto border border-sky-300 bg-white pr-4 shadow-lg dark:border-sky-900 dark:bg-slate-900"
        >
            <div v-if="filter" class="absolute right-12 top-4 text-slate-900 dark:text-slate-100">
                Press Enter To Select
            </div>
            <input
                @click="showMenu = true"
                @keyup.enter.prevent.stop="selectSingleResult"
                ref="filter"
                type="text"
                v-model="filter"
                class="m-2 rounded p-2 dark:bg-slate-900"
                placeholder="Search..."
            />
            <product-detail
                @select="select"
                class="rounded border-b border-gray-300 p-4 hover:bg-sky-100 dark:border-sky-800 dark:hover:bg-sky-900"
                :key="product"
                v-for="product in filteredProducts"
                :product="product"
            ></product-detail>
        </div>

        <!-- modal bg -->
        <div @click="hide" v-show="showMenu" class="modal-bg fixed inset-0 z-40 bg-black opacity-5"></div>
    </div>
</template>

<script>
import ProductDetail from '@/Components/ProductDetail';

export default {
    name: 'ProductPicker',

    components: {
        ProductDetail,
    },

    props: {
        products: {
            required: true,
        },
        selected: {
            default() {
                return {
                    name: '',
                };
            },
        },
    },

    data() {
        return {
            filter: '',
            showMenu: false,
            vcoConfig: {
                handler: this.handler,
                middleware: this.middleware,
                events: ['dblclick', 'click'],
                // Note: The default value is true, but in case you want to activate / deactivate
                //       this directive dynamically use this attribute.
                isActive: true,
                // Note: The default value is true. See "Detecting Iframe Clicks" section
                //       to understand why this behaviour is behind a flag.
                detectIFrame: true,
                // Note: The default value is false. Sets the capture option for EventTarget addEventListener method.
                //       Could be useful if some event's handler calls stopPropagation method preventing event bubbling.
                capture: false,
            },
        };
    },

    computed: {
        filteredProducts() {
            if (!this.filter.length) {
                return this.products;
            }
            return this.products.filter((o) => {
                return o.name.toLowerCase().indexOf(this.filter.toLowerCase()) > -1;
            });
        },
        // selectedChosen() {
        //     return Object.values(this.choices).some((o) => o === this.selected) || {};
        // },
    },

    methods: {
        hide() {
            this.showMenu = false;
        },

        toggle() {
            this.showMenu = !this.showMenu;

            if (this.showMenu) {
                setTimeout(() => {
                    this.$refs.filter.focus();
                    this.$refs.filter.select();
                }, 300);
            }
        },

        selectSingleResult() {
            if (this.filteredProducts.length === 1) {
                console.log('Only one result. Selecting it.');
                let o = {
                    product: Object.assign({}, this.filteredProducts[0]),
                };

                this.$emit('select', o);
                this.hide();
            }

            // see if there is an exact match
            if (this.filteredProducts.some((o) => o.name.toLowerCase() === this.filter.toLowerCase())) {
                let o = {
                    product: this.filteredProducts.filter((o) => o.name.toLowerCase() === this.filter.toLowerCase())[0],
                };
                this.$emit('select', o);
                this.hide();
            }
        },

        select({ product }) {
            console.log({ product });

            this.$emit('select', { product });
            this.hide();
        },

        outside() {
            console.log('clicked');
        },

        handler(event) {
            console.log('Clicked outside (Using config), middleware returned true :)');
        },
        // Note: The middleware will be executed if the event was fired outside the element.
        //       It should have only sync functionality and it should return a boolean to
        //       define if the handler should be fire or not
        middleware(event) {
            return event.target.className !== 'modal';
        },
    },
};
</script>

<style scoped></style>
