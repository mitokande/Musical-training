import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // zoom-room.js is a heavy SDK bundle loaded only by the Lesson Room.
            input: ['resources/css/app.css', 'resources/css/landing.css', 'resources/css/marketing.css', 'resources/js/app.js', 'resources/js/posthog.js', 'resources/js/zoom-room.js'],
            refresh: true,
        }),
    ],
});
