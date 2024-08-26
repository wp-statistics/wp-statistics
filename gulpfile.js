var gulp = require('gulp'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    insert = require('gulp-insert'),
    babel = require('gulp-babel'),
    uglify = require('gulp-uglify'),
    replace = require('gulp-replace'),
    path = require('path'),
    shell = require('gulp-shell'),
    pipeline = require('readable-stream').pipeline;
const sass = require('gulp-sass')(require('sass'));

// Now you can use gulpSass in your gulp tasks

function buildStyles(done) {
    return gulp.src([
        './assets/dev/sass/admin.scss',
        './assets/dev/sass/rtl.scss',
        './assets/dev/sass/frontend.scss',
    ])
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('./assets/css/'));
};

function buildScripts(done) {
    gulp.src([
        './assets/dev/javascript/plugin/*.js',
        './assets/dev/javascript/config.js',
        './assets/dev/javascript/ajax.js',
        './assets/dev/javascript/placeholder.js',
        './assets/dev/javascript/helper.js',
        './assets/dev/javascript/components/*.js',
        './assets/dev/javascript/meta-box.js',
        './assets/dev/javascript/meta-box/*.js',
        './assets/dev/javascript/pages/*.js',
        './assets/dev/javascript/run.js',
        './assets/dev/javascript/image-upload.js',
    ])
        .pipe(uglify())
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
    done()
}

// Gulp TinyMce Script
function tineMCE(done) {
    gulp.src(['./assets/dev/javascript/Tinymce/*.js'])
        .pipe(concat('tinymce.min.js'))
        .pipe(gulp.dest('./assets/js/')).pipe(babel({presets: ['@babel/env']})).pipe(replace("\\n", '')).pipe(replace("\\t", '')).pipe(replace("  ", '')).pipe(uglify()).pipe(gulp.dest('./assets/js/'));
    done()
}

// Gulp Frontend Script
function frontScripts(done) {
    const jsFiles = [
        './assets/dev/javascript/tracker.js',
    ];

    // Process for modern browsers (ES6)
    gulp.src(jsFiles)
        .pipe(replace("\\n", ''))
        .pipe(replace("\\t", ''))
        .pipe(replace("  ", ''))
        .pipe(uglify())
        .pipe(gulp.dest('./assets/js/'));

    done()
}


// Gulp Frontend Script
function miniChart(done) {
    const jsFiles = [
        './assets/dev/javascript/mini-chart.js'
    ];

    // Process for modern browsers (ES6)
    gulp.src(jsFiles)
        .pipe(replace("\\n", ''))
        .pipe(replace("\\t", ''))
        .pipe(replace("  ", ''))
        .pipe(uglify())
        .pipe(gulp.dest('./assets/js/'));

    done()
}


// Gulp charts Script
function chartScripts(done) {
    gulp.src([
        './assets/dev/javascript/plugin/chartjs-adapter-date-fns.bundle.min.js',
        './assets/dev/javascript/plugin/chartjs-chart-matrix.min.js'])
        .pipe(concat('chart-matrix.min.js'))
        .pipe(gulp.dest('./assets/js/chartjs')).pipe(babel({presets: ['@babel/env']})).pipe(replace("\\n", '')).pipe(replace("\\t", '')).pipe(replace("  ", '')).pipe(uglify()).pipe(gulp.dest('./assets/js/chartjs/'));
    done()
}

// Gulp Script Minify
function concatScripts(done) {
    gulp.src(['./assets/js/*.js', '!./assets/js/*.min.js'])
        .pipe(babel({presets: ['@babel/env']}))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
    done()
}

// Gulp Css Minify
function minifyCss(done) {
    gulp.src(['./assets/css/*.css', '!./assets/css/*.min.css'])
        .pipe(cleanCSS({
            keepSpecialComments: 1,
            level: 2
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
    done()
}

function addEsnextSuffix(filePath) {
    const lastDotIndex = filePath.lastIndexOf('.');
    if (lastDotIndex === -1) return `${filePath}.esnext`;
    const base = filePath.substring(0, lastDotIndex);
    const ext = filePath.substring(lastDotIndex);
    return `${base}.esnext${ext}`;
}

function revertToES6(cb) {
    const jsFiles = [
        './assets/js/tracker.js',
    ];

    const tasks = jsFiles.map(file => {
        const outputFile = addEsnextSuffix(file);
        return `lebab --transform arrow,let,template ${file} -o ${outputFile}`;
    });

    shell.task(tasks)();
    cb();
}

// Gulp Watch
function watch() {
    gulp.watch('assets/dev/javascript/**/*.js', gulp.series(buildScripts));
    gulp.watch('assets/dev/javascript/mini-chart.js', gulp.series(miniChart));
    gulp.watch('assets/dev/sass/**/*.scss', gulp.series(buildStyles));
    console.log(" - Development is ready...")
}

// global Task
exports.compileSass = buildStyles;
exports.script = buildScripts;
exports.chartScript = chartScripts;
exports.mce = tineMCE;
exports.frontScript = frontScripts;
exports.miniChart = miniChart;
exports.concatScripts = concatScripts;
exports.minifyCss = minifyCss;
exports.revertToES6 = revertToES6;
exports.watch = watch;

exports.default = gulp.series(watch);
