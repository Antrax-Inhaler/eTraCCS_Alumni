import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    optimizeDeps: {
        exclude: ['fast-deep-equal'],
    },

    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    server: {
        host: '192.168.8.104',           // Your local IP
        port: 5173,                       // Custom dev server port
        cors: true,                       // Enable CORS
        origin: 'http://192.168.8.104:5173', // Vite's own origin
        strictPort: true,                // Avoid fallback to random port
        watch: {
            usePolling: true,            // Helps with WSL/Docker
        },
    },
});
