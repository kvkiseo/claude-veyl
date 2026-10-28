<?php
$support = V::supportEmail();
$days    = (int) V::cfg('link_ttl_days', 30);
$maxDl   = (int) V::cfg('max_downloads', 10);
$csrf    = V::csrfToken();
?>
<section class="section">
  <div class="shell shell--narrow">

    <div class="panel" data-order-panel>
      <p data-order-badge hidden><span class="badge badge--live">Payment confirmed</span></p>
      <h1 class="display" data-order-title>Thank you</h1>

      <!-- Shown while the order is being looked up. -->
      <div data-order-loading>
        <p class="lede">We are fetching your download link.</p>
        <noscript>
          <p>Your browser has JavaScript switched off, so the link cannot be shown here. It has also been emailed to you. If it has not arrived, use the form below or email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a>.</p>
        </noscript>
      </div>

      <!-- Filled in by site.js once the order is confirmed. -->
      <div data-order-ok hidden>
        <p class="lede">Order <strong data-order-id></strong>. A copy of this link has also been sent to <strong data-order-email></strong>.</p>

        <div class="dl">
          <h2 data-order-product></h2>
          <p>One fillable PDF. Save it to your device once it has downloaded.</p>
          <a class="btn btn--dark" href="#" data-order-link>Download your guide</a>
        </div>

        <p class="fine">This link works for <?= $days ?> days and up to <?= $maxDl ?> downloads. Open the file in a PDF reader rather than inside your browser, so that what you type is saved.</p>
        <p class="fine">All sales are final. If the file will not open or the link will not work, email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a> and we will fix it.</p>
      </div>

      <!-- Shown when the link in the address is missing, expired or not recognized. -->
      <div data-order-missing hidden>
        <p class="lede">The link in your address bar is missing, has expired, or does not match an order.</p>
        <p>If you have already paid, your download link was emailed to you. You can also ask for a new one below.</p>
      </div>
    </div>

    <div class="panel" id="resend" style="margin-top:20px">
      <h2 class="display-sm">Lost your link?</h2>
      <p class="muted" style="margin-top:8px">Enter the email address on your PayPal account and we will send your download link again.</p>

      <form data-resend action="/api/resend.php" method="post" style="margin-top:4px">
        <input type="hidden" name="_csrf" value="<?= View::e($csrf) ?>">
        <div class="field">
          <label for="resend-email">Your email address</label>
          <div class="field__row">
            <input type="email" id="resend-email" name="email" required autocomplete="email"
                   placeholder="you@example.com" aria-describedby="resend-msg">
            <button class="btn btn--dark btn--sm" type="submit">Send link</button>
          </div>
          <p class="msg" id="resend-msg" role="status" aria-live="polite" data-resend-msg></p>
        </div>
      </form>

      <p class="fine" style="margin-bottom:0">For your security we give the same answer whether or not an order exists, so nobody can use this form to find out who has bought.</p>
    </div>

    <p class="fine" style="margin-top:24px">Educational only. Not legal, financial, or medical advice. <a href="/products/">Back to all guides</a></p>
  </div>
</section>
