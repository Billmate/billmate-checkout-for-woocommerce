const path      = require( 'path' );
const webpack   = require( 'webpack' );
const DIST_PATH = path.resolve( './src/assets/js' );
const TerserPlugin = require('terser-webpack-plugin');

const config = {
    cache: true,
    entry: {
        "bco-checkout": './src/assets/js/bco-checkout.js',
    },
    output: {
        path: DIST_PATH,
        filename: '[name].min.js',
    },

    resolve: {
        modules: [ 'node_modules' ],
    },
    devtool: 'source-map',
    mode: process.env.NODE_ENV,
    plugins: [
        new webpack.NoEmitOnErrorsPlugin()
    ],
    stats: {
        colors: true,
    },
    externals: {
        jquery: 'jQuery',
    },
    optimization: {
        minimize: process.env.NODE_ENV === 'production',
        minimizer: [
            new TerserPlugin({
                parallel: true,
                sourceMap: true,
                terserOptions: {
                    output: {
                        comments: false,
                    },
                    compress: {
                        drop_console: true
                    }
                },
                extractComments: false,
            }),
        ],
    },
};
module.exports = config;
