<?php
/** The confirmation page asks here for the details of one paid order. */
require __DIR__ . '/_boot.php';

V::rateLimit('links', 60, 600);

$payload = V::verify(isset($_GET['t']) ? $_GET['t'] : '', 'ord');
$order   = $payload ? Orders::byPublicId($payload['o']) : null;

if (!$order || $order['status'] !== 'paid') {
    V::json(['ok' => false], 404);
}

V::json([
    'ok'      => true,
    'orderId' => $order['public_id'],
    'email'   => $order['buyer_email'],
    'product' => $order['product_name'],
    'total'   => V::money($order['amount_cents']),
    'url'     => Orders::downloadUrl($order),
]);
