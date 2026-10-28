# Veyl — how the site works

Everything on the site is generated from one data file. There is no build step and
no framework: plain PHP, one stylesheet, one small script.

---

## 1. Folder layout

Two folders sit **side by side** on your hosting account. `public_html` is the web
root. `private_html` sits **outside** it and is never reachable by a web address.

```
public_html/                  ← the web root (this is what the internet sees)
  .htaccess                   rewrites, HTTPS, security headers, no directory listing
  index.php                   Home
  about/index.php             About
  products/index.php          Products (all ten cards)
  products/view.php           one product page, reached as /products/<slug>/
  faq/index.php               FAQ
  terms/ disclaimer/ privacy/ footer-only legal pages
  thank-you/index.php         confirmation and download page
  404.php                     not-found page
  sitemap.php                 served as /sitemap.xml
  llms.php                    served as /llms.txt
  robots.txt
  api/
    _boot.php                 finds private_html and loads the library
    code.php                  checks a discount code, returns the price
    create-order.php          starts a PayPal order (the server sets the amount)
    capture-order.php         takes the payment, records the order, emails the link
    download.php              streams the PDF after checking a signed link
    order-links.php           feeds the confirmation page
    resend.php                sends a download link again
    paypal-webhook.php        PayPal's backup notifications (refunds, disputes)
    admin.php                 the payout report in a browser
  assets/                     css, js, fonts, icons, social images

private_html/                 ← NEVER inside public_html
  .htaccess                   denies everything, in case it is ever misplaced
  config.php                  YOUR SECRETS. You create this from config.example.php.
  config.example.php          the template, with every value explained
  README.md                   this file
  data/
    products.json             THE ONE DATA FILE: all copy, prices, figures, status
    codes.json                influencer discount codes
    veyl.sqlite               orders (created automatically on the first sale)
    mail.log                  only used while mail_driver is 'log'
  kits/                       YOUR PRODUCT PDFs go here, named <slug>.pdf
  lib/                        Veyl.php, View.php, Ledger.php
  templates/                  the page templates
  tools/
    payouts.php               the payout report on the command line
    router.php                for testing on your own computer only
```

**Why the split matters.** Secrets, order records, discount codes and the product
PDFs all live in `private_html`. Nothing there has a web address. The PDFs are sent
to buyers by `api/download.php`, and only after it has checked a signed, unexpired
link against a paid order.

---

## 2. Uploading

1. In your hosting file manager, go to the folder that **contains** `public_html`.
   On Hostinger that is `/home/uXXXXXXXX/domains/yourdomain/`.
2. Upload the two folders so they sit next to each other:
   `public_html/` and `private_html/`.
   The code also accepts `private/` as the folder name, if that suits your host better.
3. Copy `private_html/config.example.php` to `private_html/config.php` and fill it in
   (section 5 below).
4. Turn on **Force HTTPS** in your hosting control panel.
   Hostinger: hPanel → Security → SSL. If your host has no such switch, the lines at
   the top of `public_html/.htaccess` do it instead.
5. Open your site. Every page should load, and every guide should read **Coming soon**
   until you add its PDF.

**Requirements:** PHP 7.4 or newer with the `pdo_sqlite`, `curl` and `mbstring`
extensions. These are standard on shared hosting. `mod_rewrite` must be on, which it
is on Apache and LiteSpeed by default.

### Testing it on your own computer first

From the folder that holds both folders:

```bash
php -S localhost:8000 -t public_html private_html/tools/router.php
```

Then open `http://localhost:8000`. For local testing, set `'site_url' => 'http://localhost:8000'`
and `'force_https' => false` in `config.php`, and put those two values back before you upload.

---

## 3. Adding a product PDF

Two steps.

1. Put the finished PDF in `private_html/kits/`, named after the product's slug:

   ```
   private_html/kits/car-buying-out-the-door-guide.pdf
   ```

