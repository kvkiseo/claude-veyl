<?php
/**
 * Streams a product PDF after checking a signed, expiring token.
 * The PDFs live in private_html/kits and are never reachable by a web address.
 */
require __DIR__ . '/_boot.php';

V::rateLimit('download', 60, 600);

$days  = (int) V::cfg('link_ttl_days', 30);
$limit = (int) V::cfg('max_downloads', 10);

$payload = V::verify(isset($_GET['t']) ? $_GET['t'] : '', 'dl');
if (!$payload) {
    V::htmlPage(
        'This link has expired',
        'Download links work for ' . $days . ' days. You can ask for a new one on the download page.',
        410
    );
}

$order = Orders::byPublicId(isset($payload['o']) ? $payload['o'] : '');
if (!$order) {
    V::htmlPage('We could not find that order', 'Please check the link, or ask for a new one on the download page.', 404);
}
if ($order['status'] !== 'paid') {
    V::htmlPage(
        'This download is no longer available',
        'This order was refunded, reversed or is under review. If you think that is a mistake, please contact us.',
        403
    );
}
if ((int) $order['download_count'] >= $limit) {
    V::htmlPage(
        'Download limit reached',
        'This link has been used ' . $limit . ' times, which is the maximum. If you still need the file, please contact us and we will help.',
        429
    );
}

$file = V::productFile($order['product_slug']);
if (!$file) {
    error_log('Veyl: missing PDF for ' . $order['product_slug'] . ' on paid order ' . $order['public_id']);
    V::htmlPage(
        'That file is not ready yet',
        'This should not happen on a paid order. Please contact us with your order number and we will send it straight away.',
        503
    );
}

V::db()->prepare('UPDATE orders SET download_count = download_count + 1 WHERE id = ?')->execute([$order['id']]);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="veyl-' . $order['product_slug'] . '.pdf"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'none\'');
readfile($file);
