<?php
$products = V::products();
$blurbs   = V::data()['sectionBlurbs'];
$price    = V::money(V::basePriceCents());
?>
<section class="hero">
  <div class="shell">
    <div class="hero__inner">
      <span class="badge">Fillable PDF guides &middot; <?= View::e($price) ?> each</span>
      <h1 class="display-xl">The decisions you make <em class="accent">once</em>, prepared for properly.</h1>
      <p class="lede">Veyl makes one thing: a fillable PDF guide for a single large, costly decision. Lessons, a script, forms to complete, and a checklist &mdash; so you walk in knowing what to ask.</p>
      <div class="btn-row">
        <a class="btn btn--dark" href="/products/">See the ten guides</a>
        <a class="btn btn--light" href="#how">How it works</a>
      </div>
      <p class="fine hero__note">One guide, one decision, <?= View::e($price) ?>. No subscription, no upsell.</p>
    </div>
  </div>
</section>

<section class="section" id="how" aria-labelledby="how-h">
  <div class="shell">
    <div class="section__head reveal">
      <h2 class="display" id="how-h">How it works</h2>
      <p>Three steps, and the guide is on your device.</p>
    </div>
    <ol class="steps reveal">
      <li>
        <span class="steps__n" aria-hidden="true">1</span>
        <h3>Choose your guide</h3>
        <p>Ten decisions, one guide each. Every product page explains exactly what is inside before you buy.</p>
      </li>
      <li>
        <span class="steps__n" aria-hidden="true">2</span>
        <h3>Check out with PayPal</h3>
        <p>Payment is handled on PayPal&rsquo;s own pages. If you have a discount code, you apply it before you pay.</p>
      </li>
      <li>
        <span class="steps__n" aria-hidden="true">3</span>
        <h3>Get your fillable PDF</h3>
        <p>Your download link appears straight away and arrives by email. Fill it in on a phone, a tablet or a computer.</p>
      </li>
    </ol>
  </div>
</section>

<section class="section" aria-labelledby="inside-h">
  <div class="shell">
    <div class="section__head reveal">
      <h2 class="display" id="inside-h">What is inside every guide</h2>
      <p>Four sections, in the same order, in every one.</p>
    </div>
    <div class="grid grid--4 strip reveal">
      <?php $n = 1; foreach ($blurbs as $name => $blurb): ?>
      <article class="card">
        <span class="strip__num" aria-hidden="true"><?= $n++ ?></span>
        <h3><?= View::e($name) ?></h3>
        <p><?= View::e($blurb) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="guides" aria-labelledby="guides-h">
  <div class="shell">
    <div class="section__head reveal">
      <h2 class="display" id="guides-h">The ten guides</h2>
      <p>Each one covers a single decision, and names what is at stake in it.</p>
    </div>
    <div class="grid grid--3">
      <?php foreach ($products as $product): ?>
      <?= View::productCard($product) ?>
      <?php endforeach; ?>
    </div>
    <p class="fine" style="margin-top:20px"><?= View::e(V::site('figureNote')) ?></p>
  </div>
</section>

<section class="section" aria-labelledby="faq-h">
  <div class="shell shell--narrow">
    <div class="section__head reveal">
      <h2 class="display" id="faq-h">Common questions</h2>
    </div>
    <div class="acc reveal" data-acc>
      <div class="acc__item">
        <h3><button class="acc__q" type="button" aria-expanded="false" aria-controls="h-a1" id="h-q1">What is a fillable PDF?<svg viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button></h3>
        <div class="acc__a" id="h-a1" role="region" aria-labelledby="h-q1" hidden><p>A PDF with boxes you can type into and save, rather than one you have to print first. You can fill it in on a phone, a tablet or a computer, and come back to it later.</p></div>
      </div>
      <div class="acc__item">
        <h3><button class="acc__q" type="button" aria-expanded="false" aria-controls="h-a2" id="h-q2">How do I receive it?<svg viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button></h3>
        <div class="acc__a" id="h-a2" role="region" aria-labelledby="h-q2" hidden><p>Straight after payment, a download link appears on screen and is sent to the email address on your PayPal account.</p></div>
      </div>
      <div class="acc__item">
        <h3><button class="acc__q" type="button" aria-expanded="false" aria-controls="h-a3" id="h-q3">Is this legal, financial, or medical advice?<svg viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button></h3>
        <div class="acc__a" id="h-a3" role="region" aria-labelledby="h-q3" hidden><p>No. Veyl guides are educational. They help you prepare, ask better questions and keep track of answers. For advice about your own situation, speak to a licensed professional.</p></div>
      </div>
    </div>
    <p style="margin-top:24px"><a class="btn btn--light" href="/faq/">Read all the questions</a></p>
  </div>
</section>