2. Open `private_html/data/products.json`, find that product, and change:

   ```json
   "status": "coming_soon"      →      "status": "live"
   ```

The buy button switches on by itself. No other file changes.

**The safety rule:** a guide is only sold when its status says `live` **and** its PDF
is actually in `private_html/kits/`. If the file is missing, the button stays off even
when the status says `live`, so nobody can pay for something you cannot deliver.

The ten slugs are:

```
remodel-contractor-bid-guide        car-buying-out-the-door-guide
buy-a-small-business-guide          franchise-fdd-guide
assisted-living-guide               wedding-budget-vendor-guide
solar-quote-financing-guide         homebuyer-agent-commission-guide
timeshare-presentation-guide        ivf-clinic-cost-guide
```

### Adding real preview images later

The four preview cards on each product page are drawn in HTML and CSS and are labelled
"Illustrative preview". To swap in pictures of the real pages, put the images in
`public_html/assets/img/previews/` and name them in that product's `previewImages`:

```json
"previewImages": {
  "Lessons":   "/assets/img/previews/car-lessons.png",
  "Script":    "/assets/img/previews/car-script.png",
  "Forms":     "/assets/img/previews/car-forms.png",
  "Checklist": "/assets/img/previews/car-checklist.png"
}
```

Any section you name uses the image. Any section you leave out keeps the drawn card.

---

## 4. Adding an influencer discount code

Open `private_html/data/codes.json` and add a line to the `codes` list:

```json
{
  "code": "JORDAN10",
  "influencer": "Jordan — @jordanbuys on TikTok",
  "active": true,
  "products": "all",
  "note": "Signed up 1 March"
}
```

- `code` — letters, numbers and dashes only. Not case sensitive: `JORDAN10` and
  `jordan10` are the same code.
- `influencer` — for your records. Never shown on the website.
- `active` — set to `false` to switch a code off without losing its sales history.
- `products` — `"all"`, or a list of slugs: `["car-buying-out-the-door-guide"]`.

Save the file and upload it. The code works immediately.

**What a code does.** Every valid code takes 10% off, so the buyer pays **$89.10**
instead of $99.00, and that influencer is owed a flat **$50.00**. You keep $39.10
before PayPal's fee. Several influencers can have their own code for the same guide;
reporting is per code, so each is paid for the sales their own code brought in.

Give each influencer a link with their code already filled in:

```
https://veyl.kiseo.io/products/car-buying-out-the-door-guide/?code=JORDAN10
```

The code appears in the box and is checked straight away. The buyer can remove it.

**To change the discount or the commission for everyone**, edit the `pricing` block at
the top of `products.json`. Amounts are in cents.

```json
"pricing": {
  "basePriceCents": 9900,       ← $99.00
  "discountPercent": 10,        ← 10% off
  "commissionCents": 5000       ← $50.00 per coded sale
}
```

The server works out every price from these values. The browser never sends a price,
so a changed page cannot change what is charged.

---

## 5. Switching PayPal from sandbox to live

The site ships in **sandbox** mode. Nothing takes real money until you change it.

### First, test in the sandbox

1. Go to <https://developer.paypal.com> → Apps & Credentials → **Sandbox**.
2. Create an app. Copy the Client ID and Secret into `config.php`:
   ```php
   'paypal_env'       => 'sandbox',
   'paypal_client_id' => '...',
   'paypal_secret'    => '...',
   ```
3. In the sandbox dashboard, create a webhook pointing at
   `https://yourdomain.com/api/paypal-webhook.php`, subscribed to:
   `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.REFUNDED`,
   `PAYMENT.CAPTURE.REVERSED`, `PAYMENT.CAPTURE.DENIED`, `CUSTOMER.DISPUTE.CREATED`.
   Copy its Webhook ID into `'paypal_webhook_id'`.
4. Buy a guide with a sandbox test account, end to end. Check that the confirmation
   page shows a download link, the email arrives, and the PDF downloads.

