import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/leaflet-map.js',
                'resources/js/dashboard-geojson-map.js',
                'resources/css/auth/login.css',
                'resources/js/auth/login.js',
            ],
            refresh: true,
        }),
    ],
});
