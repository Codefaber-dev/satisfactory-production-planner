<template>
    <div
        @click="showMenu = !showMenu"
        class="group relative flex cursor-pointer items-center justify-between rounded border border-gray-400 px-4 py-2
        shadow transition-all hover:border-blue-300 dark:border-slate-800 dark:shadow-slate-800 dark:hover:border-sky-800 dark:hover:shadow-sky-800"
    >
        <!-- favorite indicator -->
        <div class="pr-4">
            <span v-if="selected.favorite">⭐</span>
        </div>
        <!-- selected recipe -->
        <recipe-detail :slim="true" :recipe="selected"></recipe-detail>
        <!-- dropdown arrow -->
        <div
            class="text-lg font-bold transition-all group-hover:text-blue-300"
            :style="{ transform: `rotate(${showMenu ? 180 : 0}deg)` }"
        >
            🔽
        </div>

        <!-- menu -->
        <div
            v-show="showMenu"
            style="top: 100%"
            class="w-100 absolute z-50 flex flex-col border border-sky-300 bg-white shadow-lg dark:bg-slate-900"
        >
            <recipe-detail
                @select="select"
                class="rounded border-b border-gray-300 p-4 hover:bg-sky-100 dark:hover:bg-sky-900"
                :key="recipe.id"
                v-for="recipe in recipes"
                v-bind="{ recipe }"
            ></recipe-detail>
        </div>

        <!-- modal bg -->
        <div
            v-show="showMenu"
            class="modal-bg fixed inset-0 z-40 bg-black opacity-5"
        ></div>
    </div>
</template>

<script>
import RecipeDetail from '@/Components/RecipeDetail';

export default {
    name: 'RecipePicker',

    components: {
        RecipeDetail,
    },

    mounted() {
        if (this.recipes?.length === 1 && this.selected.dummy)
            this.selected = this.recipes[0];
    },

    props: {
        recipes: {
            required: true,
        },
        selected: {
            default() {
                return {
                    favorite: false,
                    product: {
                        name: 'dummy',
                    },
                    dummy: true,
                };
            },
        },
    },

    data() {
        return {
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

    methods: {
        hide() {
            this.showMenu = false;
        },

        select({ recipe }) {
            this.$emit('select', { recipe });
        },

        outside() {
            console.log('clicked');
        },

        handler(event) {
            console.log(
                'Clicked outside (Using config), middleware returned true :)'
            );
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
