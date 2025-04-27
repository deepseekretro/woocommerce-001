const gulp = require('gulp');
const terser = require('gulp-terser');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const clean = require('gulp-clean');

/**
 * Gulp task to minify JavaScript files and rename them with a '.min' suffix.
 *
 * @since 3.5.37
 */
function minifyJs() {
	return gulp.src('core/includes/js/**/*.js')
		.pipe(terser())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('core/includes/js'));
}

/**
 * Gulp task to minify gateways JavaScript files.
 *
 * @since 3.5.40
 */
function minifyGatewaysJs() {
	return gulp
		.src([
			'core/includes/gateways/**/*.js',
			'!core/includes/gateways/**/*.min.js',
		])
		.pipe(terser())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('core/includes/gateways'));
}

/**
 * Gulp task to minify CSS files and rename them with a '.min' suffix.
 *
 * @since 3.5.37
 */
function minifyCss() {
	return gulp.src('core/includes/css/**/*.css')
		.pipe(cleanCSS())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest('core/includes/css'));
}

/**
 * Gulp task to delete existing minified JS files.
 *
 * @since 3.5.37
 */
function cleanMinJsFiles() {
	return gulp.src('core/includes/js/**/*.min.js', { read: false, allowEmpty: true })
		.pipe(clean());
}

/**
 * Gulp task to delete existing minified CSS files.
 *
 * @since 3.5.37
 */
function cleanMinCssFiles() {
	return gulp.src('core/includes/css/**/*.min.css', { read: false, allowEmpty: true })
		.pipe(clean());
}
// Expose the minifyJs, minifyCss, and cleanMinFiles tasks to the Gulp CLI
exports.minifyJs  = gulp.series(cleanMinJsFiles, minifyJs, minifyGatewaysJs);
exports.minifyCss = gulp.series(cleanMinCssFiles, minifyCss);
