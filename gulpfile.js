// Require all the things (that we need)
var gulp = require('gulp');
var sort = require('gulp-sort');
var wp_pot = require('gulp-wp-pot');

// Create the .pot translation file
gulp.task('translate', function () {
    gulp.src('**/*.php')
        .pipe(sort())
        .pipe(wp_pot( {
            domain: 'diff-user-language',
            destFile:'diff-user-language.pot',
            package: 'Different_User_Language',
            bugReport: 'https://github.com/bamadesigner/different-user-language/issues',
            lastTranslator: 'Rachel Carden <bamadesigner@gmail.com>',
            team: 'Rachel Carden <bamadesigner@gmail.com>',
            headers: false
        } ))
        .pipe(gulp.dest('languages'));
});

// Let's get this party started
gulp.task('default', ['translate']);