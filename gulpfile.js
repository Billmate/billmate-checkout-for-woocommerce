/**
 * Base constants
 */
const { watch, series, src, dest } = require( 'gulp' );
const pump                         = require( 'pump' );

/**
 * SCSS
 */
const postcss              = require( 'gulp-postcss' );
const sass                 = require( 'gulp-sass' );
const autoprefixer         = require( 'autoprefixer' );
const postcssFlexbugsFixes = require( 'postcss-flexbugs-fixes' );
const postcssImport        = require( 'postcss-import' );
const cssNano              = require( 'cssnano' );

function style( cb ) {
    return pump(
        [
            src( 'src/assets/sass/**/*.scss', { sourcemaps: true } ),
            sass().on( 'error', sass.logError ),
            postcss(
                [
                    autoprefixer,
                    postcssFlexbugsFixes,
                    postcssImport,
                    cssNano(
                        {
                            preset: [
                                'default',
                                {
                                    normalizeWhitespace:
                                        process.env.NODE_ENV === 'production',
                                },
                            ],
                        }
                    ),
                ]
            ),
            dest( './assets/css/', { sourcemaps: '.' } ),
        ],
        cb
    );
}

/**
 * JS
 * Handled by webpack
 */
const webpack       = require( 'webpack' );
const webpackStream = require( 'webpack-stream' );

function script( cb ) {
    pump(
        [
            src( 'src/assets/js/*.js' ),
            webpackStream(
                { config: require( './webpack.config.babel.js' ) },
                webpack
            ),
            dest( './src/assets/js/' ),
        ],
        cb
    );
}

/**
 * General tasks
 * watch and node_env setters
 */
function watchfiles() {
    setDevEnv();
    watch( 'src/assets/sass/**/*.scss', style );
    watch( 'src/assets/js/bco-checkout.js', script );
}

function setDevEnv() {
    process.env.NODE_ENV = 'development';
}

function setProdEnv( cb ) {
    process.env.NODE_ENV = 'production';
    cb();
}

exports.style   = style;
exports.script  = script;
exports.watch   = watchfiles;
exports.default = series( setProdEnv, style, script );
