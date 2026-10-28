<?php
require __DIR__ . '/api/_boot.php';
View::render('home', [
    'title'       => 'Veyl — fillable decision guides for the choices you make once',
    'description' => 'Ten fillable PDF guides, $99 each. Lessons, script, forms and a checklist for one big decision: a remodel, a car, a franchise, assisted living, solar, a wedding and more.',
    'nav'         => 'home',
    'path'        => '/',
]);
