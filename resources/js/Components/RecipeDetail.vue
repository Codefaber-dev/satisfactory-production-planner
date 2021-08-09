<template>
    <div @click="select" class="flex-1 flex">
        <div v-show="!slim" class="flex flex-col items-center justify-center">
            <div class="h-20 w-20 rounded-full bg-blue-200 border border-blue-500 text-xl mx-4 flex flex-col items-center justify-center mb-2">
                {{ +recipe.base_per_min }}
                <span class="text-xs whitespace-nowrap">per min</span>
            </div>
            <div class="h-20 w-20 rounded-full bg-rose-200 border border-rose-500 text-xl mx-4 flex flex-col items-center justify-center">
                {{ Math.round(60*+recipe.base_yield/+recipe.base_per_min) }}s
                <span class="text-xs whitespace-nowrap">per {{ +recipe.base_yield }}</span>
            </div>
        </div>

        <div class="flex flex-col">
            <!-- recipe name -->
            <div class="font-semibold text-lg flex">
                <span class="whitespace-nowrap">{{ recipe.description || recipe.product.name }}</span>
                <div :class="slim ? ['p-1'] : ['px-4','py-2']" v-if="recipe.alt_recipe" class="text-xs rounded bg-purple-200 border border-purple-500 ml-2">
                    Alt
                </div>
                <div :class="slim ? ['p-1'] : ['px-4','py-2']" v-if="recipe.energy_efficient"
                     class="text-xs rounded bg-green-200 border border-green-500 ml-2">
                    Energy
                </div>
                <div :class="slim ? ['p-1'] : ['px-4','py-2']" v-if="recipe.resource_efficient"
                     class="text-xs rounded bg-green-200 border border-green-500 ml-2">
                    Resource
                </div>
            </div>
            <div class="text-sm" v-show="slim">⏱️ {{ +recipe.base_per_min }} per min ({{ +recipe.base_yield }} every {{ Math.round(60*+recipe.base_yield/+recipe.base_per_min) }}s)</div>
            <div class="text-sm">
                <div v-if="recipe.ingredients && recipe.ingredients.length" class="bg-yellow-200 border-l-4 border-yellow-500 p-2 my-2 rounded flex flex-col">
                    <div v-for="o in recipe.ingredients.map(o => `${o.name} (${+o.pivot.base_qty} per min)`)">{{ o }}</div>
                </div>
                <div class="bg-teal-200 border-l-4 border-teal-500 p-2 my-2 rounded" v-if="recipe.byproducts && recipe.byproducts.length">
                    {{ recipe.byproducts.map(o => `${o.name} (${+o.pivot.base_qty} per min)`).join(', ') }}
                </div>
                <span v-if="recipe.building" v-show="!slim" class="italic">{{ recipe.building.name }}</span>
            </div>
            <!--            {{ recipe }}--></div>
    </div>
</template>

<script>
export default {
    name: "RecipeDetail",

    props: {
        recipe: {
            required: true
        },
        slim : {
            default : false
        }
    },

    methods : {
        select() {
            this.$emit('select',{ recipe: this.recipe });
        }
    }
}
</script>

<style scoped>

</style>
