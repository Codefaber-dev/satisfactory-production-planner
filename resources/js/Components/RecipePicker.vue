<template>
    <div @click="showMenu = !showMenu"
         class="relative transition-all flex shadow-lg border border-gray-400 group hover:border-blue-300 rounded px-4 py-2 items-center justify-between cursor-pointer">
        <!-- favorite indicator -->
        <div class="pr-4">
            <span v-if="selected.favorite">⭐</span>
        </div>
        <!-- selected recipe -->
        <recipe-detail :slim="true" :recipe="selected"></recipe-detail>
        <!-- dropdown arrow -->
        <div class="font-bold text-lg group-hover:text-blue-300 transition-all"
             :style="{transform: `rotate(${showMenu ? 0 : 180}deg)`}">
            ^
        </div>

        <!-- menu -->
        <div v-show="showMenu" style="top:100%"
             class="absolute bg-white border border-blue-300 shadow-lg w-100 flex flex-col z-50">
            <recipe-detail @select="select" class="border-b border-gray-300 hover:bg-blue-100 rounded p-4"
                           :key="recipe.id" v-for="recipe in recipes" v-bind="{recipe}"></recipe-detail>
        </div>

        <!-- modal bg -->
        <div v-show="showMenu" class="modal-bg fixed z-40 bg-black inset-0 opacity-5"></div>
    </div>
</template>

<script>
import RecipeDetail from "@/Components/RecipeDetail";

export default {
    name: "RecipePicker",

    components: {
        RecipeDetail
    },

    props: {
        recipes: {
            required: true,
        },
        selected: {
            required: true
        }
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
                capture: false
            }
        }
    },

    methods: {
        hide() {
            this.showMenu = false;
        },

        select({recipe}) {
            this.$emit('select', {recipe});
        },

        outside() {
            console.log('clicked')
        },

        handler(event) {
            console.log('Clicked outside (Using config), middleware returned true :)')
        },
        // Note: The middleware will be executed if the event was fired outside the element.
        //       It should have only sync functionality and it should return a boolean to
        //       define if the handler should be fire or not
        middleware(event) {
            return event.target.className !== 'modal'
        }
    }
}
</script>

<style scoped>

</style>
