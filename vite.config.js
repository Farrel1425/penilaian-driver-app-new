import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/landing.css', 'resources/js/landing.js'],
            refresh: true,
            fonts: [
                bunny('Manrope', { weights: [600, 700, 800], preload: false }),
                bunny('Inter', { weights: [400, 500, 600, 700], styles: ['normal', 'italic'], preload: false }),
                bunny('Newsreader', { weights: [400], styles: ['italic'], preload: false }),
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
