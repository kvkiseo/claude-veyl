<?php
require __DIR__ . '/../api/_boot.php';
View::render('privacy', [
    'title'       => 'Privacy Policy — Veyl',
    'description' => 'What Veyl collects when you buy a guide, why, who else handles it, how long it is kept, and how to contact us about it.',
    'path'        => '/privacy/',
]);
