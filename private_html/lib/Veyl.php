<?php
/**
 * Veyl server library. Plain PHP 7.4+, no frameworks.
 * This file lives OUTSIDE the public web folder and is never reachable by URL.
 */

class V
{
    /** @var string Absolute path to the private_html folder. */
    public static $root;

    /** @var string Absolute path to the public web folder. */
    public static $public;

    private static $cfg;
    private static $data;
    private static $db;
    private static $nonce;

    public static function init($root, $public = null)
    {
        self::$root = rtrim($root, '/');
        self::$public = $public === null ? dirname(self::$root) . '/public_html' : rtrim($public, '/');
    }

    // ---------------------------------------------------------------- config

    public static function cfg($key = null, $default = null)
    {
        if (self::$cfg === null) {
            $file = getenv('VEYL_CONFIG') ?: self::$root . '/config.php';
            if (!is_file($file)) {
                self::fail(500, 'The site is not set up yet. config.php is missing.');
            }
            self::$cfg = require $file;
        }
        if ($key === null) {
            return self::$cfg;
        }
        return array_key_exists($key, self::$cfg) && self::$cfg[$key] !== '' ? self::$cfg[$key] : $default;
    }

    // ------------------------------------------------------------------ data

    /** The whole products.json file: site, pricing, sectionBlurbs, products. */
    public static function data()
    {
        if (self::$data === null) {
            $raw = file_get_contents(self::$root . '/data/products.json');
            self::$data = json_decode($raw, true);
            if (!is_array(self::$data)) {
                self::fail(500, 'The product data file could not be read.');
            }
        }
        return self::$data;
    }

    public static function site($key = null)
    {
        $s = self::data()['site'];
        return $key === null ? $s : (isset($s[$key]) ? $s[$key] : '');
    }

    public static function pricing($key = null)
    {
        $p = self::data()['pricing'];
        return $key === null ? $p : (isset($p[$key]) ? $p[$key] : null);
    }

    public static function products()
    {
        return self::data()['products'];
    }

