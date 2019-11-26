var gulp = require('gulp'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    insert = require('gulp-insert'),
    babel = require('gulp-babel'),
    uglify = require('gulp-uglify'),
    replace = require('gulp-replace'),
    sass = require('gulp-sass'),
    pipeline = require('readable-stream').pipeline;

//Gulp Script Concat
gulp.task('script', function () {
    return gulp.src([
        './assets/js/ua-parser.min.js',
        './assets/js/front.js'
    ])
        .pipe(concat('front.min.js'))
        .pipe(gulp.dest('./assets/js/'))
        .pipe(babel({presets: ['@babel/env']}))
        .pipe(uglify())
        .pipe(gulp.dest('./assets/js/'));
});

// global Task
gulp.task('default', gulp.parallel('script'));