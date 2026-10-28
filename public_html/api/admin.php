<?php
/**
 * Reports, for you only. Open with your admin key from config.php:
 *
 *   /api/admin.php?key=YOUR_ADMIN_KEY&report=payouts&month=2026-11
 *   /api/admin.php?key=YOUR_ADMIN_KEY&report=orders
 *
 * The same reports are available on the command line, with no web access at all:
 *   php private_html/tools/payouts.php 2026-11
 */
require __DIR__ . '/_boot.php';

header('X-Robots-Tag: noindex, nofollow');
V::rateLimit('admin', 10, 600);

$key      = isset($_GET['key']) ? (string) $_GET['key'] : '';
$expected = (string) V::cfg('admin_key', '');

// A short or unset key is never accepted, and a wrong key looks like a missing page.
if (strlen($expected) < 24 || strlen($key) < 24 || !hash_equals($expected, $key)) {
    V::htmlPage('Not found', 'There is nothing at this address.', 404);
}

require_once V::$root . '/lib/Ledger.php';

$report = isset($_GET['report']) ? $_GET['report'] : 'payouts';

header('Content-Type: text/csv; charset=utf-8');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');

if ($report === 'orders') {
    header('Content-Disposition: inline; filename="veyl-orders.csv"');
    Ledger::ordersCsv($out);
    exit;
}

$month = isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month'])
    ? $_GET['month']
    : gmdate('Y-m');

header('Content-Disposition: inline; filename="veyl-payouts-' . $month . '.csv"');
Ledger::monthlyPayoutCsv($out, $month);

if (!empty($_GET['mark_paid'])) {
    $through = gmdate('Y-m-t', strtotime($month . '-01'));
    $n = Ledger::markPaid($through);
    fputcsv($out, []);
    fputcsv($out, ['Marked as paid', $n . ' sales', 'through ' . $through]);
}
