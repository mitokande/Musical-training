import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/landing.css', 'resources/css/marketing.css', 'resources/js/app.js', 'resources/js/posthog.js'],
            refresh: true,
        }),
    ],
});
