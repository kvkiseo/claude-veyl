<?php
require __DIR__ . '/../api/_boot.php';
View::render('disclaimer', [
    'title'       => 'Disclaimer — Veyl',
    'description' => 'Veyl guides are educational only. They are not legal, financial, or medical advice, and no outcome is promised.',
    'path'        => '/disclaimer/',
]);
