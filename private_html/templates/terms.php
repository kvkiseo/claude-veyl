<?php
$support = V::supportEmail();
$days    = (int) V::cfg('link_ttl_days', 30);
$maxDl   = (int) V::cfg('max_downloads', 10);
?>
<section class="section">
  <div class="shell shell--narrow">
    <h1 class="display">Terms and No-Refund Policy</h1>
    <p class="muted" style="margin-top:10px">Last updated: <?= date('j F Y') ?></p>

    <div class="panel" style="margin-top:28px">
      <div class="notice">
        <p><strong>Plain-language draft. Have a lawyer review this page before you rely on it.</strong> It has not been checked by a qualified lawyer in any jurisdiction, and the no-refund term in particular interacts with consumer law differently in the United States, the United Kingdom and the European Union.</p>
      </div>

      <div class="prose">
        <p>These terms apply when you buy or use anything from this website. By placing an order you agree to them.</p>

        <h2>1. Who you are dealing with</h2>
        <p>This website is operated by <mark>[TODO: legal business name]</mark>, <mark>[TODO: registered business address]</mark>, <mark>[TODO: country of registration]</mark>. You can reach us at <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a>. We refer to ourselves below as &ldquo;Veyl&rdquo;, &ldquo;we&rdquo; or &ldquo;us&rdquo;, and to you as &ldquo;you&rdquo;.</p>

        <h2>2. What you are buying</h2>
        <p>Each product is a single fillable PDF guide for one decision. It contains four sections: Lessons, Script, Forms and Checklist. It is a digital file. Nothing is posted to you, and there is no physical product, subscription, consultation or ongoing service attached to it.</p>
        <p>Each guide is sold at the price shown on its product page at the time you order. Prices are in US dollars.</p>

        <h2>3. All sales are final. There are no refunds.</h2>
        <p>Because a guide is a digital file that is delivered to you immediately after payment, <strong>all sales are final</strong>. We do not offer refunds, returns, exchanges, cancellations or partial credits once an order is complete.</p>
        <p>Before you pay, you are asked to tick a box confirming that you understand this. Your order cannot be completed without it. We record that you ticked it, together with the date and the wording you agreed to.</p>
        <p>If you are shopping from the United Kingdom or the European Union, you normally have a right to cancel a distance purchase within 14 days. For digital content delivered immediately, that right can be given up, and ticking the box at checkout is how you do so. <mark>[TODO: have a lawyer confirm this wording for UK and EU buyers before you sell to them.]</mark></p>
        <p>This clause does not take away any right you have under the law of your own country that cannot be given up by agreement.</p>

        <h2>4. If something has genuinely gone wrong</h2>
        <p>No refund policy covers a file that does not work. If your download link fails, the file will not open, or you received the wrong guide, email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a> and we will put it right. That is a separate matter from changing your mind.</p>

        <h2>5. Delivery and your download link</h2>
        <p>After payment is confirmed, a download link appears on screen and is sent to the email address attached to your PayPal account. The link is unique to your order, works for <?= $days ?> days, and allows up to <?= $maxDl ?> downloads. Please save the file to your own device.</p>
        <p>If a link expires, you can request a new one using the email address you ordered with, or by emailing us.</p>

        <h2>6. Your license to use a guide</h2>
        <p>When you buy a guide, you get a personal, non-exclusive, non-transferable license to use it for your own decision, including with the people making that decision alongside you.</p>
        <p>You may not resell it, share it publicly, upload it anywhere others can download it, use it to provide a service to clients, or incorporate it into another product. We keep all copyright and other rights in the guides.</p>

        <h2>7. Discount codes</h2>
        <p>Discount codes are issued by us, may be withdrawn or switched off at any time, apply only to the guides they are issued for, and cannot be exchanged for cash. One code may be applied per order. A code used on an order may earn the person who issued it a commission from us. That commission does not change your price.</p>

        <h2>8. Payment</h2>
        <p>Payments are taken by PayPal. Your card or account details are handled by PayPal and are never seen or stored by us. PayPal&rsquo;s own terms apply to the payment itself. Your order is complete when PayPal confirms the payment to us.</p>

        <h2>9. Guides are educational only</h2>
        <p>Veyl guides are educational material. They are <strong>not legal, financial, tax, medical or professional advice</strong>, they are not specific to your circumstances, and buying one does not create any professional relationship between us.</p>
        <p>We do not promise any outcome. A guide cannot guarantee a better price, a better contract, a better result or any saving. The figures shown on this site describe the size of a decision; they are not predictions and they are not promises. The <a href="/disclaimer/">Disclaimer</a> forms part of these terms.</p>

        <h2>10. Guides are written for the United States</h2>
        <p>Our guides are written with United States practice in mind. Laws, fees, contract terms and common practice differ by country and by state. If you are elsewhere, check your own position before relying on anything in a guide.</p>

        <h2>11. Using this website</h2>
        <p>Please do not attempt to break into, overload, scrape at scale, or interfere with this website or its ordering system, and do not use it for anything unlawful. We may block access where we reasonably believe this is happening.</p>

        <h2>12. Availability</h2>
        <p>We try to keep the site available, but we do not promise it will never be interrupted. We may change, withdraw or stop selling any guide at any time. That does not affect an order you have already completed.</p>

        <h2>13. Our responsibility to you</h2>
        <p>To the fullest extent the law allows, we are not responsible for any loss that arises from decisions you make after reading a guide, or from using this website. Where we are responsible, our total liability to you for any claim is limited to the amount you paid for the guide the claim relates to.</p>
        <p>Nothing here limits liability for fraud, for death or personal injury caused by negligence, or for anything else that cannot be limited by law. <mark>[TODO: have a lawyer review this section for US buyers and for your own country.]</mark></p>

        <h2>14. Changes to these terms</h2>
        <p>We may update these terms. The version that applies to your order is the one published on the day you ordered. The date at the top of this page shows when it was last changed.</p>

        <h2>15. Which law applies</h2>
        <p><mark>[TODO: choose the country or state whose law governs these terms, and where disputes are handled.]</mark></p>

        <h2>16. Contact</h2>
        <p>Email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a>. It is the only way to contact us.</p>
      </div>
    </div>
  </div>
</section>
