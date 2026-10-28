<?php
/**
 * Local testing only. PHP's built-in web server does not read .htaccess,
 * so this script applies the same two rewrites.
 *
 * From the folder that holds public_html and private_html:
 *   php -S localhost:8000 -t public_html private_html/tools/router.php
 *
 * Then open http://localhost:8000
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = $_SERVER['DOCUMENT_ROOT'];

// A real file: let the built-in server serve it.
if ($path !== '/' && is_file($root . $path)) {
    return false;
}

if (preg_match('#^/products/([a-z0-9][a-z0-9-]{1,78})/?$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require $root . '/products/view.php';
    return true;
}

if ($path === '/sitemap.xml') {
    require $root . '/sitemap.php';
    return true;
}

if ($path === '/llms.txt') {
    require $root . '/llms.php';
    return true;
}

$index = rtrim($root . $path, '/') . '/index.php';
if (is_file($index)) {
    require $index;
    return true;
}

http_response_code(404);
require $root . '/404.php';
return true;
