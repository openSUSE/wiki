"use strict";

var gulp = require("gulp");
var browserify = require("browserify");
var log = require("gulplog");
var tap = require("gulp-tap");
var buffer = require("gulp-buffer");
var sourcemaps = require("gulp-sourcemaps");
var uglify = require("gulp-uglify");
var sass = require("gulp-sass");
var svgmin = require("gulp-svgmin");

// Compile JavaScripts with sourcemaps
gulp.task("js", function() {
	return gulp
		.src("src/js/index.js", { read: false })
		.pipe(
			tap(function(file) {
				log.info("bundling " + file.path);
				file.contents = browserify(file.path, { debug: true }).bundle();
			})
		)
		.pipe(buffer())
		.pipe(sourcemaps.init({ loadMaps: true }))
		.pipe(uglify())
		.pipe(sourcemaps.write("./"))
		.pipe(gulp.dest("dist/js"));
});

// Compile SaSS stylesheets with sourcemaps
gulp.task("sass", function() {
	return gulp
		.src("src/sass/**/*.scss")
		.pipe(sourcemaps.init())
		.pipe(
			sass({
				outputStyle: "compressed",
				includePaths: ["node_modules"]
			}).on("error", sass.logError)
		)
		.pipe(sourcemaps.write("./"))
		.pipe(gulp.dest("dist/css"));
});

// Minify SVG images
gulp.task("svg", function() {
	return gulp
		.src("src/images/**/*.svg")
		.pipe(svgmin())
		.pipe(gulp.dest("dist/images"));
});

// Copy font files
gulp.task("copy", function() {
	return gulp
		.src(["node_modules/opensuse-theme-chameleon/dist/fonts/**/*"])
		.pipe(gulp.dest("dist/fonts"));
});

// Build all
gulp.task("default", gulp.parallel("js", "sass", "svg"));

// Watch all
gulp.task("watch", function() {
	gulp.watch("src/sass/**/*.scss", gulp.parallel("sass"));
	gulp.watch("src/js/**/*.js", gulp.parallel("js"));
	gulp.watch("src/images/**/*.svg", gulp.parallel("svg"));
});
