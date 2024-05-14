var gulp = require('gulp'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    inlineCss = require('gulp-inline-css');
    concat = require('gulp-concat'),
    insert = require('gulp-insert'),
    babel = require('gulp-babel'),
    uglify = require('gulp-uglify'),
    replace = require('gulp-replace'),
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

function mailStyle(done) {
    return gulp.src([
        './assets/dev/sass/mail.scss',
    ])
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(gulp.dest('./assets/css/'));
};
function inlineMailStyle(done) {
    return gulp.src('./assets/mail/*.html')
        .pipe(inlineCss())
        .pipe(gulp.dest('./assets/mail/build/'));
};



function buildScripts(done) {
    gulp.src([
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
    gulp.src(['./assets/dev/javascript/tracker.js'])
        .pipe(gulp.dest('./assets/js/')).pipe(babel({presets: ['@babel/env']})).pipe(replace("\\n", '')).pipe(replace("\\t", '')).pipe(replace("  ", '')).pipe(uglify()).pipe(gulp.dest('./assets/js/'));
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

// Gulp Watch
function watch() {
    gulp.watch('assets/dev/javascript/**/*.js', gulp.series(buildScripts));
    gulp.watch('assets/dev/sass/**/*.scss', gulp.series(buildStyles));
    console.log(" - Development is ready...")
}

// global Task
exports.compileSass = buildStyles;
exports.script = buildScripts;
exports.mce = tineMCE;
exports.frontScript = frontScripts;
exports.mailStyle = gulp.series(mailStyle ,inlineMailStyle)  ;
exports.concatScripts = concatScripts;
exports.minifyCss = minifyCss;
exports.watch = watch;

exports.default = gulp.series(watch);
