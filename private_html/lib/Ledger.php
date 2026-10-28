<?php
/**
 * Payout and order reports. Reads the same database the checkout writes to.
 * A sale becomes payable once it has been paid for longer than commission_hold_days,
 * is still marked paid (not refunded or disputed), and has not been paid out yet.
 */
class Ledger
{
    private static function holdCutoff($throughDate)
    {
        $hold = 86400 * (int) V::cfg('commission_hold_days', 30);
        return gmdate('Y-m-d H:i:s', strtotime($throughDate . ' 23:59:59 UTC') - $hold);
    }

    /** Payable sales up to a date, oldest first, grouped later by code. */
    public static function payable($throughDate)
    {
        $stmt = V::db()->prepare(
            'SELECT * FROM orders
              WHERE status = "paid"
                AND code <> ""
                AND commission_status = "unpaid"
                AND paid_at <= ?
           ORDER BY code, paid_at'
        );
        $stmt->execute([self::holdCutoff($throughDate)]);
        return $stmt->fetchAll();
    }

    /** Every paid sale on a code within one calendar month, whether or not it is payable yet. */
    public static function monthRows($month)
    {
        $start = $month . '-01 00:00:00';
        $end   = gmdate('Y-m-t 23:59:59', strtotime($start));
        $stmt  = V::db()->prepare(
            'SELECT * FROM orders
              WHERE status = "paid" AND code <> "" AND paid_at BETWEEN ? AND ?
           ORDER BY code, paid_at'
        );
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll();
    }

    /**
     * The monthly payout report: one block per influencer code.
     * $month is YYYY-MM. Writes CSV to the given handle.
     */
    public static function monthlyPayoutCsv($out, $month)
    {
        $rows  = self::monthRows($month);
        $codes = V::codes();
        $hold  = (int) V::cfg('commission_hold_days', 30);

        fputcsv($out, ['VEYL MONTHLY PAYOUT REPORT']);
        fputcsv($out, ['Month', $month]);
        fputcsv($out, ['Generated (UTC)', V::now()]);
        fputcsv($out, ['Commission per sale', V::money((int) V::pricing('commissionCents'))]);
        fputcsv($out, ['Hold before a sale is payable', $hold . ' days']);
        fputcsv($out, []);

        $summary = [];
        foreach ($rows as $order) {
            $code = $order['code'];
            if (!isset($summary[$code])) {
                $summary[$code] = ['sales' => 0, 'revenue' => 0, 'commission' => 0, 'payable' => 0, 'held' => 0];
            }
            $summary[$code]['sales']++;
            $summary[$code]['revenue']    += (int) $order['amount_cents'];
            $summary[$code]['commission'] += (int) $order['commission_cents'];
            if ($order['commission_status'] === 'paid') {
                $summary[$code]['payable'] += 0;
            } elseif (strtotime($order['paid_at']) <= strtotime(self::holdCutoff(gmdate('Y-m-d')))) {
                $summary[$code]['payable'] += (int) $order['commission_cents'];
            } else {
                $summary[$code]['held'] += (int) $order['commission_cents'];
            }
        }

        fputcsv($out, ['SUMMARY BY CODE']);
        fputcsv($out, ['code', 'influencer', 'sales', 'revenue_usd', 'commission_usd', 'payable_now_usd', 'still_on_hold_usd']);

        $totals = ['sales' => 0, 'revenue' => 0, 'commission' => 0, 'payable' => 0, 'held' => 0];
        foreach ($summary as $code => $s) {
            fputcsv($out, [
                $code,
                isset($codes[$code]['influencer']) ? $codes[$code]['influencer'] : '(code not in codes.json)',
                $s['sales'],
                V::usd($s['revenue']),
                V::usd($s['commission']),
                V::usd($s['payable']),
                V::usd($s['held']),
            ]);
            foreach ($totals as $key => $_) {
                $totals[$key] += $s[$key];
            }
        }
        fputcsv($out, [
            'TOTAL', '', $totals['sales'],
            V::usd($totals['revenue']), V::usd($totals['commission']),
            V::usd($totals['payable']), V::usd($totals['held']),
        ]);

        fputcsv($out, []);
        fputcsv($out, ['EVERY SALE IN THIS MONTH']);
        fputcsv($out, ['order', 'paid_at_utc', 'code', 'influencer', 'guide', 'paid_usd', 'commission_usd', 'commission_status', 'paypal_transaction_id']);
        foreach ($rows as $order) {
            fputcsv($out, [
                $order['public_id'],
                $order['paid_at'],
                $order['code'],
                $order['influencer'],
                $order['product_slug'],
                V::usd($order['amount_cents']),
                V::usd($order['commission_cents']),
                $order['commission_status'],
                $order['capture_id'],
            ]);
        }

        $clawbacks = V::db()->query('SELECT * FROM orders WHERE note LIKE "%CLAWBACK%"')->fetchAll();
        if ($clawbacks) {
            fputcsv($out, []);
            fputcsv($out, ['CLAWBACK NEEDED — refunded or disputed after the influencer was paid']);
            fputcsv($out, ['order', 'code', 'commission_usd', 'note']);
            foreach ($clawbacks as $order) {
                fputcsv($out, [$order['public_id'], $order['code'], V::usd($order['commission_cents']), $order['note']]);
            }
        }
    }

    /** Mark every payable sale up to a date as paid out. */
    public static function markPaid($throughDate)
    {
        $rows = self::payable($throughDate);
        $stmt = V::db()->prepare('UPDATE orders SET commission_status = "paid", commission_paid_at = ? WHERE id = ?');
        foreach ($rows as $order) {
            $stmt->execute([V::now(), $order['id']]);
        }
        return count($rows);
    }

    /** Every order, for your own bookkeeping. */
    public static function ordersCsv($out)
    {
        $feePct   = (float) V::pricing('paypalFeePercentForReporting');
        $feeFixed = (int) V::pricing('paypalFeeFixedCentsForReporting');

        fputcsv($out, [
            'order', 'status', 'guide', 'list_usd', 'discount_usd', 'paid_usd',
            'paypal_fee_usd', 'commission_usd', 'net_usd',
            'code', 'influencer', 'commission_status', 'buyer_email', 'consent',
            'created_at_utc', 'paid_at_utc', 'refunded_at_utc', 'downloads', 'paypal_transaction_id', 'note',
        ]);

        foreach (V::db()->query('SELECT * FROM orders ORDER BY id')->fetchAll() as $order) {
            $paid = (int) $order['amount_cents'];
            $fee  = (int) $order['fee_cents'];
            if ($fee === 0 && $order['status'] === 'paid') {
                // PayPal did not report a fee. Fall back to the configured estimate.
                $fee = (int) round($paid * $feePct / 100) + $feeFixed;
            }
            $net = $order['status'] === 'paid' ? $paid - $fee - (int) $order['commission_cents'] : 0;

            fputcsv($out, [
                $order['public_id'],
                $order['status'],
                $order['product_slug'],
                V::usd($order['list_cents']),
                V::usd($order['discount_cents']),
                V::usd($paid),
                V::usd($fee),
                V::usd($order['commission_cents']),
                V::usd($net),
                $order['code'],
                $order['influencer'],
                $order['commission_status'],
                $order['buyer_email'],
                $order['consent'] ? 'yes' : 'no',
                $order['created_at'],
                $order['paid_at'],
                $order['refunded_at'],
                $order['download_count'],
                $order['capture_id'],
                $order['note'],
            ]);
        }
    }
}
