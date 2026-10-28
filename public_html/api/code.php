<?php
/**
 * Validate a discount code and return the price it produces.
 * The browser only ever receives a price; it never sends one.
 */
require __DIR__ . '/_boot.php';

V::sameOrigin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    V::json(['ok' => false, 'error' => 'POST only.'], 405);
}

V::rateLimit('code', 20, 600);

$in = V::input();
V::checkCsrf($in);

$slug    = V::str(isset($in['slug']) ? $in['slug'] : '', 60);
$product = V::product($slug);
if (!$product) {
    V::json(['ok' => false, 'error' => 'Unknown guide.'], 404);
}
if (!V::isBuyable($product)) {
    V::json(['ok' => false, 'error' => 'This guide is not available to buy yet.'], 409);
}

$result = V::price(isset($in['code']) ? $in['code'] : '', $slug);

if (!$result['ok']) {
    V::json(['ok' => false, 'error' => $result['error']], 200);
}

V::json([
    'ok'       => true,
    'code'     => $result['code'],
    'label'    => $result['label'],
    'list'     => V::money($result['list']),
    'total'    => V::money($result['total']),
    'discount' => V::money($result['discount']),
]);
