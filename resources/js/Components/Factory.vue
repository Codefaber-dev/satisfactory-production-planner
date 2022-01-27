<template>
    <div class="flex my-12 relative">
        <div
            class="absolute flex rounded-t-xl px-4 py-2 shadow bg-sky-300 dark:bg-sky-900 items-center justify-center"
            style="top:-44px; left: 8rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>

            <span class="font-semibold text-xl ml-2">
                    <span v-show="! editing">{{ name }}</span>
                    <input @keyup.enter="saveEdits" ref="newName" class="px-2 rounded shadow dark:bg-sky-600 w-48"
                           v-model="form.name"
                           v-show="editing">
                </span>
        </div>

        <div class="font-semibold relative text-2xl text-center flex flex-col items-start justify-center ">
            <div
                class="w-24 flex flex-col rounded-l-xl justify-center items-center divide-y divide-black dark:divide-white p-4 bg-sky-300 dark:bg-sky-900">
                <div class="py-2">
                    <span v-show="! editing">{{ (+yield).toFixed(0) }}</span>
                    <input @keyup.enter="saveEdits" class="px-2 py-1 rounded shadow dark:bg-sky-600 w-16"
                           v-show="editing"
                           v-model="form.yield" type="text">
                </div>
                <div class="py-2">
                    min
                </div>
            </div>
        </div>
        <div
            class="flex relative w-full rounded-r-xl shadow bg-white dark:bg-slate-900 items-center border-4 border-sky-300 dark:border-sky-900">

            <div class="flex flex-1 mx-8 space-x-4 items-center py-2">
                <cloud-image :public-id="product.name" crop="scale" quality="100" width="64" alt="logo"/>

                <div class="flex flex-col flex-1">
                    <div v-show="! editing" class="font-bold text-2xl flex">
                        {{ product.name }}
                    </div>
                    <span v-show="! editing" class="italic">{{ recipe.description || 'default' }}</span>
                    <div v-html="renderedNotes"  v-if="!! notes && ! editing"
                       class="p-4 m-4 font-mono dark:bg-slate-900 bg-gray-100 rounded-lg border dark:border-slate-800 prose dark:prose-invert">
                    </div>
                    <textarea v-show="editing" v-model="form.notes" placeholder="Notes" rows="4" class="p-2 rounded-lg border shadow dark:bg-slate-800 dark:border-slate-700 font-mono my-2 w-full">

                    </textarea>
                    <p v-if="!! imports && ! editing">
                       Imports <cloud-image v-for="name in imports.split(',')" :public-id="name" crop="scale" quality="100" width="24" class="inline-flex px-2" alt="logo"/>
                    </p>
                    <recipe-picker v-show="editing" @select="updateRecipe"
                                   :recipes="product.recipes"
                                   :selected="selectedRecipe"></recipe-picker>
                </div>

                <div v-show="! editing" class="flex space-x-2 mx-4">
                    <inertia-link as="button" :href="planUrl" class="btn btn-emerald">Plan</inertia-link>
                    <button @click="toggleEditing" class="btn btn-orange">Edit</button>
                    <button @click="deletePrompt" class="btn btn-rose">Delete</button>
                </div>

                <div v-show="editing" class="flex space-x-2 mx-4">

                    <button @click="saveEdits" class="btn btn-emerald">Save</button>
                    <button @click="cancelEditing" class="btn btn-rose">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import RecipePicker from '@/Components/RecipePicker';
const Markdown = require('markdown-it')();

export default {
    name: "Factory",

    components: {
        RecipePicker
    },

    props: {
        id: Number,
        name: String,
        product: Object,
        recipe: Object,
        yield: Number,
        imports: String,
        notes: String,
    },

    data() {
        return {
            editing: false,
            selectedRecipe: this.recipe,
            form: {
                name: this.name,
                yield: +this.yield,
                recipe_id: this.recipe.id,
                imports: this.imports,
                notes: this.notes
            }
        }
    },

    methods: {
        parse(el) {
            return Markdown.renderInline(el);
        },

        toggleEditing() {
            this.editing = !this.editing;

            if (this.editing) {
                this.$refs.newName.focus();
            }
        },

        deletePrompt() {
            let conf = confirm("Are you sure you want to delete the factory?");

            if (!conf)
                return;

            return this.$inertia.delete(`/factories/${this.id}`);
        },

        saveEdits() {
            this.$inertia.patch(`/factories/${this.id}`, this.form, {
                onSuccess: () => {
                    this.editing = false;
                }
            });
        },

        cancelEditing() {
            this.editing = false;
        },

        updateRecipe({recipe}) {
            this.form.recipe_id = recipe.id;
            this.selectedRecipe = recipe;
        }
    },

    computed: {
        planUrl() {
            return `/dashboard/${this.product.name}/${(+this.yield).toFixed(2)}/${this.recipe.description || this.product.name}?factory=${this.id}&imports=${this.imports || ''}`;
        },
        renderedNotes() {
            return Markdown.render(this.form.notes);
        }
    }
}
</script>

<style scoped>

</style>
