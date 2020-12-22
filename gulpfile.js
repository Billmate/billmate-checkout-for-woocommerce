/**
 * Base constants
 */
const { watch, series, src, dest } = require( 'gulp' );
const gulp                         = require( 'gulp' );
const sort                         = require('gulp-sort');
const pump                         = require( 'pump' );
const wpPot                        = require( 'gulp-wp-pot' );

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

gulp.task('makePOT', function () {
    return gulp.src('**/*.php')
    .pipe(sort())
    .pipe(wpPot({
        domain: 'billmate-checkout-for-woocommerce',
        destFile: 'src/languages/billmate-checkout-for-woocommerce.pot',
        package: 'billmate-checkout-for-woocommerce',
        bugReport: 'http://krokedil.se',
        lastTranslator: 'Krokedil <info@krokedil.se>',
        team: 'Krokedil <info@krokedil.se>'
    }))
    .pipe(gulp.dest('src/languages/billmate-checkout-for-woocommerce.pot'));
});

exports.style   = style;
exports.script  = script;
exports.watch   = watchfiles;
exports.default = series( setProdEnv, style, script );
