<template>
    <div :class="{ dark }">
        <jet-banner />

        <release-notes
            ref="releaseNotes"
            v-bind="{ version: $page.props.version, releaseNotes: $page.props.releaseNotes }"
        />

        <div class="min-h-screen bg-gray-100 dark:bg-slate-800 dark:text-gray-100">
            <nav class="border-b border-gray-100 bg-white dark:border-sky-500 dark:bg-slate-900">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex flex-shrink-0 items-center">
                                <inertia-link :href="route('dashboard')">
                                    <jet-application-mark class="block h-9 w-auto" />
                                </inertia-link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <jet-nav-link :href="route('dashboard')" :active="route().current('dashboard')">
                                    Production Planner
                                </jet-nav-link>
                                <jet-nav-link :href="route('power.index')" :active="route().current('power')">
                                    Power Planner
                                </jet-nav-link>
                                <jet-nav-link :href="route('factories')" :active="route().current('factories')">
                                    My Factories
                                </jet-nav-link>
                                <jet-nav-link :href="route('favorites')" :active="route().current('favorites')">
                                    My Favorite Recipes
                                </jet-nav-link>
                                <!-- <jet-nav-link
                                    :href="route('checklist')"
                                    :active="route().current('checklist')"
                                >
                                    Production Checklist
                                </jet-nav-link> -->
                            </div>
                        </div>

                        <div class="hidden sm:ml-6 sm:flex sm:items-center">
                            <div class="mr-3 flex h-16 items-center justify-center">
                                <!--                                <span class="mr-4 whitespace-nowrap text-emerald-800 dark:text-emerald-300">-->
                                <!--                                    🙏 Donations: {{ '$' + $page.props.donations }}-->
                                <!--                                </span>-->

                                <a
                                    target="_blank"
                                    href="https://www.paypal.com/donate/?hosted_button_id=LZHQ2LHJQA78Y"
                                    class="btn btn-gray mr-4 whitespace-nowrap"
                                >
                                    Donate
                                </a>

                                <a
                                    target="_blank"
                                    href="https://www.patreon.com/SatisfactoryProductionPlanner"
                                    class="btn btn-gray mr-4 whitespace-nowrap"
                                >
                                    Become a Patron
                                </a>

                                <button @click="showReleaseNotes" class="btn btn-gray mr-4 whitespace-nowrap">
                                    Release Notes
                                </button>

                                <a
                                    href="https://discord.gg/dqGQECppCy"
                                    target="_blank"
                                    class="btn btn-emerald mr-4 whitespace-nowrap"
                                >
                                    Join the Discussion on Discord
                                </a>

                                <!-- dark mode toggle -->
                                <div class="flex w-full items-center justify-center dark:text-gray-200">
                                    <label for="darkToggle" class="flex cursor-pointer items-center">
                                        <!-- light mode -->
                                        <div class="mr-2 font-medium">
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
                                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                                                />
                                            </svg>
                                        </div>
                                        <!-- toggle -->
                                        <div class="relative">
                                            <!-- input -->
                                            <input
                                                id="darkToggle"
                                                @change="savePrefs"
                                                v-model="dark"
                                                type="checkbox"
                                                class="sr-only"
                                            />
                                            <!-- line -->
                                            <div class="block h-8 w-14 rounded-full bg-gray-600 dark:bg-gray-200"></div>
                                            <!-- dot -->
                                            <div
                                                class="dot absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition"
                                            ></div>
                                        </div>
                                        <!-- dark mode -->
                                        <div class="ml-2 font-medium">
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
                                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                                                />
                                            </svg>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="relative ml-3">
                                <jet-dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700 focus:outline-none dark:border-gray-500 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-blue-300 dark:hover:bg-gray-900 dark:hover:text-blue-300 dark:focus:text-blue-200"
                                            >
                                                {{ $page.props?.user.name || 'Guest' }}

                                                <svg
                                                    class="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template v-if="$page.props?.user.id" #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-100">
                                            Manage Account
                                        </div>

                                        <jet-dropdown-link :href="route('profile.show')"> Profile </jet-dropdown-link>

                                        <jet-dropdown-link
                                            :href="route('api-tokens.index')"
                                            v-if="$page.props.jetstream.hasApiFeatures"
                                        >
                                            API Tokens
                                        </jet-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <jet-dropdown-link as="button"> Log Out </jet-dropdown-link>
                                        </form>
                                    </template>

                                    <template v-else #content>
                                        <jet-dropdown-link :href="route('login')"> Login </jet-dropdown-link>

                                        <jet-dropdown-link :href="route('register')"> Register </jet-dropdown-link>
                                    </template>
                                </jet-dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-100"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pt-2 pb-3">
                        <jet-responsive-nav-link :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </jet-responsive-nav-link>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pt-4 pb-1">
                        <div class="flex items-center px-4">
                            <div>
                                <div class="text-base font-medium text-gray-800">
                                    {{ $page.props?.user.name }}
                                </div>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $page.props?.user.email }}
                                </div>
                            </div>
                        </div>

                        <div v-if="$page.props?.user.id" class="mt-3 space-y-1">
                            <jet-responsive-nav-link
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                Profile
                            </jet-responsive-nav-link>

                            <jet-responsive-nav-link
                                :href="route('api-tokens.index')"
                                :active="route().current('api-tokens.index')"
                                v-if="$page.props.jetstream.hasApiFeatures"
                            >
                                API Tokens
                            </jet-responsive-nav-link>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <jet-responsive-nav-link as="button"> Log Out </jet-responsive-nav-link>
                            </form>
                        </div>
                        <!-- Guest Options -->
                        <div v-else>
                            <jet-responsive-nav-link :href="route('login')"> Login </jet-responsive-nav-link>

                            <jet-responsive-nav-link :href="route('register')"> Register </jet-responsive-nav-link>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow dark:bg-sky-900" v-if="$slots.header">
                <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header"></slot>
                </div>
            </header>

            <!-- Page Content -->
            <main class="dark:bg-gray-800 dark:text-gray-100">
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script>
import JetApplicationMark from '@/Jetstream/ApplicationMark';
import JetBanner from '@/Jetstream/Banner';
import JetDropdown from '@/Jetstream/Dropdown';
import JetDropdownLink from '@/Jetstream/DropdownLink';
import JetNavLink from '@/Jetstream/NavLink';
import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink';
import store from '@/store';
import ReleaseNotes from '@/Components/ReleaseNotes';

