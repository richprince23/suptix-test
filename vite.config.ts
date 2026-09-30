import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import { glob } from 'glob';

// Dynamically discover all Vue pages
const pages = glob.sync('resources/js/pages/**/*.vue');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.ts',
                'resources/css/app.css',
                // 'resources/js/app.js',
                ...pages,
            ],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },

    server: {
        host: '0.0.0.0', // Ensures server is accessible on local network
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173
        },
        cors: true,
    },
});
