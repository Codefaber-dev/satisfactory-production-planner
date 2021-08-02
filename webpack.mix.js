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
 */

// mix.browserSync({
//     proxy: 'satis-pp.test',
//     host: 'satis-pp.test',
//     open: false,
//     https: true,
//     socket : {
//         domain: "https:/satis-pp.test:3000"
//     },
//     options : {
//         clicks : false
//     }
// });

mix.js('resources/js/app.js', 'public/js').vue()
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
    ])
    .webpackConfig(require('./webpack.config'));
    // .browserSync('https://satis-pp.test');

if (mix.inProduction()) {
    mix.version();
}
