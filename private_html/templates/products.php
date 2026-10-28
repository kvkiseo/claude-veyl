<?php $products = V::products(); ?>
<section class="section">
  <div class="shell">
    <div class="hero__inner" style="text-align:center;margin-bottom:36px">
      <span class="badge"><?= count($products) ?> guides &middot; <?= View::e(V::money(V::basePriceCents())) ?> each</span>
      <h1 class="display" style="margin-top:18px">Every guide</h1>
      <p class="lede" style="margin-top:16px">One fillable PDF for one decision. Choose the one you are facing.</p>
    </div>

    <h2 class="sr-only">All <?= count($products) ?> guides</h2>
    <div class="grid grid--3">
      <?php foreach ($products as $product): ?>
      <?= View::productCard($product) ?>
      <?php endforeach; ?>
    </div>

    <p class="fine" style="margin-top:24px;max-width:46rem"><?= View::e(V::site('figureNote')) ?> Each figure shows the amount at stake in that decision. It is not a saving, and nothing on this site promises one.</p>
  </div>
</section>
