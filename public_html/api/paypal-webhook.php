<?php
/**
 * PayPal tells us here about completed payments, refunds, reversals and disputes.
 * Nothing is trusted until PayPal itself confirms the signature.
 *
 * This is the backup that stops a paid order being lost if the buyer closes the tab
 * before the capture call finishes.
 */
require __DIR__ . '/_boot.php';

$raw   = file_get_contents('php://input');
$event = json_decode($raw, true);

if (!is_array($event) || empty($event['id']) || empty($event['event_type'])) {
    V::json(['ok' => false], 400);
}

$header = function ($name) {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
};

$verify = PayPal::call('POST', '/v1/notifications/verify-webhook-signature', [
    'auth_algo'         => $header('PAYPAL-AUTH-ALGO'),
    'cert_url'          => $header('PAYPAL-CERT-URL'),
    'transmission_id'   => $header('PAYPAL-TRANSMISSION-ID'),
    'transmission_sig'  => $header('PAYPAL-TRANSMISSION-SIG'),
    'transmission_time' => $header('PAYPAL-TRANSMISSION-TIME'),
    'webhook_id'        => PayPal::webhookId(),
    'webhook_event'     => $event,
]);

if ((isset($verify['json']['verification_status']) ? $verify['json']['verification_status'] : '') !== 'SUCCESS') {
    error_log('Veyl webhook rejected: signature did not verify (' . $event['event_type'] . ')');
    V::json(['ok' => false], 400);
}

$db = V::db();

// Each event is handled once, however many times PayPal sends it.
$seen = $db->prepare('SELECT 1 FROM webhook_events WHERE id = ?');
$seen->execute([$event['id']]);
if ($seen->fetch()) {
    V::json(['ok' => true, 'duplicate' => true]);
}
$db->prepare('INSERT INTO webhook_events (id, type, received_at) VALUES (?,?,?)')
   ->execute([$event['id'], $event['event_type'], V::now()]);

$type     = $event['event_type'];
$resource = isset($event['resource']) ? $event['resource'] : [];

// ---- A payment completed. If our own capture call did not finish, rescue the order here.
if ($type === 'PAYMENT.CAPTURE.COMPLETED') {
    $publicId = isset($resource['custom_id']) ? $resource['custom_id'] : '';
    $order    = $publicId ? Orders::byPublicId($publicId) : null;

    if ($order && $order['status'] === 'created') {
        $paid = isset($resource['amount']['value']) ? (int) round($resource['amount']['value'] * 100) : 0;

        if ($paid !== (int) $order['amount_cents']) {
            error_log('Veyl webhook AMOUNT MISMATCH on ' . $order['public_id']);
            $db->prepare('UPDATE orders SET note = ? WHERE id = ?')
               ->execute(['Webhook amount mismatch: ' . $resource['amount']['value'], $order['id']]);
            V::json(['ok' => true, 'ignored' => 'amount mismatch']);
        }

        $fee = isset($resource['seller_receivable_breakdown']['paypal_fee']['value'])
            ? (int) round($resource['seller_receivable_breakdown']['paypal_fee']['value'] * 100)
            : 0;

        // The capture resource does not carry the payer's email, so read the order back.
        $email = '';
        $name  = '';
        $full  = PayPal::call('GET', '/v2/checkout/orders/' . $order['paypal_order_id']);
        if (!empty($full['json']['payer']['email_address'])) {
            $email = $full['json']['payer']['email_address'];
            $name  = trim(
                (isset($full['json']['payer']['name']['given_name']) ? $full['json']['payer']['name']['given_name'] : '') . ' ' .
                (isset($full['json']['payer']['name']['surname']) ? $full['json']['payer']['name']['surname'] : '')
            );
        }

        $db->prepare(
            'UPDATE orders SET status = "paid", capture_id = ?, buyer_email = ?, buyer_name = ?,
                    paid_at = ?, fee_cents = ?, note = ? WHERE id = ?'
        )->execute([
            isset($resource['id']) ? $resource['id'] : '',
            $email,
            $name,
            V::now(),
            $fee,
            trim(($order['note'] ? $order['note'] . ' | ' : '') . 'Completed by webhook'),
            $order['id'],
        ]);

        Orders::applyCommission($order['id']);

        $order = Orders::byPublicId($publicId);
        if ($email !== '' && V::validEmail($email)) {
            try {
                Orders::deliveryEmail($order);
            } catch (Throwable $e) {
                error_log('Veyl webhook delivery email failed: ' . $e->getMessage());
            }
        }
    }
    V::json(['ok' => true]);
}

// ---- A payment was undone. Close the download and void any unpaid commission.
$captureId = '';
if ($type === 'PAYMENT.CAPTURE.REFUNDED') {
    foreach ((isset($resource['links']) ? $resource['links'] : []) as $link) {
        if ($link['rel'] === 'up' && preg_match('#/captures/([A-Z0-9]+)$#', $link['href'], $m)) {
            $captureId = $m[1];
        }
    }
} elseif ($type === 'PAYMENT.CAPTURE.REVERSED' || $type === 'PAYMENT.CAPTURE.DENIED') {
    $captureId = isset($resource['id']) ? $resource['id'] : '';
} elseif ($type === 'CUSTOMER.DISPUTE.CREATED') {
    $captureId = isset($resource['disputed_transactions'][0]['seller_transaction_id'])
        ? $resource['disputed_transactions'][0]['seller_transaction_id']
        : '';
}

if ($captureId !== '') {
    $order = Orders::byCaptureId($captureId);
    if ($order && $order['status'] === 'paid') {
        if ($type === 'PAYMENT.CAPTURE.REFUNDED') {
            $amount = isset($resource['amount']['value']) ? (float) $resource['amount']['value'] : 0;
            if ((int) round($amount * 100) >= (int) $order['amount_cents']) {
                Orders::revoke($order, 'refunded', 'Full refund issued through PayPal');
            } else {
                $db->prepare('UPDATE orders SET note = ? WHERE id = ?')
                   ->execute(['Partial refund of ' . $amount . ' (download stays open)', $order['id']]);
            }
        } elseif ($type === 'CUSTOMER.DISPUTE.CREATED') {
            Orders::revoke($order, 'disputed', 'Dispute opened with PayPal');
        } else {
            Orders::revoke($order, 'refunded', 'Payment reversed or denied by PayPal');
        }
    }
}

V::json(['ok' => true]);
