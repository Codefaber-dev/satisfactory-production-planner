require('./bootstrap');

import { createApp, h } from 'vue';
import mitt from 'mitt';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import CloudImage from '@/Components/CloudImage';
import { createPinia } from 'pinia';

const Bus = mitt();
const Pinia = createPinia();

createInertiaApp({
    resolve: (name) => require(`./Pages/${name}`).default,
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.config.globalProperties.Bus = Bus;
        app
            .use(plugin)
            .use(Pinia)
            .mixin({ methods: { route } })
            .component('CloudImage', CloudImage)
            .component('InertiaLink', Link)
            .mount(el);
    },
    progress: {
        color: '#6ee7b7',
    },
});
