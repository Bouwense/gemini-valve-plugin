const gulp = require('gulp');
const sass = require('gulp-dart-sass');
let autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync').create();
const watch = require('gulp-watch');
const cssmin = require('gulp-cssmin');
let sourcemaps = require('gulp-sourcemaps');
const babel = require('gulp-babel');
let livereload = require('gulp-livereload');

gulp.task('serve', function () {
    "use strict";
    browserSync.init({
        proxy: "http://stylemix.loc",
        host: "192.168.0.236",
        port: 3000,
        notify: true,
        ui: {
            port: 3001
        },
        open: false
    });
});

gulp.task('styles', function () {
    return gulp.src('./assets/scss/**/*.scss')
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(sourcemaps.init())
        .pipe(autoprefixer())
        .pipe(gulp.dest('./assets/css/'))
        .pipe(cssmin())
        .pipe(sourcemaps.write('../sourcemap'))
        .pipe(browserSync.stream())
        .pipe(livereload());
});

gulp.task('build_styles', function () {
    return gulp.src('./assets/scss/**/*.scss')
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(sourcemaps.init())
        .pipe(autoprefixer())
        .pipe(gulp.dest('./assets/css/'))
        .pipe(cssmin())
        .pipe(sourcemaps.write('../sourcemap'))
        .pipe(browserSync.stream())
        .pipe(livereload());
});

gulp.task('watch', function () {
    livereload.listen();
    return gulp.watch('./assets/scss/**/*.scss', gulp.parallel('styles'));
});

gulp.task('scripts', function () {
    livereload.listen();
    gulp.watch('./assets/es6/**/*.js').on('change', (e) => {
        console.log(e);
        gulp.src('./assets/es6/**/*.js')
            .pipe(babel({
                presets: ['@babel/env']
            }))
            .on('error', console.error.bind(console))
            .pipe(gulp.dest('./assets/js'))
    });
});

gulp.task('build_scripts', function () {
    return gulp.src('./assets/es6/**/*.js')
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .on('error', console.error.bind(console))
        .pipe(gulp.dest('./assets/js'));
});

gulp.task('default', gulp.parallel('watch', 'serve', 'styles', 'scripts'));

gulp.task('build', gulp.series(gulp.parallel('build_styles', 'build_scripts')));
