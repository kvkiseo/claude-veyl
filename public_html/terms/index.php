<?php
require __DIR__ . '/../api/_boot.php';
View::render('terms', [
    'title'       => 'Terms and No-Refund Policy — Veyl',
    'description' => 'The terms that apply when you buy a Veyl guide, including the no-refund policy for digital products delivered immediately.',
    'path'        => '/terms/',
]);