### Then go live

1. In the PayPal dashboard switch to **Live** and create a live app and a live webhook
   at the same address.
2. In `config.php`:
   ```php
   'paypal_env'             => 'live',
   'paypal_live_client_id'  => '...',
   'paypal_live_secret'     => '...',
   'paypal_live_webhook_id' => '...',
   ```
   Leave the sandbox values where they are. The site uses whichever pair matches
   `paypal_env`, so you can switch back by changing that one word.
3. Buy one guide yourself with a real card, then refund it from the PayPal dashboard.
   Check that the refund closes the download link and that the sale leaves the payout
   report. That one test proves the whole chain.

### The other settings in config.php

| Setting | What it does |
|---|---|
| `hmac_secret` | Signs download links. Generate with `php -r "echo bin2hex(random_bytes(32));"` |
| `admin_key` | Opens the payout report in a browser. Make it long and different from the above |
| `mail_driver` | `resend` (recommended), `smtp`, `mail`, or `log` for testing |
| `link_ttl_days` | How many days a download link works. Default 30 |
| `max_downloads` | How many times one order may download. Default 10 |
| `commission_hold_days` | How long before a sale becomes payable. Default 30 |

**Email.** Set `mail_driver` to `resend` and paste a key from <https://resend.com>,
having verified your domain there. `mail` uses your host's built-in function; it works
but often lands in spam. `log` writes to `private_html/data/mail.log` and sends nothing,
which is what you want while testing. **This site was tested with `log`.** Send yourself
a real order before launch to confirm whichever driver you choose actually delivers.

---

## 6. Reading the payout report

### On the command line (no web address, nothing to protect)

```bash
php private_html/tools/payouts.php 2026-11
```

```bash
php private_html/tools/payouts.php 2026-11 --mark-paid
```

```bash
php private_html/tools/payouts.php --orders
```

The first prints the month's report. The second prints it and records that you have
sent the money. The third prints every order, for your own bookkeeping.

### In a browser

```
https://veyl.kiseo.io/api/admin.php?key=YOUR_ADMIN_KEY&report=payouts&month=2026-11
https://veyl.kiseo.io/api/admin.php?key=YOUR_ADMIN_KEY&report=orders
```

A wrong key, or a key shorter than 24 characters, returns a plain "not found" page.
The page is marked `noindex` and is rate limited. **The command line is safer; use the
browser version only when you have to.**

### What the report shows

```
SUMMARY BY CODE
code,influencer,sales,revenue_usd,commission_usd,payable_now_usd,still_on_hold_usd
JORDAN10,"Jordan — @jordanbuys",4,356.40,200.00,150.00,50.00
```

- **sales** — paid orders on that code in the month.
- **revenue_usd** — what buyers actually paid ($89.10 each).
- **commission_usd** — $50.00 per sale.
- **payable_now_usd** — past the 30-day hold, so safe to send.
- **still_on_hold_usd** — too recent; a dispute could still arrive.

Below that, every sale is listed with its PayPal transaction ID. If a buyer disputes or
charges back after you have paid an influencer, the order is marked and appears under
**CLAWBACK NEEDED** so you can see what to recover.

---

## 7. How checkout is kept safe

- **The server sets the price.** Everything is worked out from `products.json` and
  `codes.json`. The browser only ever sends a product slug and a code.
- **The amount is checked after payment.** If PayPal reports a different amount from
  the one we asked for, the order is flagged and nothing is delivered.
- **Links are signed and expire.** Each download link is an HMAC-signed token tied to
  one order, valid for `link_ttl_days` and `max_downloads`.
- **A refund closes the download** and voids any commission that has not been paid.
- **The webhook is a backup.** If a buyer closes the tab before the capture finishes,
  PayPal's `PAYMENT.CAPTURE.COMPLETED` notification completes the order and sends the
  email. Signatures are verified with PayPal before anything is trusted.
