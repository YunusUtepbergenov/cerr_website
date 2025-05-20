import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [ 
                'resources/js/app.js',
                'resources/css/plugins/fontawesome-5.css',
                'resources/css/style.css',
            ],
            refresh: true,
        }),
    ],
});