var gulp = require('gulp'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    insert = require('gulp-insert'),
    babel = require('gulp-babel'),
    uglify = require('gulp-uglify'),
    replace = require('gulp-replace'),
    sass = require('gulp-sass')(require('sass')),
    pipeline = require('readable-stream').pipeline;

// Gulp Sass Compiler
sass.compiler = require('node-sass');
gulp.task('sass', function () {
    return gulp.src([
        './assets/dev/sass/admin.scss',
        './assets/dev/sass/jquery-datepicker/datepicker.scss',
        './assets/dev/sass/rtl.scss',
        './assets/dev/sass/frontend.scss',
    ])
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(['./assets/css/']));
});

//Gulp Script Concat
gulp.task('script', function () {
    return gulp.src([
        './assets/dev/javascript/plugin/*.js',
        './assets/dev/javascript/config.js',
        './assets/dev/javascript/ajax.js',
        './assets/dev/javascript/placeholder.js',
        './assets/dev/javascript/helper.js',
        './assets/dev/javascript/meta-box.js',
        './assets/dev/javascript/meta-box/*.js',
        './assets/dev/javascript/pages/*.js',
        './assets/dev/javascript/run.js',
    ])
        .pipe(concat('admin.min.js'))
        .pipe(insert.prepend('jQuery(document).ready(function ($) {'))
        .pipe(insert.append('});'))
        .pipe(gulp.dest('./assets/js/'))
        .pipe(babel({presets: ['@babel/env']}))
        .pipe(replace("\\n", ''))
        .pipe(replace("\\t", ''))
        .pipe(replace("  ", ''))
        .pipe(uglify())
        .pipe(gulp.dest('./assets/js/'));
});

// Gulp TinyMce Script
gulp.task('mce', function () {
    return gulp.src(['./assets/dev/javascript/Tinymce/*.js'])
        .pipe(concat('tinymce.min.js'))
        .pipe(gulp.dest('./assets/js/')).pipe(babel({presets: ['@babel/env']})).pipe(replace("\\n", '')).pipe(replace("\\t", '')).pipe(replace("  ", '')).pipe(uglify()).pipe(gulp.dest('./assets/js/'));
});

// Gulp Script Minify
gulp.task('js', function () {
    return gulp.src(['./assets/js/*.js', '!./assets/js/*.min.js'])
        .pipe(babel({presets: ['@babel/env']}))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
});

// Gulp Css Minify
gulp.task('css', function () {
    return gulp.src(['./assets/css/*.css', '!./assets/css/*.min.css'])
        .pipe(cleanCSS({
            keepSpecialComments: 1,
            level: 2
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
});

// Gulp Watch
gulp.task('watch', function () {
    gulp.watch('assets/dev/javascript/**/*.js', gulp.series('script'));
    gulp.watch('assets/dev/sass/**/*.scss', gulp.series('sass'));
});

// global Task
gulp.task('default', gulp.parallel('sass', 'script', 'mce'));