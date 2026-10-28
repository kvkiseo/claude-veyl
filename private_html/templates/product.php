<?php
/** @var array $product  set by public_html/products/view.php */
$blurbs   = V::data()['sectionBlurbs'];
$stake    = $product['stake'];
$buyable  = V::isBuyable($product);
$list     = V::basePriceCents();
$discount = V::discountedPriceCents();
$percent  = (int) V::pricing('discountPercent');
$csrf     = V::csrfToken();
$prefill  = isset($_GET['code']) ? V::cleanCode($_GET['code']) : '';
$payState = isset($_GET['pay']) ? preg_replace('/[^a-z_]/', '', $_GET['pay']) : '';
$consentText = 'I understand this is a digital product delivered immediately, all sales are final, and I have read the Terms and Disclaimer.';
?>
<section class="section" style="padding-bottom:32px">
  <div class="shell">
    <nav class="crumbs" aria-label="Breadcrumb">
      <a href="/">Home</a> / <a href="/products/">Products</a> / <span aria-current="page"><?= View::e($product['name']) ?></span>
    </nav>

    <div class="product__head">
      <div>
        <span class="badge <?= $buyable ? 'badge--live' : 'badge--soon' ?>"><?= $buyable ? 'Available now' : 'Coming soon' ?></span>
        <h1 class="display" style="margin:16px 0 14px"><?= View::e($product['name']) ?></h1>
        <p class="lede"><?= View::e($product['oneLine']) ?></p>

        <h2 class="sr-only">In plain language</h2>
        <div class="plain">
          <div class="plain__block">
            <h3>What this is</h3>
            <p><?= View::e($product['whatThisIs']) ?></p>
          </div>
          <div class="plain__block">
            <h3>If you use this guide</h3>
            <p><?= View::e($product['ifYouUse']) ?></p>
          </div>
          <div class="plain__block">
            <h3>If you skip it</h3>
            <p><?= View::e($product['ifYouSkip']) ?></p>
          </div>
        </div>
      </div>

      <!-- ---------------------------------------------------------------- buy -->
      <aside class="buy" id="buy" aria-labelledby="buy-h"
             data-buy
             data-slug="<?= View::e($product['slug']) ?>"
             data-list="<?= $list ?>"
             data-csrf="<?= View::e($csrf) ?>"
             <?= $buyable ? 'data-buyable="1"' : '' ?>>
        <h2 class="sr-only" id="buy-h">Buy this guide</h2>

        <p class="buy__price">
          <span class="buy__now" data-price-now><?= View::e(V::money($list)) ?></span>
          <span class="buy__was" data-price-was hidden></span>
          <span class="buy__unit">one payment, USD</span>
        </p>
        <p class="buy__saving" data-saving hidden></p>

        <?php if ($payState === 'canceled'): ?>
          <p class="msg msg--bad" role="status">Payment was canceled. You have not been charged.</p>
        <?php elseif ($payState === 'failed'): ?>
          <p class="msg msg--bad" role="status">That payment did not go through. You have not been charged. Please try again, or email <a href="mailto:<?= View::e(V::supportEmail()) ?>"><?= View::e(V::supportEmail()) ?></a>.</p>
        <?php elseif ($payState === 'code_invalid'): ?>
          <p class="msg msg--bad" role="status">That code was not recognized, so nothing was charged. Check the spelling and try again.</p>
        <?php elseif ($payState === 'consent'): ?>
          <p class="msg msg--bad" role="status">Please tick the box to confirm you have read the Terms and Disclaimer.</p>
        <?php endif; ?>

        <?php if ($buyable): ?>

        <form id="pay-form" class="buy__form" action="/api/create-order.php" method="post">
          <input type="hidden" name="_csrf" value="<?= View::e($csrf) ?>">
          <input type="hidden" name="slug" value="<?= View::e($product['slug']) ?>">

          <div class="field">
            <label for="code">Discount code <span class="muted">(optional)</span></label>
            <div class="field__row">
              <input type="text" id="code" name="code" value="<?= View::e($prefill) ?>"
                     autocomplete="off" autocapitalize="characters" spellcheck="false"
                     maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9-]{1,31}"
                     placeholder="Enter code" aria-describedby="code-msg">
              <button class="btn btn--ghost btn--sm" type="button" data-code-apply>Apply</button>
            </div>
            <p class="msg" id="code-msg" role="status" aria-live="polite" data-code-msg></p>
          </div>

          <label class="consent" for="consent">
            <input type="checkbox" id="consent" name="consent" value="1" required>
            <span><?= View::e($consentText) ?> <a href="/terms/">Terms and No-Refund Policy</a> &middot; <a href="/disclaimer/">Disclaimer</a></span>
          </label>

          <p class="msg" role="status" aria-live="polite" data-pay-msg></p>
          <div class="paypal-slot" data-paypal-slot></div>

          <div class="buy__fallback" data-fallback>
            <button class="btn btn--dark btn--block" type="submit">Buy with PayPal</button>
          </div>
        </form>

        <p class="fine buy__legal">All sales are final. This is a digital product delivered immediately, so there are no refunds. Your download link works for <?= (int) V::cfg('link_ttl_days', 30) ?> days.</p>
        <p class="fine">Have a creator&rsquo;s code? It takes <?= $percent ?>% off, bringing this guide to <?= View::e(V::money($discount)) ?>.</p>

        <?php else: ?>

        <p><button class="btn btn--dark btn--block" type="button" disabled>Coming soon</button></p>
        <div class="buy__soon">
          <p>This guide is not available to buy yet.</p>
          <p>Veyl never sells a guide it cannot deliver straight away, so the button stays switched off until the PDF is finished.</p>
        </div>
        <p class="fine">Want to know when it is ready? Email <a href="mailto:<?= View::e(V::supportEmail()) ?>"><?= View::e(V::supportEmail()) ?></a> and we will tell you.</p>

        <?php endif; ?>

        <p class="fine" style="margin-bottom:0">Educational only. Not legal, financial, or medical advice.</p>
      </aside>
    </div>
  </div>
