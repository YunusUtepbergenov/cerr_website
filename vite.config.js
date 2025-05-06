import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import dotenv from 'dotenv';

dotenv.config();

export default defineConfig({
    plugins: [
        laravel({
            input: [ 
                'resources/css/plugins/fontawesome-5.css',
                'resources/css/app.css',
                'resources/css/vendor/swiper.css',
                'resources/css/vendor/metismenu.css', 
                'resources/css/vendor/magnific-popup.css',
                'resources/css/style.css',
                'resources/js/vendor/jquery.min.js',
                'resources/js/app.js',
                'resources/js/vendor/swiper.js',
                'resources/js/vendor/metisMenu.min.js',
                'resources/js/plugins/audio.js',
                'resources/js/plugins/magnific-popup.js',
                'resources/js/plugins/resize-sensor.min.js',
                'resources/js/plugins/theia-sticky-sidebar.min.js',
                'resources/js/main.js'
            ],
            refresh: true,
        }),
    ],
});