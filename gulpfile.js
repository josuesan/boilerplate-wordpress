/**
 * IMPORT MODULES
 */
require('dotenv').config();
const gulp = require('gulp'),
    plumber = require('gulp-plumber'),
    sass = require('gulp-sass'),
    postcss = require('gulp-postcss'),
    cssmin = require('gulp-minify-css'),
    autoprefixer = require('autoprefixer'),
    php = require('gulp-connect-php'),
    sassGlob = require('gulp-sass-glob'),
    sassLint = require('gulp-sass-lint'),
    groupmq = require('gulp-group-css-media-queries'),
    bs = require('browser-sync'),
    livereload = require('gulp-livereload'),
    del = require('del'),
    uglify = require('gulp-uglify'),
    rigger = require('gulp-rigger');

/**
 * URL_SITE FOR SASS, JS FILES
 */
const THEME_NAME = process.env.THEME_NAME;

const SASS_ORIGIN__ALL_SOURCES = 'wp-content/themes/' + THEME_NAME + '/assets/scss/**/*.scss',
    SASS_ORIGIN_SOURCES = ['wp-content/themes/' + THEME_NAME + '/assets/scss/*.scss'],
    SASS_DESTINATION_SOURCES = 'wp-content/themes/' + THEME_NAME + '/assets/css';

const JS_ORIGIN__ALL_SOURCES = 'wp-content/themes/' + THEME_NAME + '/assets/js/**/*.js',
    JS_ORIGIN_SOURCES = ['wp-content/themes/' + THEME_NAME + '/assets/js/*.js',],
    JS_DESTINATION_SOURCES = 'wp-content/themes/' + THEME_NAME + '/assets/js/min';

const PHP_ORIGIN__ALL_SOURCES = 'wp-content/themes/' + THEME_NAME + '/*.php';
/**
 * Compile Sass for livereload files
 */
gulp.task('compile:sass', ['clean:styles', "sass:lint"], () =>
    gulp.src(SASS_ORIGIN_SOURCES)
        .pipe(plumber()) // Prevent termination on error
        .pipe(sassGlob())
        .pipe(sass({
            indentType: 'tab',
            indentWidth: 1,
            outputStyle: 'compressed', // Expanded so that our CSS is readable
        })).on('error', sass.logError)
        .pipe(postcss([
            autoprefixer({
                cascade: false,
            })
        ]))
        .pipe(groupmq())
        .pipe(cssmin())
        .pipe(gulp.dest(SASS_DESTINATION_SOURCES)) // Output compiled files in the same dir as Sass sources
        .pipe(livereload()));
/**
 * Compile for server Sass files
 */
gulp.task('compile-server:sass', ['clean:styles', "sass:lint"], () =>
    gulp.src(SASS_ORIGIN_SOURCES)
        .pipe(plumber()) // Prevent termination on error
        .pipe(sassGlob())
        .pipe(sass({
            indentType: 'tab',
            indentWidth: 1,
            outputStyle: 'compressed', // Expanded so that our CSS is readable
        })).on('error', sass.logError)
        .pipe(postcss([
            autoprefixer({
                cascade: false,
            })
        ]))
        .pipe(groupmq())
        .pipe(cssmin())
        .pipe(gulp.dest(SASS_DESTINATION_SOURCES))
        .pipe(bs.stream())); // Stream to browserSync

/**
 * Clean compiled js task
*/
gulp.task('compile:js', ['clean:js'], () =>
    gulp.src(JS_ORIGIN_SOURCES)
        .pipe(rigger())
        .pipe(uglify())
        .pipe(gulp.dest(JS_DESTINATION_SOURCES)));
/**
 * Start up browserSync and watch Sass files for changes 
 */
gulp.task('watch-server:sass', ['php', 'compile-server:sass'], () => {
    bs.init({
        proxy: process.env.SITE_URL,
        host: "127.0.0.1",
        port: 3000,
        baseDir: "./",
        open: "external",
        notify: true,
        watchOptions: {
            debounceDelay: 1000
        }
    });
    gulp.watch(SASS_ORIGIN__ALL_SOURCES, ['compile-server:sass']);
    gulp.watch(JS_ORIGIN__ALL_SOURCES, ['compile:js']);
    gulp.watch(PHP_ORIGIN__ALL_SOURCES, bs.reload);
});

gulp.task('watch:sass', ['compile:sass'], () => {
    // Start a livereload server
    livereload.listen();
    //gulp.watch(SASS_ORIGIN_SOURCES, ['compile:sass']);
    gulp.watch(SASS_ORIGIN__ALL_SOURCES, ['compile:sass']);
    gulp.watch(JS_ORIGIN__ALL_SOURCES, ['compile:js']);
});

/**
 * Lint Sass
 */
gulp.task('reload', function () {
    return livereload.reload();
});

/**
 * PHP SERVER
 */
gulp.task('php', function () {
    php.server({ base: '.', port: process.env.PORT, keepalive: true });
});

/**
 * Lint Sass
 */
gulp.task('sass:lint', () =>
    gulp.src(SASS_ORIGIN_SOURCES)
        .pipe(plumber())
        .pipe(sassLint())
        .pipe(sassLint.format()));

/**
 * Clean compiled task
 */
gulp.task('clean:styles', () =>
    del([SASS_DESTINATION_SOURCES + '/style.css', SASS_DESTINATION_SOURCES + 'style.min.css']));
/**
 * Clean compiled task
 */
gulp.task('clean:js', () =>
    del([JS_DESTINATION_SOURCES + '**.min.js']));
/**
 * Default task executed by running `gulp`
 */

gulp.task('default', ['watch:sass', 'compile:js']);
gulp.task('server', ['watch-server:sass', 'compile:js']);