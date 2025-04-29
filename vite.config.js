import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({

    // Optimize dependencies
    optimizeDeps: {
        exclude: ['fast-deep-equal'], // Exclude specific dependencies from pre-bundling
    },

    // Plugins configuration
    plugins: [
        laravel({
            input: 'resources/js/app.js', // Entry point for your application
            refresh: true, // Enable automatic browser refresh on changes
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null, // Customize asset URL transformations
                    includeAbsolute: false,
                },
            },
        }),
    ],
});