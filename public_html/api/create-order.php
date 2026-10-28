<?php
/**
 * Step 1 of checkout. The amount is worked out HERE, from products.json and codes.json.
 * The browser never sends a price.
 *
 * Two ways in:
 *   - JSON (the PayPal buttons): returns { id: "<PayPal order id>" }
 *   - a normal form post (no JavaScript): redirects the buyer to PayPal
 */
require __DIR__ . '/_boot.php';

V::sameOrigin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    V::fail(405, 'POST only.');
}

V::rateLimit('order', 20, 600);

$in   = V::input();
$json = V::wantsJson();
V::checkCsrf($in);

$slug    = V::str(isset($in['slug']) ? $in['slug'] : '', 60);
$product = V::product($slug);
if (!$product) {
    V::fail(404, 'Unknown guide.');
}

$back = V::siteUrl() . '/products/' . $slug . '/';

if (!V::isBuyable($product)) {
    if ($json) {
        V::json(['ok' => false, 'error' => 'This guide is not available to buy yet.'], 409);
    }
    V::redirect($back . '#buy');
}

// The buyer must confirm they understand all sales are final.
$consentText = 'I understand this is a digital product delivered immediately, all sales are final, and I have read the Terms and Disclaimer.';
$consent = !empty($in['consent']);
if (!$consent) {
    if ($json) {
        V::json(['ok' => false, 'error' => 'Please tick the box to confirm you have read the Terms and Disclaimer.'], 422);
    }
    V::redirect($back . '?pay=consent#buy');
}

$price = V::price(isset($in['code']) ? $in['code'] : '', $slug);
if (!$price['ok']) {
    if ($json) {
        V::json(['ok' => false, 'error' => $price['error']], 422);
    }
    V::redirect($back . '?pay=code_invalid#buy');
}

$publicId = Orders::newPublicId();
$currency = V::pricing('currency');

$body = [
    'intent'         => 'CAPTURE',
    'purchase_units' => [[
        'reference_id' => $slug,
        'custom_id'    => $publicId,
        'invoice_id'   => $publicId,
        'description'  => mb_substr('Veyl guide: ' . $product['name'], 0, 120),
        'amount'       => ['currency_code' => $currency, 'value' => V::usd($price['total'])],
    ]],
];

// Without JavaScript the buyer is sent to PayPal and comes back to capture-order.php.
if (!$json) {
    $body['payment_source'] = ['paypal' => ['experience_context' => [
        'brand_name'          => 'Veyl',
        'user_action'         => 'PAY_NOW',
        'shipping_preference' => 'NO_SHIPPING',
        'return_url'          => V::siteUrl() . '/api/capture-order.php',
        'cancel_url'          => $back . '?pay=canceled#buy',
    ]]];
}

$res = PayPal::call('POST', '/v2/checkout/orders', $body, ['PayPal-Request-Id: create-' . $publicId]);

if (empty($res['json']['id'])) {
    error_log('Veyl create-order failed: HTTP ' . $res['status'] . ' ' . $res['raw']);
    if ($json) {
        V::json(['ok' => false, 'error' => 'Checkout could not be started. Please try again in a moment.'], 502);
    }
    V::redirect($back . '?pay=failed#buy');
}

V::db()->prepare(
    'INSERT INTO orders
        (public_id, paypal_order_id, status, product_slug, product_name,
         list_cents, discount_cents, amount_cents, currency,
         code, influencer, consent, consent_text, created_at)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
)->execute([
    $publicId,
    $res['json']['id'],
    'created',
    $slug,
    $product['name'],
    $price['list'],
    $price['discount'],
    $price['total'],
    $currency,
    $price['code'],
    $price['influencer'],
    1,
    $consentText,
    V::now(),
]);

if ($json) {
    V::json(['ok' => true, 'id' => $res['json']['id']]);
}

$approve = '';
foreach ($res['json']['links'] as $link) {
    if (in_array($link['rel'], ['payer-action', 'approve'], true)) {
        $approve = $link['href'];
        break;
    }
}
if ($approve === '') {
    V::redirect($back . '?pay=failed#buy');
}
V::redirect($approve);
