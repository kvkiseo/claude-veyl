<?php
require __DIR__ . '/../api/_boot.php';
View::render('products', [
    'title'       => 'All guides — Veyl',
    'description' => 'Every Veyl guide: one fillable PDF for one costly decision, $99 each. Remodel bids, car buying, small business, franchises, assisted living, weddings, solar, agent commission, timeshares and IVF.',
    'nav'         => 'products',
    'path'        => '/products/',
]);