</section>

<!-- ------------------------------------------------------------- at stake -->
<section class="section" style="padding-top:0" aria-labelledby="stake-h">
  <div class="shell">
    <div class="stake reveal">
      <h2 class="eyebrow" id="stake-h"><?= View::e(isset($stake['eyebrow']) ? $stake['eyebrow'] : 'What is at stake in this decision') ?></h2>
      <span class="stake__amount"><?= View::e($stake['amount']) ?></span>
      <p><?= View::e($stake['line']) ?></p>
      <p class="stake__src"><?= View::e($stake['source']) ?><?= $stake['verified'] ? '' : ' &mdash; estimates vary and we could not confirm a single authoritative figure.' ?></p>
      <?php if (!empty($stake['note'])): ?>
      <p class="stake__src"><?= View::e($stake['note']) ?></p>
      <?php endif; ?>
      <p class="stake__src" style="margin-top:8px"><?= View::e(V::site('figureNote')) ?></p>
    </div>
  </div>
</section>

<!-- --------------------------------------------------------- what is inside -->
<section class="section" style="padding-top:0" aria-labelledby="inside-h">
  <div class="shell">
    <div class="section__head reveal">
      <h2 class="display" id="inside-h">What is inside</h2>
      <p>One fillable PDF. Four sections. Here is what each one covers for this decision.</p>
    </div>
    <div class="inside">
      <?php foreach ($product['inside'] as $section => $items): ?>
      <article class="card inside__card reveal">
        <h3><?= View::e($section) ?></h3>
        <p><?= View::e($blurbs[$section]) ?></p>
        <ul>
          <?php foreach ($items as $item): ?>
          <li><?= View::e($item) ?></li>
          <?php endforeach; ?>
        </ul>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ------------------------------------------------------------------ preview -->
<section class="section" style="padding-top:0" aria-labelledby="preview-h">
  <div class="shell">
    <div class="section__head reveal">
      <h2 class="display" id="preview-h">Illustrative preview</h2>
      <p>These four cards show how each section is laid out. They are illustrations drawn for this page, not pictures of the finished PDF.</p>
    </div>
    <div class="previews reveal">
      <?php foreach (['Lessons', 'Script', 'Forms', 'Checklist'] as $section): ?>
      <?= View::previewPage($product, $section) ?>
      <?php endforeach; ?>
    </div>
    <p class="fine" style="margin-top:16px">Illustrative preview. Sample content, not the real pages.</p>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="shell">
    <div class="panel" style="text-align:center">
      <h2 class="display-sm"><?= View::e($product['name']) ?></h2>
      <p class="lede" style="margin:12px auto 20px;max-width:32rem">One fillable PDF, <?= View::e(V::money($list)) ?>. Educational only. Not legal, financial, or medical advice.</p>
      <div class="btn-row" style="justify-content:center">
        <?php if ($buyable): ?>
        <a class="btn btn--dark" href="#buy">Buy this guide</a>
        <?php else: ?>
        <span class="btn btn--dark" aria-disabled="true">Coming soon</span>
        <?php endif; ?>
        <a class="btn btn--light" href="/products/">Back to all guides</a>
      </div>
    </div>
  </div>
</section>

<?php if ($buyable): ?>
<script type="application/json" id="veyl-paypal" nonce="<?= View::e(V::nonce()) ?>"><?= json_encode([
    'clientId' => PayPal::clientId(),
    'currency' => V::pricing('currency'),
    'env'      => V::cfg('paypal_env', 'sandbox'),
]) ?></script>
<?php endif; ?>
