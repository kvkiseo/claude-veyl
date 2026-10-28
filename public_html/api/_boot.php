<?php
/**
 * Shared start-up for every PHP page and endpoint.
 * Finds the private folder that sits NEXT TO the public web folder, whether it is
 * called "private_html" or "private".
 */
$base = dirname(__DIR__, 2);
$priv = getenv('VEYL_PRIVATE')
    ?: (is_dir($base . '/private_html') ? $base . '/private_html' : $base . '/private');

require_once $priv . '/lib/Veyl.php';
require_once $priv . '/lib/View.php';

// This file always lives in <public folder>/api, so its parent is the web root.
// View::asset() needs it to stamp stylesheets and scripts with their file date.
V::init($priv, dirname(__DIR__));

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_exception_handler(function ($e) {
    error_log('Veyl error: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    V::fail(500, 'Something went wrong on our side. You have not been charged unless PayPal tells you otherwise. Please try again, or contact us.');
});
