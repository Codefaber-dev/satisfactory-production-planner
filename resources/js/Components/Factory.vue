<template>
    <div class="relative my-12 flex">
        <div
            class="absolute flex items-center justify-center rounded-t-xl bg-sky-300 px-4 py-2 shadow dark:bg-sky-900"
            style="top: -44px; left: 8rem"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                />
            </svg>

            <span class="ml-2 flex items-center text-xl font-semibold">
                <span v-show="!editing">
                    <a target="_blank" :href="`/shared/${hash_id}`">{{ name }}</a>
                </span>
                <span
                    class="ml-6 flex rounded-xl bg-emerald-300 px-2 py-1 text-sm dark:bg-emerald-800"
                    v-show="is_shared"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="mr-1 h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"
                        />
                    </svg>
                    Public
                </span>
                <input
                    @keyup.enter="saveEdits"
                    ref="newName"
                    class="w-48 rounded px-2 shadow dark:bg-sky-600"
                    v-model="form.name"
                    v-show="editing"
                />
            </span>
        </div>

        <div class="absolute flex" style="top: -48px; right: 2rem">
            <div v-show="!editing" class="mx-4 flex space-x-2">
                <button @click="shareLink" class="btn-sm btn-emerald">{{ shareBtnText }}</button>
                <inertia-link as="button" :href="url" class="btn btn-emerald"> Plan </inertia-link>
                <button @click="toggleEditing" class="btn btn-orange">Edit</button>
                <button @click="deletePrompt" class="btn btn-rose">Delete</button>
            </div>

            <div v-show="editing" class="mx-4 flex items-center space-x-2">
                <label v-show="$page.props?.user">
                    <input v-model="form.is_shared" type="checkbox" />
                    Share With Community
                </label>
                <button @click="saveEdits" class="btn btn-emerald">Save</button>
                <button @click="cancelEditing" class="btn btn-rose">Cancel</button>
            </div>
        </div>

        <div class="flex w-full flex-col">
            <div class="flex w-full">
                <div class="relative flex flex-col items-start justify-center text-center text-2xl font-semibold">
                    <div
                        class="flex w-24 flex-col items-center justify-center divide-y divide-black rounded-l-xl bg-sky-300 p-4 dark:divide-white dark:bg-sky-900"
                    >
                        <div class="py-2">
                            <span v-show="!editing">{{ (+yield).toFixed(0) }}</span>
                            <input
                                @keyup.enter="saveEdits"
                                class="w-16 rounded px-2 py-1 shadow dark:bg-sky-600"
                                v-show="editing"
                                v-model="form.yield"
                                type="text"
                            />
                        </div>
                        <div class="py-2">min</div>
                    </div>
                </div>
                <div
                    class="relative flex w-full items-center rounded-r-xl border-4 border-sky-300 bg-white shadow dark:border-sky-900 dark:bg-slate-900"
                >
                    <div class="mx-8 flex flex-1 items-center space-x-4 py-2">
                        <cloud-image :public-id="product.name" crop="scale" quality="100" width="64" alt="logo" />

                        <div class="flex flex-1 flex-col">
                            <div v-show="!editing" class="flex text-2xl font-bold">
                                {{ product.name }}
                            </div>
                            <span v-show="!editing" class="italic">{{ recipe.description || 'default' }}</span>
                            <div
                                v-html="renderedNotes"
                                v-if="!!notes && !editing"
                                class="prose m-4 rounded-lg border bg-gray-100 p-4 font-mono dark:prose-invert dark:border-slate-800 dark:bg-slate-900"
                            ></div>
                            <textarea
                                v-show="editing"
                                v-model="form.notes"
                                placeholder="Notes"
                                rows="4"
                                class="my-2 w-full rounded-lg border p-2 font-mono shadow dark:border-slate-700 dark:bg-slate-800"
                            >
                            </textarea>
                            <recipe-picker
                                v-show="editing"
                                @select="updateRecipe"
                                :recipes="product.recipes"
                                :selected="selectedRecipe"
                            ></recipe-picker>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ml-4 flex items-center py-2" v-if="!!imports && !editing">
                <span class="mr-4 font-bold">Imports</span>
                <div class="flex items-center space-x-4">
                    <div v-for="name in imports.split(',')" class="flex items-center">
                        <cloud-image
                            :public-id="name"
                            crop="scale"
                            quality="100"
                            width="48"
                            class="inline-flex px-2"
                            alt="logo"
                        />
                        {{ name }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import RecipePicker from '@/Components/RecipePicker';

const Markdown = require('markdown-it')();

export default {
    name: 'Factory',

    components: {
        RecipePicker,
    },

    props: {
        id: Number,
        name: String,
        product: Object,
        recipe: Object,
        yield: Number,
        imports: String,
        notes: String,
        choices: Object,
        url: String,
        is_shared: Boolean,
        hash_id: String,
    },

    data() {
        return {
            editing: false,
            selectedRecipe: this.recipe,
            form: {
                name: this.name,
                yield: +this.yield,
                recipe_id: this.recipe?.id,
                imports: this.imports,
                notes: this.notes,
                is_shared: !!this.is_shared,
            },
            shareBtnText: 'Share Link',
        };
    },

    methods: {
        shareLink() {
            copyToClipboard(`https://satisfactoryproductionplanner.com/shared/${this.hash_id}`);

            this.shareBtnText = 'Copied...';
            setTimeout(() => (this.shareBtnText = 'Share Link'), 1200);
        },

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
            let conf = confirm('Are you sure you want to delete the factory?');

            if (!conf) return;

            return this.$inertia.delete(`/factories/${this.id}`);
        },

        saveEdits() {
            this.$inertia.patch(`/factories/${this.id}`, this.form, {
                onSuccess: () => {
                    this.editing = false;
                },
            });
        },

        cancelEditing() {
            this.editing = false;
        },

        updateRecipe({ recipe }) {
            this.form.recipe_id = recipe.id;
            this.selectedRecipe = recipe;
        },
    },

    computed: {
        renderedNotes() {
            return Markdown.render(this.form.notes || '');
        },
    },
};
</script>

<style scoped></style>
