<?php
require __DIR__ . '/../api/_boot.php';
View::render('thankyou', [
    'title'       => 'Your download — Veyl',
    'description' => 'Your Veyl download link.',
    'path'        => '/thank-you/',
    'noindex'     => true,
]);
