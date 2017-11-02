var elixir = require('laravel-elixir');
var gulp = require('gulp');

require('./tasks/angular.task.js');
require('./tasks/bower.task.js');
require('./tasks/concatScripts.task.js');
require('./tasks/concatStyles.task.js');
require('laravel-elixir-livereload');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

gulp.task('fonts', function() {
    return gulp.src([
                    './bower_components/font-awesome/fonts/fontawesome-webfont.*'])
            .pipe(gulp.dest('./public/vendor/fonts/'));
});

gulp.task('build', ['fonts']);

elixir(function(mix) {
   
    var styles = [
                    './bower_components/font-awesome/css/font-awesome.min.css',
                    './assets/css/bootstrap.css',
                    './assets/css/animate.css',
                    './assets/css/magnific-popup.css',
                    './assets/css/radio-checkbox.css', 
                    './public/vendor/css/vendor.css',
                    './assets/css/new_styles.css',
                    './assets/css/flags.css'
                    ];

    var scripts = ['./assets/js/plugins.js', './assets/js/functions.js',
        './assets/js/jquery.gmap.js', './public/vendor/js/vendor.js', './public/js/timezones.js', './public/vendor/js/app.js'
        ,'./assets/js/fn_addition.js'
        ];
        
    mix    
        .bower('vendor.js', './public/vendor/js', 'vendor.css', './public/vendor/css')
        .angular('./angular/', './public/vendor/js', 'app.js')
        .concatScripts(scripts, './../public/vendor/js/final.min.js')
        .concatStyles(styles, './../public/vendor/css/final.min.css')
        .sass('dashboard.scss')
        .copy('./angular/app/html/**/*.html', 'public/vendor/html/')
        .livereload([
            'public/vendor/html/**/*.html',
        ], {liveCSS: true});
});