const path = require('path');

module.exports = {
    resolve: {
        alias: {
            //'vue$': 'vue/dist/vue.runtime.js',
            '@': path.resolve('resources/js'),
        },
    },
    //output: { chunkFilename: 'js/[name].[contenthash].js' },
};