export default {
    components: {
        JetApplicationMark,
        JetBanner,
        JetDropdown,
        JetDropdownLink,
        JetNavLink,
        JetResponsiveNavLink,
        ReleaseNotes,
    },

    props: {
        version: String,
        releaseNotes: String,
    },

    mounted() {
        this.setDarkMode();
    },

    data() {
        return {
            showingNavigationDropdown: false,
            dark: store.getItem('dark'),
        };
    },

    methods: {
        switchToTeam(team) {
            this.$inertia.put(
                route('current-team.update'),
                {
                    team_id: team.id,
                },
                {
                    preserveState: false,
                }
            );
        },

        showReleaseNotes() {
            this.$refs.releaseNotes.toggle();
        },

        logout() {
            this.$inertia.post(route('logout'));
        },

        savePrefs() {
            store.setItem('dark', this.dark);

            this.setDarkMode();
        },

        setDarkMode() {
            if (this.dark) {
                document.querySelector('html').classList.add('dark', 'bg-slate-800');
                document.querySelector('html').classList.remove('bg-gray-100');
            } else {
                document.querySelector('html').classList.remove('dark', 'bg-slate-800');
                document.querySelector('html').classList.add('bg-gray-100');
            }
        },
    },
};
</script>

<style>
/* Toggle B */
input:checked ~ .dot {
    transform: translateX(100%);
    background-color: #48bb78;
}
</style>