    public static function product($slug)
    {
        foreach (self::products() as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
        return null;
    }

    /** A product may only be bought when it is marked live AND its PDF is actually on disk. */
    public static function isBuyable($product)
    {
        return $product['status'] === 'live' && self::productFile($product['slug']) !== null;
    }

    public static function productFile($slug)
    {
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $path = self::$root . '/kits/' . $slug . '.pdf';
        return is_file($path) ? $path : null;
    }

    public static function siteUrl()
    {
        return rtrim(self::cfg('site_url', self::site('url')), '/');
    }

    public static function supportEmail()
    {
        return self::site('supportEmail');
    }

    public static function dataDir()
    {
        $dir = self::$root . '/data';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        return $dir;
    }

    // ------------------------------------------------------------- responses

    public static function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data);
        exit;
    }

    public static function wantsJson()
    {
        $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
        $type   = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        return strpos($accept, 'application/json') !== false || strpos($type, 'application/json') !== false;
    }

    /** A small standalone page, used by endpoints that have no template around them. */
    public static function htmlPage($title, $message, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $m = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(self::supportEmail(), ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
           . '<meta name="viewport" content="width=device-width,initial-scale=1">'
           . '<title>' . $t . ' — Veyl</title><meta name="robots" content="noindex">'
           . '<link rel="stylesheet" href="/assets/css/veyl.css"></head>'
           . '<body><main class="shell shell--narrow"><div class="panel">'
           . '<h1 class="display">' . $t . '</h1><p class="lede">' . $m . '</p>'
           . '<p><a class="btn btn--dark" href="/">Back to Veyl</a></p>'
           . '<p class="fine">Need help? Email <a href="mailto:' . $email . '">' . $email . '</a>.</p>'
           . '</div></main></body></html>';
        exit;
    }

    public static function fail($status, $message)
    {
        if (self::wantsJson() || php_sapi_name() === 'cli') {
            self::json(['ok' => false, 'error' => $message], $status);
        }
        self::htmlPage('Sorry', $message, $status);
    }

    public static function redirect($url, $code = 303)
    {
        header('Location: ' . $url, true, $code);
        exit;
    }

    // --------------------------------------------------------------- security

    /** One random value per request, used to allow our own inline script in the CSP. */
    public static function nonce()
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }

    public static function securityHeaders()
    {
        $paypal = 'https://www.paypal.com https://www.sandbox.paypal.com https://www.paypalobjects.com';
        $csp = "default-src 'self'; "
             . "base-uri 'self'; "
             . "object-src 'none'; "
             . "frame-ancestors 'self'; "
             . "form-action 'self' " . $paypal . '; '
             . "script-src 'self' 'nonce-" . self::nonce() . "' " . $paypal . '; '
             . "style-src 'self' 'unsafe-inline'; "
             . "img-src 'self' data: " . $paypal . '; '
             . "font-src 'self'; "
             . "connect-src 'self' https://*.paypal.com; "
             . "frame-src " . $paypal . '; '
             . 'upgrade-insecure-requests';
        header('Content-Security-Policy: ' . $csp);
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-Frame-Options: SAMEORIGIN');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()');
        if (self::cfg('force_https', true) && self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    public static function isHttps()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /** Reject cross-site form posts and fetches. */
    public static function sameOrigin()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if ($origin === '') {
            return;
        }
        $allowed = array_merge([self::siteUrl()], (array) self::cfg('allowed_origins', []));
        foreach ($allowed as $a) {
            if (rtrim($a, '/') === rtrim($origin, '/')) {
                return;
            }
        }
        self::fail(403, 'That request did not come from this website.');
    }

    /**
     * CSRF protection, double-submit style. The page carries a token and the browser
     * carries the same token in a cookie. A cross-site page can send one but not the other.
     */
    public static function csrfToken()
    {
        $existing = isset($_COOKIE['veyl_csrf']) ? (string) $_COOKIE['veyl_csrf'] : '';
        if (preg_match('/^[a-f0-9]{48}$/', $existing)) {
            return $existing;
        }
        $token = bin2hex(random_bytes(24));
        if (!headers_sent()) {
            setcookie('veyl_csrf', $token, [
                'expires'  => 0,
                'path'     => '/',
                'secure'   => self::isHttps(),
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE['veyl_csrf'] = $token;
        return $token;
    }

    public static function checkCsrf($input)
    {
        $sent   = isset($input['_csrf']) ? (string) $input['_csrf'] : '';
        $cookie = isset($_COOKIE['veyl_csrf']) ? (string) $_COOKIE['veyl_csrf'] : '';
        if ($cookie === '' || $sent === '' || !hash_equals($cookie, $sent)) {
            self::fail(403, 'Your session expired. Please reload the page and try again.');
        }
    }

    public static function input()
    {
        $type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (strpos($type, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if (strlen($raw) > 20000) {
                self::fail(413, 'That request was too large.');
            }
            $parsed = json_decode($raw, true);
            return is_array($parsed) ? $parsed : [];
        }
        return $_POST;
    }

    public static function str($value, $max = 200)
    {
        $value = is_string($value) ? trim($value) : '';
        return mb_substr($value, 0, $max);
    }

    public static function validEmail($email)
    {
        return strlen($email) <= 200
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && !preg_match('/[\r\n]/', $email);
    }

    public static function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    // --------------------------------------------------------------- database

    public static function db()
    {
        if (self::$db) {
            return self::$db;
        }
        $pdo = new PDO('sqlite:' . self::dataDir() . '/veyl.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA busy_timeout=5000');
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS orders (
                id                 INTEGER PRIMARY KEY AUTOINCREMENT,
                public_id          TEXT UNIQUE,
                paypal_order_id    TEXT UNIQUE,
                capture_id         TEXT,
                status             TEXT,
                product_slug       TEXT,
                product_name       TEXT,
                list_cents         INTEGER DEFAULT 0,
                discount_cents     INTEGER DEFAULT 0,
                amount_cents       INTEGER DEFAULT 0,
                currency           TEXT,
                code               TEXT DEFAULT "",
                influencer         TEXT DEFAULT "",
                commission_cents   INTEGER DEFAULT 0,
                commission_status  TEXT DEFAULT "none",
                commission_paid_at TEXT,
                buyer_email        TEXT DEFAULT "",
                buyer_name         TEXT DEFAULT "",
                consent            INTEGER DEFAULT 0,
                consent_text       TEXT DEFAULT "",
                fee_cents          INTEGER DEFAULT 0,
                download_count     INTEGER DEFAULT 0,
                created_at         TEXT,
                paid_at            TEXT,
                refunded_at        TEXT,
                note               TEXT DEFAULT ""
            )'
        );
        $pdo->exec('CREATE TABLE IF NOT EXISTS webhook_events (id TEXT PRIMARY KEY, type TEXT, received_at TEXT)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS hits (id INTEGER PRIMARY KEY AUTOINCREMENT, kind TEXT, who TEXT, at INTEGER)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_code ON orders (code)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_email ON orders (buyer_email)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_hits ON hits (kind, who, at)');
        self::$db = $pdo;
        return $pdo;
    }

    public static function now()
    {
        return gmdate('Y-m-d H:i:s');
    }

    public static function rateLimit($kind, $max, $windowSeconds)
    {
        $ip  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli';
        $who = hash('sha256', $ip . self::cfg('hmac_secret', ''));
        $db  = self::db();
        $db->prepare('DELETE FROM hits WHERE at < ?')->execute([time() - 86400]);
        $stmt = $db->prepare('SELECT COUNT(*) AS c FROM hits WHERE kind = ? AND who = ? AND at > ?');
        $stmt->execute([$kind, $who, time() - $windowSeconds]);
        if ((int) $stmt->fetch()['c'] >= $max) {
            self::fail(429, 'That is a lot of tries in a short time. Please wait a few minutes and try again.');
        }
        $db->prepare('INSERT INTO hits (kind, who, at) VALUES (?, ?, ?)')->execute([$kind, $who, time()]);
    }

    // ----------------------------------------------------------- signed links

    public static function b64($raw)
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    public static function unb64($text)
    {
        return base64_decode(strtr($text, '-_', '+/'));
    }

    public static function sign($payload, $ttlSeconds)
    {
        $payload['e'] = time() + $ttlSeconds;
        $body = self::b64(json_encode($payload));
        return $body . '.' . self::b64(hash_hmac('sha256', $body, self::cfg('hmac_secret'), true));
    }

    /** Returns the payload, or null if the link is forged, expired or of the wrong kind. */
    public static function verify($token, $kind)
    {
        $parts = explode('.', (string) $token);
        if (count($parts) !== 2) {
            return null;
        }
        $expected = self::b64(hash_hmac('sha256', $parts[0], self::cfg('hmac_secret'), true));
        if (!hash_equals($expected, $parts[1])) {
            return null;
        }
        $payload = json_decode(self::unb64($parts[0]), true);
        if (!is_array($payload)) {
            return null;
        }
        if ((isset($payload['e']) ? $payload['e'] : 0) < time()) {
            return null;
        }
        if ((isset($payload['t']) ? $payload['t'] : '') !== $kind) {
            return null;
        }
        return $payload;
    }

    public static function linkTtl()
    {
        return 86400 * (int) self::cfg('link_ttl_days', 30);
    }

    // ------------------------------------------------- prices and discount codes

    public static function usd($cents)
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public static function money($cents)
    {
        return '$' . number_format($cents / 100, 2, '.', ',');
    }

    public static function basePriceCents()
    {
        return (int) self::pricing('basePriceCents');
    }

    public static function discountCents()
    {
        return (int) floor(self::basePriceCents() * (int) self::pricing('discountPercent') / 100);
    }

    public static function discountedPriceCents()
    {
        return self::basePriceCents() - self::discountCents();
    }

    /** Strict whitelist. Codes are letters, digits and dashes, and are not case sensitive. */
    public static function cleanCode($code)
    {
        $code = strtoupper(self::str($code, 32));
        return preg_match('/^[A-Z0-9][A-Z0-9-]{1,31}$/', $code) ? $code : '';
    }

    public static function codes()
    {
        $file = self::$root . '/data/codes.json';
        if (!is_file($file)) {
            return [];
        }
        $parsed = json_decode(file_get_contents($file), true);
        $out = [];
        foreach ((isset($parsed['codes']) && is_array($parsed['codes']) ? $parsed['codes'] : []) as $row) {
            if (!isset($row['code'])) {
                continue;
            }
            $key = strtoupper(trim($row['code']));
            $out[$key] = $row;
        }
        return $out;
    }

    /**
     * Work out what a buyer actually pays. The browser never sends a price;
     * this is the only place a total is decided.
     *
     * @return array{ok:bool, code:string, influencer:string, list:int, discount:int,
     *                total:int, commission:int, label:string, error?:string}
     */
    public static function price($rawCode, $slug)
    {
        $list = self::basePriceCents();
        $none = [
            'ok'         => true,
            'code'       => '',
            'influencer' => '',
            'list'       => $list,
            'discount'   => 0,
            'total'      => $list,
            'commission' => 0,
            'label'      => '',
        ];

        $raw = self::str($rawCode, 32);
        if ($raw === '') {
            return $none;
        }

        $code = self::cleanCode($raw);
        $invalid = [
            'ok'    => false,
            'error' => 'That code was not recognized. Check the spelling and try again.',
        ] + $none;

        if ($code === '') {
            return $invalid;
        }

        $codes = self::codes();
        if (!isset($codes[$code])) {
            return $invalid;
        }

        $row = $codes[$code];
        if (empty($row['active'])) {
            return ['ok' => false, 'error' => 'That code is no longer active.'] + $none;
        }

        $scope = isset($row['products']) ? $row['products'] : 'all';
        if (is_array($scope) && !in_array($slug, $scope, true)) {
            return ['ok' => false, 'error' => 'That code does not apply to this guide.'] + $none;
        }

        $discount = self::discountCents();
        $percent  = (int) self::pricing('discountPercent');

        return [
            'ok'         => true,
            'code'       => $code,
            'influencer' => isset($row['influencer']) ? (string) $row['influencer'] : '',
            'list'       => $list,
            'discount'   => $discount,
            'total'      => $list - $discount,
            'commission' => (int) self::pricing('commissionCents'),
            'label'      => $percent . '% off',
        ];
    }

    // ------------------------------------------------------------------- email

    public static function mail($to, $subject, $text, $replyTo = null)
    {
        $driver = self::cfg('mail_driver', 'log');
        $from   = self::cfg('mail_from', 'Veyl <' . self::supportEmail() . '>');

        if ($driver === 'log') {
            $entry = "---- " . self::now() . "\nTo: $to\nSubject: $subject\n"
                   . ($replyTo ? "Reply-To: $replyTo\n" : '') . "\n$text\n\n";
            file_put_contents(self::dataDir() . '/mail.log', $entry, FILE_APPEND);
            return true;
        }

        if ($driver === 'resend') {
            $body = ['from' => $from, 'to' => [$to], 'subject' => $subject, 'text' => $text];
            if ($replyTo) {
                $body['reply_to'] = $replyTo;
            }
            $res = self::http(
                'POST',
                'https://api.resend.com/emails',
                ['Authorization: Bearer ' . self::cfg('resend_api_key'), 'Content-Type: application/json'],
                json_encode($body)
            );
            return $res['status'] >= 200 && $res['status'] < 300;
        }

        if ($driver === 'smtp') {
            return Smtp::send($to, $subject, $text, $from, $replyTo);
        }

        $headers = "From: $from\r\n"
                 . ($replyTo ? "Reply-To: $replyTo\r\n" : '')
                 . "Content-Type: text/plain; charset=utf-8\r\nMIME-Version: 1.0";
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $text, $headers);
    }

    public static function http($method, $url, $headers = [], $body = null, $userpwd = null)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        if ($userpwd) {
            curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
        }
        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error  = curl_error($ch);
        curl_close($ch);
        return ['status' => $status, 'raw' => $raw, 'json' => json_decode((string) $raw, true), 'error' => $error];
    }
}