- **CSRF protection** on every state-changing endpoint, plus same-origin checks.
- **Rate limiting** on code checks, orders, downloads, resends and the admin report.
- **Security headers** on every page: Content-Security-Policy with a per-request nonce,
  `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`
  and HSTS over HTTPS.
- **No directory listing**, and data files are denied even if one is ever misplaced
  into the web folder.

---

## 8. Changing the stylesheet or the script

`.htaccess` tells browsers to keep CSS and JavaScript for a week, which keeps the
site fast. To stop that hiding your changes, the page stamps each address with the
file's own modification date:

```html
<link rel="stylesheet" href="/assets/css/veyl.css?v=1793158773">
```

Upload a changed `veyl.css` and the number changes by itself, so browsers fetch the
new copy immediately. There is nothing to remember and no version to bump by hand.

If you ever edit a file in place on the server and the change does not appear, check
that the number in the page source moved. If it did not, the file did not actually
save.

---

## 9. Everything still to fill in

### Must be done before taking real money

| Where | What |
|---|---|
| `config.php` | Does not exist yet. Copy `config.example.php` and fill it in. |
| `config.php` | `paypal_client_id`, `paypal_secret`, `paypal_webhook_id` |
| `config.php` | `hmac_secret` and `admin_key` — long, random, different from each other |
| `config.php` | `mail_driver` is `log`, which sends nothing. Change it and test. |
| `kits/` | **Empty.** No guide can be sold until its PDF is here. |
| `products.json` | All ten products are `coming_soon`. |

### Legal pages — a lawyer should read these

They are written in plain language and marked as drafts on the page itself. Each
placeholder appears on the live page as `[TODO: ...]`, so nothing can ship unnoticed.

| Page | What is missing |
|---|---|
| Terms | Legal business name, registered address, country of registration |
| Terms | Governing law and where disputes are handled |
| Terms | The EU and UK withdrawal-right waiver wording |
| Terms | The limitation-of-liability section, for US buyers and for your own country |
| Privacy | Legal business name, registered address, country of registration |
| Privacy | The names of your email provider and hosting provider |
| Privacy | How long you keep order records and server logs |
| Privacy | Which privacy laws apply to you, and whether a transfer mechanism is needed |

The **no-refund policy** is the clause most worth a lawyer's time. It is enforceable for
digital goods delivered immediately in most places, but it interacts differently with
consumer law in the US, the UK and the EU, and the checkout wording is what makes the
EU waiver work.

### Figures

Every dollar figure carries its source on the page. Nine of the ten were confirmed
against published reporting. One was not:

- **`ivf-clinic-cost-guide`** is marked `"verified": false` in `products.json`.
  Published estimates genuinely disagree: a widely cited professional-body figure of
  about $12,400 covers the procedure only, while all-in estimates run from roughly
  $15,000 to $30,000 depending on what is counted. The page shows a range, says the
  estimates vary, and the product page prints an extra line saying we could not confirm
  a single authoritative figure.

- **`franchise-fdd-guide`** deliberately shows **no dollar figure**. No regulator
  publishes an average franchise investment, and the private surveys that do disagree
  by hundreds of thousands of dollars. The page uses the FTC's 14-day rule instead,
  and explains why there is no dollar line.

- **`timeshare-presentation-guide`** uses ARDA/EY's 2025 edition (2024 data). A later
  edition reportedly shows higher figures, but we could not confirm it against the
  industry report itself, so the confirmed numbers are shown with the edition named.

Figures go out of date. Re-check them once a year, and update both the number and the
source label in `products.json` together.

### Not included

- There is no testimonial, rating, sales-count or "trusted by" section anywhere, and
  none should be added until real ones exist.
- There is no influencer dashboard. Payouts are read from the report and sent by hand.
- There is no analytics tool. If you add one, say so in the Privacy Policy and add a
  cookie notice if it sets non-essential cookies.
