<template>
    <div @click="select" class="flex flex-1">
        <div v-show="!slim" class="flex flex-col items-center justify-center">
            <div
                class="mx-4 mb-2 flex h-20 w-20 flex-col items-center justify-center rounded-full border border-blue-500 bg-blue-200 text-xl dark:text-slate-800"
            >
                {{ +recipe.base_per_min }}
                <span class="whitespace-nowrap text-xs">per min</span>
            </div>
            <div
                class="mx-4 flex h-20 w-20 flex-col items-center justify-center rounded-full border border-rose-500 bg-rose-200 text-xl dark:text-slate-800"
            >
                {{
                    Math.round(
                        (60 * +recipe.base_yield) / +recipe.base_per_min
                    )
                }}s
                <span class="whitespace-nowrap text-xs">per {{ +recipe.base_yield }}</span>
            </div>
        </div>

        <div class="flex flex-col">
            <!-- recipe name -->
            <div class="flex text-lg font-semibold">
                <span class="whitespace-nowrap">{{ description }}</span>
                <div
                    :class="slim ? ['p-1'] : ['px-4', 'py-2']"
                    v-if="recipe.alt_recipe"
                    class="ml-2 rounded border border-purple-500 bg-purple-200 text-xs dark:text-slate-800"
                >
                    Alt
                </div>
                <div
                    :class="slim ? ['p-1'] : ['px-4', 'py-2']"
                    v-if="recipe.energy_efficient"
                    class="ml-2 rounded border border-green-500 bg-green-200 text-xs dark:text-slate-800"
                >
                    Energy
                </div>
                <div
                    :class="slim ? ['p-1'] : ['px-4', 'py-2']"
                    v-if="recipe.resource_efficient"
                    class="ml-2 rounded border border-green-500 bg-green-200 text-xs dark:text-slate-800"
                >
                    Resource
                </div>
            </div>
            <div class="text-sm" v-show="slim">
                ⏱️ {{ +recipe.base_per_min }} per min ({{
                    +recipe.base_yield
                }}
                every
                {{
                    Math.round(
                        (60 * +recipe.base_yield) / +recipe.base_per_min
                    )
                }}s)
            </div>
            <div class="text-sm">
                <div
                    v-if="recipe.ingredients && recipe.ingredients.length"
                    class="my-2 flex flex-col rounded border-l-4 border-yellow-500 bg-yellow-200 p-2 dark:text-slate-800"
                >
                    <div
                        v-for="o in recipe.ingredients.map(
                            (o) => `${o.name} (${+o.pivot.base_qty} per min)`
                        )"
                    >
                        {{ o }}
                    </div>
                </div>
                <div
                    class="my-2 rounded border-l-4 border-teal-500 bg-teal-200 p-2 dark:text-slate-800"
                    v-if="recipe.byproducts && recipe.byproducts.length"
                >
                    {{
                        recipe.byproducts
                            .map(
                                (o) =>
                                    `${o.name} (${+o.pivot.base_qty} per min)`
                            )
                            .join(', ')
                    }}
                </div>
                <span v-if="recipe.building" v-show="!slim" class="italic">{{
                    recipe.building.name
                }}</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RecipeDetail',

    props: {
        recipe: {
            required: true,
        },
        slim: {
            default: false,
        },
    },

    methods: {
        select() {
            this.$emit('select', { recipe: this.recipe });
        },
    },

    computed: {
        description() {
            return this.recipe.description || this.recipe.product.name;
        },
    },
};
</script>

<style scoped></style>
