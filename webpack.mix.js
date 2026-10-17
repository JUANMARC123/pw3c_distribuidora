const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 | Environment variables prefixed with MIX_ are automatically available
 | in the frontend as process.env.MIX_VAR_NAME at build time.
 */

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        //
    ])
    .webpackConfig({
        // Ensure process.env is available for MIX_ variables
        plugins: [
            new (require('webpack')).DefinePlugin({
                'process.env.MIX_FIREBASE_API_KEY': JSON.stringify(process.env.MIX_FIREBASE_API_KEY || ''),
                'process.env.MIX_FIREBASE_DATABASE_URL': JSON.stringify(process.env.MIX_FIREBASE_DATABASE_URL || ''),
            })
        ]
    });
