import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/pages/home-entry.css', 'resources/css/pages/about-entry.css', 'resources/css/pages/article-entry.css', 'resources/css/pages/listings-entry.css', 'resources/css/pages/podcast-entry.css', 'resources/js/app.js', 'resources/css/filament/admin/theme.css', 'resources/js/filament/admin.js', 'resources/css/prism.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
