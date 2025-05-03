import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        cors: true,
        // host: "0.0.0.0",
        //     hmr: {
        //         host: "192.168.1.5",
        //         // protocol: "wss",
        //     },
    },
});