/** A very small SMTP sender, used only when mail_driver is 'smtp'. */
class Smtp
{
    public static function send($to, $subject, $text, $from, $replyTo = null)
    {
        $host   = V::cfg('smtp_host');
        $port   = (int) V::cfg('smtp_port', 587);
        $secure = V::cfg('smtp_secure', 'tls');
        if (!$host) {
            return false;
        }

        $target = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = @stream_socket_client($target, $errno, $errstr, 15);
        if (!$socket) {
            error_log('Veyl SMTP connect failed: ' . $errstr);
            return false;
        }

        $read = function () use ($socket) {
            $out = '';
            while (($line = fgets($socket, 515)) !== false) {
                $out .= $line;
                if (strlen($line) < 4 || $line[3] === ' ') {
                    break;
                }
            }
            return $out;
        };
        $say = function ($line) use ($socket, $read) {
            fwrite($socket, $line . "\r\n");
            return $read();
        };

        $read();
        $say('EHLO veyl');
        if ($secure === 'tls') {
            $say('STARTTLS');
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }
            $say('EHLO veyl');
        }
        if (V::cfg('smtp_user')) {
            $say('AUTH LOGIN');
            $say(base64_encode(V::cfg('smtp_user')));
            $say(base64_encode(V::cfg('smtp_pass')));
        }

