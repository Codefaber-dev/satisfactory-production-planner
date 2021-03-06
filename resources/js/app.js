require('./bootstrap');

// Import modules...
import { createApp, h } from 'vue';
import mitt from 'mitt';
import { App as InertiaApp, plugin as InertiaPlugin, InertiaLink } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import CloudImage from '@/Components/CloudImage';

const el = document.getElementById('app');

const Bus = mitt();


const app = createApp({
    render: () =>
        h(InertiaApp, {
            initialPage: JSON.parse(el.dataset.page),
            resolveComponent: (name) => require(`./Pages/${name}`).default,
        }),
    });

    app.mixin({ methods: { route } })
        .use(InertiaPlugin)
        .component('CloudImage',CloudImage)
        .component('InertiaLink',InertiaLink)
        .mount(el);

app.config.globalProperties.Bus = Bus;

InertiaProgress.init({ color: '#6ee7b7', showSpinner: true });
