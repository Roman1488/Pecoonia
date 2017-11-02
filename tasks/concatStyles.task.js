var gulp = require('gulp');
var concat = require('gulp-concat');
var minifyCSS = require('gulp-minify-css');
var gulpIf = require('gulp-if');

var Elixir = require('laravel-elixir');

var Task = Elixir.Task;

Elixir.extend('concatStyles', function(styles, dest) {

    new Task('concat-scripts', function() {

        return gulp.src(styles)
            .pipe(concat(dest))
            .pipe(gulpIf(Elixir.config.production, minifyCSS()))
            .pipe(gulp.dest(Elixir.config.js.outputFolder));
    }).watch(styles);

});