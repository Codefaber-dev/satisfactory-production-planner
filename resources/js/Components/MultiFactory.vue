<template>
    <div class='relative my-12 flex'>
        <div
            class='absolute flex items-center justify-center rounded-t-xl bg-sky-300 px-4 py-2 shadow dark:bg-sky-900'
            style='top: -44px; left: 8rem'
        >
            <svg
                xmlns='http://www.w3.org/2000/svg'
                class='h-6 w-6'
                fill='none'
                viewBox='0 0 24 24'
                stroke='currentColor'
            >
                <path
                    stroke-linecap='round'
                    stroke-linejoin='round'
                    stroke-width='2'
                    d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
                />
            </svg>

            <span class='ml-2 text-xl font-semibold'>
                <span v-show='!editing'>{{ name }}</span>
                <input
                    @keyup.enter='saveEdits'
                    ref='newName'
                    class='w-48 rounded px-2 shadow dark:bg-sky-600'
                    v-model='form.name'
                    v-show='editing'
                />
            </span>
        </div>

        <div class='absolute flex ' style='top: -48px; right: 2rem'>
            <div class='flex items-center justify-center'>
                <div v-show='!editing' class='mx-4 flex space-x-2'>
                    <inertia-link
                        as='button'
                        :href='url'
                        class='btn btn-emerald'
                    >Plan
                    </inertia-link
                    >
                    <button @click='toggleEditing' class='btn btn-orange'>
                        Edit
                    </button>
                    <button @click='deletePrompt' class='btn btn-rose'>
                        Delete
                    </button>
                </div>

                <div v-show='editing' class='mx-4 flex space-x-2'>
                    <button @click='saveEdits' class='btn btn-emerald'>
                        Save
                    </button>
                    <button @click='cancelEditing' class='btn btn-rose'>
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class='flex flex-col w-full '>
            <div class='flex rounded-xl bg-sky-300 dark:bg-sky-900' v-for='output in form.outputs'>
                <div
                    class='relative flex flex-col items-start justify-center text-center text-2xl font-semibold'
                >
                    <div
                        class='flex w-24 flex-col items-center justify-center divide-y divide-black  p-4 dark:divide-white '
                    >
                        <div class='py-2'>
                            <span v-show='!editing'>{{ (+output.yield).toFixed(0) }}</span>
                            <input
                                @keyup.enter='saveEdits'
                                class='w-16 rounded px-2 py-1 shadow dark:bg-sky-600'
                                v-show='editing'
                                v-model='output.yield'
                                type='text'
                            />
                        </div>
                        <div class='py-2'>min</div>
                    </div>
                </div>
                <div
                    class='relative flex w-full items-center rounded-r-xl border-4 border-sky-300 bg-white shadow dark:border-sky-900 dark:bg-slate-900'
                >
                    <div class='mx-8 flex flex-1 items-center space-x-4 py-2'>
                        <cloud-image
                            :public-id='output.product.name'
                            crop='scale'
                            quality='100'
                            width='64'
                            alt='logo'
                        />

                        <div class='flex flex-1 flex-col'>
                            <div v-show='!editing' class='flex text-2xl font-bold'>
                                {{ output.product.name }}
                            </div>
                            <span v-show='!editing' class='italic'>{{
                                    output.recipe.description || 'default'
                                }}</span>
                            <recipe-picker
                                v-show='editing'
                                @select='updateRecipe'
                                :recipes='output.product.recipes'
                                :selected='output.recipe'
                            ></recipe-picker>
                        </div>
                    </div>
                </div>

            </div>
            <div class='ml-4 py-2 flex items-center' v-if="!!imports && !editing">
                <span class='font-bold mr-4'>Imports</span>
                <div class='flex items-center space-x-4'>
                    <div v-for="name in imports.split(',')" class='flex items-center'>
                        <cloud-image
                            :public-id='name'
                            crop='scale'
                            quality='100'
                            width='48'
                            class='inline-flex px-2'
                            alt='logo'
                        />
                        {{ name }}
                    </div>
                </div>
            </div>
        </div>
        <div
            v-html='renderedNotes'
            v-if='!!notes && !editing'
            class='prose m-4 rounded-lg border bg-gray-100 p-4 font-mono dark:prose-invert dark:border-slate-800 dark:bg-slate-900'
        ></div>
        <textarea
            v-show='editing'
            v-model='form.notes'
            placeholder='Notes (Markdown Supported)'
            rows='4'
            class='my-2 ml-4 w-full rounded-lg border p-2 font-mono shadow dark:border-slate-700 dark:bg-slate-800'
        >
        </textarea>
    </div>

</template>

<script>
import RecipePicker from '@/Components/RecipePicker';

const Markdown = require('markdown-it')();

export default {
    name: 'MultiFactory',

    components: {
        RecipePicker,
    },

    props: {
        id: Number,
        name: String,
        outputs: Array,
        imports: String,
        notes: String,
        url: String,
    },

    data() {
        return {
            editing: false,
            selectedRecipe: this.recipe,
            form: {
                name: this.name,
                outputs: this.outputs,
                imports: this.imports,
                notes: this.notes,
            },
        };
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
            let conf = confirm('Are you sure you want to delete the factory?');

            if (!conf) return;

            return this.$inertia.delete(`/factories/multi/${this.id}`);
        },

        saveEdits() {
            this.$inertia.patch(`/factories/multi/${this.id}`, this.form, {
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
        // planUrl() {
        //     return `/dashboard/${this.product.name}/${(+this.yield).toFixed(
        //         2
        //     )}/${this.recipe.description || this.product.name}?factory=${
        //         this.id
        //     }&imports=${this.imports || ''}`;
        // },
        renderedNotes() {
            return Markdown.render(this.form.notes);
        },
    },
};
</script>

<style scoped></style>
