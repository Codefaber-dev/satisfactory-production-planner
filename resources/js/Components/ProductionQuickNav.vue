<template>
    <div class="fixed right-0 bottom-0 z-50">
        <button
            v-show="isCollapsed"
            @click="toggle"
            class="my-4 mx-8 rounded border border-slate-900 bg-slate-100 px-4 py-2 dark:border-cyan-400 dark:bg-slate-900"
        >
            🗺️ Quick Nav
        </button>
        <div
            v-show="!isCollapsed"
            class="relative my-4 mx-8 flex flex-col rounded-md border border-slate-900 bg-slate-100 p-4 shadow-md dark:border-cyan-400 dark:bg-slate-900"
        >
            <button @click="toggle" class="absolute right-0 top-0 mx-4">&mdash;</button>
            <span>Quick Nav</span>
            <input
                type="text"
                v-model="filter"
                placeholder="Type to filter"
                class="w-full rounded p-1 text-sm text-slate-900"
            />
            <ul v-if="links.length" class="mt-2 flex max-h-[400px] flex-col overflow-y-auto pr-8">
                <li v-for="link in filteredLinks">
                    <button
                        class="text-sm text-cyan-600 hover:text-cyan-500 dark:text-cyan-200 dark:hover:text-cyan-100"
                        @click="scrollTo(link.identifier)"
                    >
                        {{ link.checked ? '✅ ' : '' }} {{ link.identifier }} {{ link.name }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {
        this.Bus.on('RegisterQuickNav', ({ name, identifier }) => {
            this.links.push({
                name,
                identifier,
            });
        });
    },
    props: {
        productionChecks: Object,
    },
    data() {
        return {
            isCollapsed: false,
            links: [],
            filter: '',
        };
    },
    methods: {
        toggle() {
            this.isCollapsed = !this.isCollapsed;
        },
        scrollTo(identifier) {
            const target = document.getElementById(identifier);
            if (target) {
                const offset = 100; // Change this to your desired offset value
                const elementPosition = target.getBoundingClientRect().top + window.scrollY;
                const offsetPosition = elementPosition - offset;

                history.pushState(null, null, `#${identifier}`);

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth', // Enables smooth scrolling
                });
            }
        },
    },
    computed: {
        filteredLinks() {
            if (!this.filter) {
                return this.links.map((o) => {
                    return {
                        ...o,
                        checked: Object.keys(this.productionChecks).some((oo) => oo.includes(o.name)),
                    };
                });
            }
            return this.links
                .filter((o) => {
                    return (
                        o.name.toLowerCase().includes(this.filter.toLowerCase()) ||
                        o.identifier.toLowerCase().includes(this.filter.toLowerCase())
                    );
                })
                .map((o) => {
                    return {
                        ...o,
                        checked: Object.keys(this.productionChecks).some((oo) => oo.includes(o.name)),
                    };
                });
        },
    },
};
</script>

<style scoped></style>
