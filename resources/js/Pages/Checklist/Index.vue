<template>
    <app-layout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100"
            >
                Product Checklist
            </h2>
        </template>

        <div class="py-12 flex flex-col space-y-8">
            <div class="mx-auto flex max-w-7xl space-x-10 sm:px-6 lg:px-8">
                <!-- left column -->
                <form
                    class="w-full"
                    @submit.prevent="fetch"
                >
                    <div
                        class="flex w-full flex-col justify-center space-y-4 bg-white p-4 shadow-xl dark:bg-gray-800 sm:rounded-lg"
                    >
                        <span class="font-semibold">
                            Search Products
                        </span>
                        <input
                            v-model="search"
                            class="rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800 w-full"
                        />

                        <span class='font-semibold'>
                            Tier
                        </span>

                        <label :key='tag' v-for='(tag) in tierTags' :for='`tag-${tag}`'>
                            <input type='checkbox' :id='`tag-${tag}`' :value='tag' v-model='tags' > {{ tag.$ucwords() }}
                        </label>

                        <span class='font-semibold'>
                            Tags
                        </span>

                        <input
                            v-model="tagFilter"
                            placeholder='Filter Tags'
                            class="rounded p-4 shadow dark:border dark:border-gray-500 dark:bg-gray-800 w-full"
                        />

                        <label :key='tag' v-for='(tag) in filteredTags' :for='`tag-${tag}`'>
                            <input type='checkbox' :id='`tag-${tag}`' :value='tag' v-model='tags' > {{ tag.$ucwords() }}
                        </label>

                    </div>
                </form>

                <!-- right col -->
                <div class='flex-1'>
                    <table class='w-full'>
<!--                        <thead>-->
<!--                            <th width='48'></th>-->
<!--                            <th>Product</th>-->
<!--                            <th>Tier</th>-->
<!--                        </thead>-->

                            <tr :key='product.name' v-for='product in filteredProducts'>
                                <td class='block w-16 p-2'>
                                    <cloud-image :alt='product.name' class='h-12 w-12 mr-4' :public-id='product.name' />
                                </td>
                                <td nowrap='nowrap' class='p-2'>
                                        {{ product.name }}
                                </td>
                                <td nowrap='nowrap' class='p-2'>
                                    {{ product.tags.filter(o=>o.indexOf('tier')>-1)[0].$ucwords() }}
                                </td>
                            </tr>

                    </table>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { useSatisfactoryStore } from '@/SatisfactoryStore';
import { mapStores } from 'pinia';
import AppLayout from '@/Layouts/AppLayout';
import CloudImage from '@/Components/CloudImage';

export default {
    name: 'Index',

    components: {
        AppLayout,
        CloudImage
    },

    data() {
        return {
            search: '',
            tagFilter: '',
            tags: []
        }
    },

    computed: {
        ...mapStores(useSatisfactoryStore),

        filteredProducts(){
            return this.satisfactoryStore.filter(this.search, this.tags);
        },

        filteredTags() {
            if (! this.tagFilter.trim().length) {
                return this.satisfactoryStore.tags
                    .filter(o => o.toLowerCase().indexOf('tier') < 0);
            }

            return this.satisfactoryStore
                .tags
                .filter(o => o.toLowerCase().indexOf('tier') < 0)
                .filter(o => o.toLowerCase().indexOf(this.tagFilter.toLowerCase()) > -1);
        },

        tierTags() {
            return this.satisfactoryStore
                .tags
                .filter(o => o.indexOf('tier ') > -1)
        }
    }
};
</script>

<style scoped>

</style>
