<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__.$uri) && !preg_match('/\.php$/', $uri)) {
    return false;
}

session_save_path(__DIR__.'/sessions');

if (preg_match('/\.php$/', $uri)) {
    require_once __DIR__.$uri;
} else {
    require_once __DIR__.'/index.php';
}
