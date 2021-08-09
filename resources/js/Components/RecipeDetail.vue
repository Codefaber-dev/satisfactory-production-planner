<template>
    <div @click="select" class="flex-1 flex">
        <div v-show="!slim" class="flex items-center justify-center">
            <div class="h-20 w-20 rounded-full bg-blue-200 border border-blue-500 text-2xl mx-4 flex flex-col items-center justify-center">
                {{ +recipe.base_per_min }}
                <span class="text-xs whitespace-nowrap">per min</span>
            </div>
        </div>

        <div class="flex flex-col">
            <!-- recipe name -->
            <div class="font-semibold text-lg flex flex-wrap">
                <span class="whitespace-nowrap">{{ recipe.description || 'default' }}
                    <span v-show="slim">({{ +recipe.base_per_min }} per min)</span>
                </span>
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
            <div class="text-sm">
                <div v-if="recipe.ingredients && recipe.ingredients.length" class="bg-yellow-200 p-2 my-2 rounded flex flex-col">
                    <div v-for="o in recipe.ingredients.map(o => `${o.name} (${+o.pivot.base_qty} per min)`)">{{ o }}</div>
                </div>
                <div class="bg-teal-200 p-2 my-2 rounded" v-if="recipe.byproducts && recipe.byproducts.length">
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
