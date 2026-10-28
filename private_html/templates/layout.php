<?php
/**
 * The page shell: head, navigation, footer.
 * $meta and $content are set by View::render().
 */
$siteUrl = V::siteUrl();
$support = V::supportEmail();
$nonce   = V::nonce();
$navItems = View::navItems();
$canonical = $siteUrl . $meta['path'];
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= View::e($meta['title']) ?></title>
<meta name="description" content="<?= View::e($meta['description']) ?>">
<link rel="canonical" href="<?= View::e($canonical) ?>">
<meta name="robots" content="<?= $meta['noindex'] ? 'noindex,nofollow' : 'index,follow,max-image-preview:large' ?>">
<meta name="theme-color" content="#fdfbf9">

<meta property="og:site_name" content="Veyl">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= View::e($meta['title']) ?>">
<meta property="og:description" content="<?= View::e($meta['description']) ?>">
<meta property="og:url" content="<?= View::e($canonical) ?>">
<meta property="og:image" content="<?= View::e($siteUrl . $meta['ogImage']) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Veyl — fillable decision guides">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= View::e($meta['title']) ?>">
<meta name="twitter:description" content="<?= View::e($meta['description']) ?>">
<meta name="twitter:image" content="<?= View::e($siteUrl . $meta['ogImage']) ?>">

<link rel="icon" href="/assets/icons/veyl-favicon.svg" type="image/svg+xml">
<link rel="icon" href="/assets/icons/veyl-favicon-32.png" sizes="32x32" type="image/png">
<link rel="shortcut icon" href="/favicon.ico">
<link rel="apple-touch-icon" href="/assets/icons/veyl-apple-touch-icon.png">

<link rel="preload" href="/assets/fonts/instrument-serif-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/inter-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= View::asset('/assets/css/veyl.css') ?>">
<script nonce="<?= View::e($nonce) ?>">document.documentElement.classList.replace("no-js","js")</script>
<?php if (!empty($meta['jsonLd'])): ?>
<script type="application/ld+json" nonce="<?= View::e($nonce) ?>"><?= json_encode($meta['jsonLd'], JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
</head>
<body<?= $meta['bodyClass'] ? ' class="' . View::e($meta['bodyClass']) . '"' : '' ?>>
<a class="skip" href="#main">Skip to content</a>

<header class="nav" data-nav>
  <div class="nav__bar">
    <a class="nav__logo" href="/" aria-label="Veyl — home"><?= View::wordmark(19) ?></a>
    <nav class="nav__links" aria-label="Main">
      <?php foreach ($navItems as $key => $item): ?>
      <a href="<?= View::e($item[0]) ?>"<?= $meta['nav'] === $key ? ' aria-current="page"' : '' ?>><?= View::e($item[1]) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="nav__actions">
      <a class="btn btn--dark btn--sm" href="/products/">Shop</a>
      <button class="nav__burger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="nav-mobile">
        <span></span><span></span>
      </button>
    </div>
  </div>
  <div class="nav__mobile" id="nav-mobile">
    <?php foreach ($navItems as $key => $item): ?>
    <a href="<?= View::e($item[0]) ?>"<?= $meta['nav'] === $key ? ' aria-current="page"' : '' ?>><?= View::e($item[1]) ?></a>
    <?php endforeach; ?>
  </div>
</header>

<main id="main">
<?= $content ?>
</main>

<footer class="footer">
  <div class="shell">
    <div class="footer__grid">
      <div class="footer__brand">
        <?= View::wordmark(20) ?>
        <p class="fine" style="max-width:26rem"><?= View::e(V::site('tagline')) ?></p>
        <p class="fine">Questions? <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a></p>
      </div>
      <div>
        <h2>Veyl</h2>
        <ul>
          <?php foreach ($navItems as $item): ?>
          <li><a href="<?= View::e($item[0]) ?>"><?= View::e($item[1]) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h2>Legal</h2>
        <ul>
          <li><a href="/terms/">Terms and No-Refund Policy</a></li>
          <li><a href="/disclaimer/">Disclaimer</a></li>
          <li><a href="/privacy/">Privacy Policy</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__legal">
      <p class="fine"><strong>Educational only. Not legal, financial, or medical advice.</strong> Veyl guides are written to help you prepare for a decision. They do not create a professional relationship and are no substitute for a licensed professional in your own situation.</p>
      <p class="fine"><?= View::e(V::site('figureNote')) ?></p>
      <p class="fine">All sales are final. Guides are digital products delivered immediately, so there are no refunds. Payments are handled by PayPal.</p>
      <p class="fine">&copy; <?= date('Y') ?> Veyl. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="<?= View::asset('/assets/js/site.js') ?>" defer nonce="<?= View::e($nonce) ?>"></script>
</body>
</html>
