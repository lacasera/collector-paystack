const mix = require('laravel-mix');
const {react} = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js/app.js').react()
    .postCss('resources/css/collector.css', 'public/css/app.css', [
        require('tailwindcss')
    ]);

// mix.copyDirectory('resources/assets', 'public/assets');

mix.disableSuccessNotifications();
