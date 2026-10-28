<?php
require __DIR__ . '/api/_boot.php';
http_response_code(404);
View::render('notfound', [
    'title'   => 'Page not found — Veyl',
    'path'    => '/404',
    'noindex' => true,
]);
