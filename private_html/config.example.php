<?php
/**
 * Veyl configuration — EXAMPLE FILE.
 *
 * Copy this file to config.php in the same folder, then fill in the values.
 * This folder sits OUTSIDE the public web folder, so nothing here is reachable by URL.
 *
 * Never paste any of these values into a page, a script, an email or a support ticket.
 * Prices, discounts and commission are NOT here — they live in data/products.json.
 */

return [

    // ---------------------------------------------------------------
    // PayPal — https://developer.paypal.com > Apps & Credentials
    // ---------------------------------------------------------------
    // Leave this on 'sandbox' until you have tested a full purchase.
    // Switch to 'live' only when you are ready to take real money.
    'paypal_env'        => 'sandbox',
    'paypal_client_id'  => 'PASTE_SANDBOX_CLIENT_ID',
    'paypal_secret'     => 'PASTE_SANDBOX_SECRET',
    'paypal_webhook_id' => 'PASTE_WEBHOOK_ID',

    // Optional: when you go live, paste the live credentials here as well.
    // The site uses whichever pair matches 'paypal_env' above.
    'paypal_live_client_id' => '',
    'paypal_live_secret'    => '',
    'paypal_live_webhook_id' => '',

    // ---------------------------------------------------------------
    // Secrets — make each one a long random string, different from the others.
    // Generate one with:  php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
    // ---------------------------------------------------------------
    'hmac_secret' => 'CHANGE_ME_LONG_RANDOM_STRING',   // signs download and order links
    'admin_key'   => 'CHANGE_ME_A_DIFFERENT_RANDOM',   // opens the payout report at /api/admin.php

    // ---------------------------------------------------------------
    // Email
    // ---------------------------------------------------------------
    // 'resend' — recommended. Create a free key at https://resend.com and verify your domain.
    // 'smtp'   — your own SMTP server.
    // 'mail'   — the host's built-in mail() function. Works, but often lands in spam.
    // 'log'    — writes to data/mail.log instead of sending. Use this while testing.
    'mail_driver'    => 'log',
    'mail_from'      => 'Veyl <orders@veyl.kiseo.io>',
    'resend_api_key' => '',

    // Only needed when mail_driver is 'smtp'.
    'smtp_host'   => '',
    'smtp_port'   => 587,
    'smtp_user'   => '',
    'smtp_pass'   => '',
    'smtp_secure' => 'tls',   // 'tls' for port 587, 'ssl' for port 465

    // ---------------------------------------------------------------
    // Download links
    // ---------------------------------------------------------------
    'link_ttl_days'      => 30,   // how many days a download link keeps working
    'max_downloads'      => 10,   // how many times one order may download each file
    'commission_hold_days' => 30, // a sale appears in the payout report this many days after it is paid,
                                  // so that chargebacks and disputes have time to surface

    // ---------------------------------------------------------------
    // Site
    // ---------------------------------------------------------------
    // Leave 'site_url' empty to use the address in data/products.json.
    // Set it only if you are testing on a different domain.
    'site_url'         => '',
    'force_https'      => true,
    'allowed_origins'  => [],   // extra addresses allowed to call the API. For local testing only.
];
