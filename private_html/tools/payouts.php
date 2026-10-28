<?php
/**
 * Monthly influencer payout report, on the command line.
 *
 *   php private_html/tools/payouts.php                 the current month
 *   php private_html/tools/payouts.php 2026-11         a specific month
 *   php private_html/tools/payouts.php 2026-11 --mark-paid
 *   php private_html/tools/payouts.php --orders        every order, for bookkeeping
 *
 * --mark-paid records that you have sent the money. Only sales that are past the
 * hold period, still paid, and not already paid out are marked.
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("This script runs on the command line only.\n");
}

require dirname(__DIR__) . '/lib/Veyl.php';
require dirname(__DIR__) . '/lib/Ledger.php';

V::init(dirname(__DIR__));

$args = array_slice($argv, 1);
$out  = fopen('php://output', 'w');

if (in_array('--orders', $args, true)) {
    Ledger::ordersCsv($out);
    exit(0);
}

$month = gmdate('Y-m');
foreach ($args as $arg) {
    if (preg_match('/^\d{4}-\d{2}$/', $arg)) {
        $month = $arg;
    }
}

Ledger::monthlyPayoutCsv($out, $month);

if (in_array('--mark-paid', $args, true)) {
    $through = gmdate('Y-m-t', strtotime($month . '-01'));
    $marked  = Ledger::markPaid($through);
    fwrite(STDERR, "\nMarked as paid: $marked sales, through $through\n");
}
