import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import VueGoogleMaps from '@fawmi/vue-google-maps';
import '@fortawesome/fontawesome-free/css/all.css';

import { createPinia } from 'pinia'; // ✅ Add this

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia()) // ✅ Register Pinia here
            .use(VueGoogleMaps, {
                load: {
                    key: 'AIzaSyDhDxk7K-eY-Evo5-TPFj8iVacRXbsv1Ss',
                    libraries: 'places',
                },
            })
            .mount(el);

        return app; // ✅ Return the created app instance
    },
    progress: {
        color: '#4B5563',
    },
});
