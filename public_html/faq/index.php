<?php
require __DIR__ . '/../api/_boot.php';
View::render('faq', [
    'title'       => 'Questions, answered — Veyl',
    'description' => 'What a fillable PDF is, what is inside a Veyl guide, how it reaches you, how discount codes work, how to pay, and the no-refund policy.',
    'nav'         => 'faq',
    'path'        => '/faq/',
]);
