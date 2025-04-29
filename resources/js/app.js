import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import VueGoogleMaps from '@fawmi/vue-google-maps';
import '@fortawesome/fontawesome-free/css/all.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueGoogleMaps, {
                load: {
                    key: 'AIzaSyCHX8R7Dty7yOx-DXRnIK8O51WovSyq08g',
                    libraries: 'places', // This is required if you use the Autocomplete plugin
                },
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
