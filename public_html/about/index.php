<?php
require __DIR__ . '/../api/_boot.php';
View::render('about', [
    'title'       => 'About Veyl — calm, practical decision guides',
    'description' => 'Veyl publishes fillable PDF guides for large, hard-to-undo decisions. What a guide contains, who it is for, and what it is not.',
    'nav'         => 'about',
    'path'        => '/about/',
]);
