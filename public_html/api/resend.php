<?php
/**
 * "Lost your link?" The answer is the same whether or not an order exists,
 * so this form cannot be used to find out who has bought.
 */
require __DIR__ . '/_boot.php';

V::sameOrigin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    V::fail(405, 'POST only.');
}

$in   = V::input();
$json = V::wantsJson();
V::checkCsrf($in);
V::rateLimit('resend', 5, 3600);

$email = V::str(isset($in['email']) ? $in['email'] : '', 200);
if (!V::validEmail($email)) {
    if ($json) {
        V::json(['ok' => false, 'error' => 'Please enter a valid email address.'], 422);
    }
    V::htmlPage('Please check the form', 'Please enter a valid email address.', 422);
}

$stmt = V::db()->prepare(
    'SELECT * FROM orders WHERE lower(buyer_email) = lower(?) AND status = "paid" ORDER BY id DESC LIMIT 5'
);
$stmt->execute([$email]);

foreach ($stmt->fetchAll() as $order) {
    try {
        Orders::deliveryEmail($order);
    } catch (Throwable $e) {
        error_log('Veyl resend failed for ' . $order['public_id'] . ': ' . $e->getMessage());
    }
}

$message = 'If we found an order with that email address, we have sent the download link again. Please check your spam folder too.';
if ($json) {
    V::json(['ok' => true, 'message' => $message]);
}
V::htmlPage('Check your email', $message);