        $envelopeFrom = preg_match('/<([^>]+)>/', $from, $m) ? $m[1] : $from;
        $say('MAIL FROM:<' . $envelopeFrom . '>');
        $say('RCPT TO:<' . $to . '>');
        $say('DATA');

        $headers = "From: $from\r\nTo: $to\r\n"
                 . ($replyTo ? "Reply-To: $replyTo\r\n" : '')
                 . 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n"
                 . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\n";
        $body = preg_replace('/^\./m', '..', $text);
        $final = $say($headers . "\r\n" . $body . "\r\n.");
        $say('QUIT');
        fclose($socket);

        return strpos($final, '250') === 0;
    }
}

/** PayPal Orders API v2. */
class PayPal
{
    public static function isLive()
    {
        return V::cfg('paypal_env', 'sandbox') === 'live';
    }

    public static function base()
    {
        // 'paypal_base' is for local testing against a stand-in server only.
        // Leave it out of config.php and the real PayPal endpoints are used.
        $override = V::cfg('paypal_base');
        if ($override) {
            return rtrim($override, '/');
        }
        return self::isLive() ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    public static function clientId()
    {
        return self::isLive()
            ? V::cfg('paypal_live_client_id', V::cfg('paypal_client_id'))
            : V::cfg('paypal_client_id');
    }

    public static function secret()
    {
        return self::isLive()
            ? V::cfg('paypal_live_secret', V::cfg('paypal_secret'))
            : V::cfg('paypal_secret');
    }

    public static function webhookId()
    {
        return self::isLive()
            ? V::cfg('paypal_live_webhook_id', V::cfg('paypal_webhook_id'))
            : V::cfg('paypal_webhook_id');
    }

    public static function token()
    {
        $res = V::http(
            'POST',
            self::base() . '/v1/oauth2/token',
            ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
            'grant_type=client_credentials',
            self::clientId() . ':' . self::secret()
        );
        if (empty($res['json']['access_token'])) {
            throw new RuntimeException('PayPal sign-in failed (HTTP ' . $res['status'] . ')');
        }
        return $res['json']['access_token'];
    }

    public static function call($method, $path, $body = null, $extraHeaders = [])
    {
        $headers = array_merge([
            'Authorization: Bearer ' . self::token(),
            'Content-Type: application/json',
            'Accept: application/json',
        ], $extraHeaders);
        return V::http($method, self::base() . $path, $headers, $body === null ? null : json_encode($body));
    }
}

/** Reading and writing order records. */
class Orders
{
    public static function byPaypalId($id)
    {
        $stmt = V::db()->prepare('SELECT * FROM orders WHERE paypal_order_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function byPublicId($id)
    {
        $stmt = V::db()->prepare('SELECT * FROM orders WHERE public_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function byCaptureId($id)
    {
        $stmt = V::db()->prepare('SELECT * FROM orders WHERE capture_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function newPublicId()
    {
        return 'VL-' . strtoupper(bin2hex(random_bytes(4)));
    }

    /** A fresh, signed, expiring download link for one paid order. */
    public static function downloadUrl($order)
    {
        $token = V::sign(['t' => 'dl', 'o' => $order['public_id']], V::linkTtl());
        return V::siteUrl() . '/api/download.php?t=' . $token;
    }

    public static function deliveryEmail($order)
    {
        $days    = (int) V::cfg('link_ttl_days', 30);
        $maximum = (int) V::cfg('max_downloads', 10);
        $url     = self::downloadUrl($order);
        $support = V::supportEmail();

        $text = "Thank you for your order.\n\n"
              . "Order: " . $order['public_id'] . "\n"
              . "Guide: " . $order['product_name'] . "\n"
              . "Total: " . V::money($order['amount_cents']) . ' ' . $order['currency'] . "\n\n"
              . "Download your guide:\n" . $url . "\n\n"
              . "This link works for $days days and up to $maximum downloads. Please save the file to your device.\n"
              . "If the link stops working, you can ask for a new one at " . V::siteUrl() . "/thank-you/#resend\n\n"
              . "All sales are final. This is a digital product delivered immediately, so there are no refunds.\n"
              . "Veyl guides are educational only. They are not legal, financial or medical advice.\n\n"
              . "Questions: $support\n";

        return V::mail($order['buyer_email'], 'Your Veyl guide (' . $order['public_id'] . ')', $text, $support);
    }

    /** A valid code on a paid order owes its influencer the flat commission. */
    public static function applyCommission($orderId)
    {
        $order = self::byId($orderId);
        if (!$order || $order['code'] === '') {
            return;
        }
        $codes = V::codes();
        if (!isset($codes[$order['code']])) {
            return;
        }
        V::db()
            ->prepare('UPDATE orders SET commission_cents = ?, commission_status = "unpaid" WHERE id = ?')
            ->execute([(int) V::pricing('commissionCents'), $orderId]);
    }

    public static function byId($id)
    {
        $stmt = V::db()->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    /** A refund, reversal or dispute closes the download and voids any unpaid commission. */
    public static function revoke($order, $status, $note)
    {
        $commission = $order['commission_status'];
        $clawback   = '';
        if ($commission === 'paid') {
            $clawback = ' CLAWBACK: the influencer was already paid for this sale.';
        } elseif ($commission === 'unpaid') {
            $commission = 'void';
        }
        $combined = trim(($order['note'] ? $order['note'] . ' | ' : '') . $note . $clawback);
        V::db()
            ->prepare('UPDATE orders SET status = ?, refunded_at = ?, commission_status = ?, note = ? WHERE id = ?')
            ->execute([$status, V::now(), $commission, $combined, $order['id']]);
    }
}
