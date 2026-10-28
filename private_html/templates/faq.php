<?php
$support  = V::supportEmail();
$list     = V::money(V::basePriceCents());
$discount = V::money(V::discountedPriceCents());
$percent  = (int) V::pricing('discountPercent');
$days     = (int) V::cfg('link_ttl_days', 30);
$maxDl    = (int) V::cfg('max_downloads', 10);

$faqs = [
    ['What exactly do I get for ' . $list . '?',
     '<p>One fillable PDF guide for one decision. It has four sections: Lessons, Script, Forms and Checklist. There is nothing else to buy, no subscription, and no add-ons.</p>'],

    ['What is a fillable PDF?',
     '<p>It is a PDF with boxes you can type into and save, rather than one you have to print before you can use it. You fill in your own numbers and answers, save the file, and come back to it as the decision moves along.</p>'],

    ['What is inside each guide?',
     '<p><strong>Lessons</strong> explain in plain language how that deal works and where the money usually moves. <strong>Script</strong> gives you the exact questions to ask and lines to use. <strong>Forms</strong> are pages you fill in as you go, so your numbers stay in one place. <strong>Checklist</strong> is the step-by-step list you tick off so nothing is missed.</p>'],

    ['How do I receive my guide?',
     '<p>Straight after your payment is confirmed, a download link appears on screen. The same link is emailed to the address on your PayPal account. If it has not arrived within a few minutes, check your spam folder.</p>'],

    ['How long does my download link last?',
     '<p>It works for ' . $days . ' days and up to ' . $maxDl . ' downloads. Please save the file to your own device once you have it. If a link stops working, you can ask for a new one from the download page, or email ' . V::esc($support) . '.</p>'],

    ['Which devices and apps can open it?',
     '<p>Any device with a PDF reader. On a computer, Adobe Acrobat Reader, Preview on a Mac, or any modern browser will open it and save what you type. On a phone or tablet, use the Files app on iPhone and iPad, or Adobe Acrobat Reader on Android.</p>'
     . '<p>One thing to know: some browser PDF viewers will let you type but not save. If your answers disappear, download the file and open it in a proper PDF reader instead.</p>'],

    ['What is a discount code, and how do I apply it?',
     '<p>Creators who recommend a guide sometimes have a code for their audience. A valid code takes ' . $percent . '% off, bringing a guide from ' . $list . ' to ' . $discount . '.</p>'
     . '<p>Type it into the <em>Discount code</em> box on the product page and press Apply. The new price is shown before you pay. If you arrived through a link that already contained a code, it is filled in for you, and you can remove it if you prefer.</p>'
     . '<p>A creator whose code is used earns a commission from Veyl. It does not change what you pay.</p>'],

    ['My code was not accepted. What now?',
     '<p>Codes are not case sensitive, but spelling and dashes matter. Check it against the one you were given. A code may also have been switched off, or may only apply to certain guides. If it still will not work, email ' . V::esc($support) . ' and tell us which code and which guide.</p>'],

    ['How do I pay?',
     '<p>Through PayPal. You can pay with your PayPal balance, or with a debit or credit card through PayPal&rsquo;s own checkout. Payment happens on PayPal&rsquo;s pages. Veyl never sees or stores your card number or your PayPal password.</p>'],

    ['Do you offer refunds?',
     '<p>No. All sales are final. Each guide is a digital product delivered immediately, so there are no refunds, returns or exchanges. That is set out on every product page, in the box you tick at checkout, and on the <a href="/terms/">Terms and No-Refund Policy</a> page.</p>'
     . '<p>This is why every product page explains in detail what is inside before you buy. If something has genuinely gone wrong &mdash; a file that will not open, a link that will not work &mdash; email ' . V::esc($support) . ' and we will fix it.</p>'],

    ['Why is a guide marked &ldquo;Coming soon&rdquo;?',
     '<p>Because its PDF is not finished. Veyl never takes money for a guide it cannot deliver immediately, so the buy button stays switched off until the file is ready.</p>'],

    ['Is this legal, financial, or medical advice?',
     '<p>No. Veyl guides are educational only. They help you prepare for a conversation, ask better questions and keep track of the answers. They are not advice about your own circumstances and buying one does not create a professional relationship.</p>'
     . '<p>For decisions that turn on your specific legal, financial, tax or medical position, speak to a licensed professional. See the <a href="/disclaimer/">Disclaimer</a>.</p>'],

    ['Will a guide save me money?',
     '<p>We do not promise that, and nothing on this site should be read as promising it. The figures shown on each product page describe the size of the decision, not a saving. What a guide does is make sure the questions get asked.</p>'],

    ['Do the guides work outside the United States?',
     '<p>They are written with the United States in mind. The questions and the structure travel well, but prices, laws and common practice differ by country and by state. Check your local position before relying on anything in a guide.</p>'],

    ['Can I share my guide with someone else?',
     '<p>A guide is licensed for your own personal use. Please do not resell or redistribute it. Sharing it with your partner, or whoever is making the decision with you, is fine.</p>'],

    ['Do you keep my personal details?',
     '<p>We keep what we need to complete your order: your email address from PayPal, which guide you bought, the amount, the date, and PayPal&rsquo;s own order and transaction numbers. We do not sell data. What you type into your PDF stays on your own device and never reaches us. See the <a href="/privacy/">Privacy Policy</a>.</p>'],

    ['How do I contact support?',
     '<p>Email <a href="mailto:' . V::esc($support) . '">' . V::esc($support) . '</a>. Please include your order number if you have one &mdash; it begins with VL- and appears on your download page and in your confirmation email.</p>'],
];
?>
<section class="section">
  <div class="shell shell--narrow">
    <div class="hero__inner" style="text-align:center;margin-bottom:36px">
      <span class="badge">FAQ</span>
      <h1 class="display" style="margin-top:18px">Questions, answered</h1>
      <p class="lede" style="margin-top:16px">Everything about what a guide is, how it reaches you, and what we do not do.</p>
    </div>

    <div class="acc" data-acc>
      <?php foreach ($faqs as $i => $faq): $n = $i + 1; ?>
      <div class="acc__item">
        <h2><button class="acc__q" type="button" aria-expanded="false" aria-controls="faq-a<?= $n ?>" id="faq-q<?= $n ?>">
          <span><?= $faq[0] ?></span>
          <svg viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </button></h2>
        <div class="acc__a" id="faq-a<?= $n ?>" role="region" aria-labelledby="faq-q<?= $n ?>" hidden><?= $faq[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="fine" style="margin-top:28px">Still stuck? Email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a>.</p>
  </div>
</section>
