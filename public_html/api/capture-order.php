<?php
/**
 * Step 2. Take the money on the server, check it matches what we asked for,
 * record the order, and email the download link.
 *
 * GET  — the buyer coming back from PayPal without JavaScript.
 * POST — the PayPal buttons, after the buyer approves in the pop-up.
 */
require __DIR__ . '/_boot.php';

$isGet = $_SERVER['REQUEST_METHOD'] === 'GET';
$in    = $isGet ? $_GET : V::input();

if (!$isGet) {
    V::sameOrigin();
    V::checkCsrf($in);
}

V::rateLimit('capture', 30, 600);

$paypalId = V::str(isset($in['orderID']) ? $in['orderID'] : (isset($in['token']) ? $in['token'] : ''), 40);
if (!preg_match('/^[A-Z0-9]{10,30}$/', $paypalId)) {
    V::fail(400, 'That checkout link is missing its order reference.');
}

$order = Orders::byPaypalId($paypalId);
if (!$order) {
    V::fail(404, 'We could not find that order.');
}

$failBack = V::siteUrl() . '/products/' . $order['product_slug'] . '/?pay=failed#buy';

/** Finish: hand the buyer a signed, expiring link to their confirmation page. */
function finish($order, $isGet)
{
    $token = V::sign(['t' => 'ord', 'o' => $order['public_id']], V::linkTtl());
    if ($isGet) {
        V::redirect(V::siteUrl() . '/thank-you/#t=' . rawurlencode($token));
    }
    V::json([
        'ok'      => true,
        'token'   => $token,
        'orderId' => $order['public_id'],
        'total'   => V::money($order['amount_cents']),
    ]);
}

// Already captured, perhaps by the webhook or a refreshed tab: give the same answer again.
if ($order['status'] === 'paid') {
    finish($order, $isGet);
}
if ($order['status'] !== 'created') {
    V::fail(409, 'This order cannot be completed. Please contact us with order ' . $order['public_id'] . '.');
}

$res = PayPal::call(
    'POST',
    '/v2/checkout/orders/' . $paypalId . '/capture',
    new stdClass(),
    ['PayPal-Request-Id: capture-' . $order['public_id']]
);

$result  = $res['json'];
$capture = isset($result['purchase_units'][0]['payments']['captures'][0])
    ? $result['purchase_units'][0]['payments']['captures'][0]
    : null;

// PayPal says it was already captured: read the order back instead of failing.
if (!$capture && (isset($result['details'][0]['issue']) ? $result['details'][0]['issue'] : '') === 'ORDER_ALREADY_CAPTURED') {
    $res     = PayPal::call('GET', '/v2/checkout/orders/' . $paypalId);
    $result  = $res['json'];
    $capture = isset($result['purchase_units'][0]['payments']['captures'][0])
        ? $result['purchase_units'][0]['payments']['captures'][0]
        : null;
}

$orderStatus   = isset($result['status']) ? $result['status'] : '';
$captureStatus = isset($capture['status']) ? $capture['status'] : '';

if (!$capture || $orderStatus !== 'COMPLETED' || $captureStatus !== 'COMPLETED') {
    error_log('Veyl capture failed: HTTP ' . $res['status'] . ' ' . $res['raw']);
    $issue = isset($result['details'][0]['issue']) ? $result['details'][0]['issue'] : '';
    $why = $issue === 'INSTRUMENT_DECLINED'
        ? 'PayPal declined that payment method. Please try another one. You have not been charged.'
        : 'The payment was not completed. You have not been charged. Please try again.';
    if ($isGet) {
        V::redirect($failBack);
    }
    V::json(['ok' => false, 'error' => $why], 422);
}

// What PayPal actually took must equal what we asked for.
if ($capture['amount']['value'] !== V::usd($order['amount_cents'])
    || $capture['amount']['currency_code'] !== $order['currency']) {
    error_log('Veyl AMOUNT MISMATCH on order ' . $order['public_id'] . ': PayPal took ' . $capture['amount']['value']);
    V::db()->prepare('UPDATE orders SET note = ? WHERE id = ?')
        ->execute(['Amount mismatch: PayPal reported ' . $capture['amount']['value'], $order['id']]);
    V::fail(409, 'The amount did not match our records. Nothing has been delivered. Please contact us with order ' . $order['public_id'] . '.');
}

$email = isset($result['payer']['email_address']) ? $result['payer']['email_address'] : '';
$name  = trim(
    (isset($result['payer']['name']['given_name']) ? $result['payer']['name']['given_name'] : '') . ' ' .
    (isset($result['payer']['name']['surname']) ? $result['payer']['name']['surname'] : '')
);
$fee = isset($capture['seller_receivable_breakdown']['paypal_fee']['value'])
    ? (int) round($capture['seller_receivable_breakdown']['paypal_fee']['value'] * 100)
    : 0;

V::db()->prepare(
    'UPDATE orders SET status = "paid", capture_id = ?, buyer_email = ?, buyer_name = ?, paid_at = ?, fee_cents = ? WHERE id = ?'
)->execute([$capture['id'], $email, $name, V::now(), $fee, $order['id']]);

Orders::applyCommission($order['id']);

$order = Orders::byPaypalId($paypalId);

if ($email !== '' && V::validEmail($email)) {
    try {
        Orders::deliveryEmail($order);
    } catch (Throwable $e) {
        // The buyer still gets the link on screen, so a mail failure must not break checkout.
        error_log('Veyl delivery email failed for ' . $order['public_id'] . ': ' . $e->getMessage());
    }
}

finish($order, $isGet);
