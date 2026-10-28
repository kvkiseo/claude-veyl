<?php
$support = V::supportEmail();
$days    = (int) V::cfg('link_ttl_days', 30);
?>
<section class="section">
  <div class="shell shell--narrow">
    <h1 class="display">Privacy Policy</h1>
    <p class="muted" style="margin-top:10px">Last updated: <?= date('j F Y') ?></p>

    <div class="panel" style="margin-top:28px">
      <div class="notice">
        <p><strong>Plain-language draft. Have a lawyer review this page before you rely on it</strong>, particularly if you sell to buyers in the European Union, the United Kingdom or California.</p>
      </div>

      <div class="prose">
        <p class="lede">The short version: we collect your email address and your order details so we can send you the guide you bought. We do not sell your data, and we do not use advertising or tracking cookies.</p>

        <h2>Who is responsible for your data</h2>
        <p><mark>[TODO: legal business name]</mark>, <mark>[TODO: registered business address]</mark>, <mark>[TODO: country of registration]</mark>. Contact: <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a>.</p>

        <h2>What we collect</h2>
        <ul>
          <li><strong>When you buy.</strong> Your email address and name as PayPal gives them to us, which guide you bought, the amount, the date, any discount code used, and PayPal&rsquo;s order and transaction numbers. We also record that you ticked the box confirming all sales are final. We never receive your card number or your PayPal password.</li>
          <li><strong>When you ask for a download link again.</strong> The email address you type in, so we can look up your order.</li>
          <li><strong>When you visit.</strong> Our web host keeps standard server logs, which include your IP address, the page you asked for and the time.</li>
          <li><strong>Browser storage.</strong> One essential cookie, named <code>veyl_csrf</code>. It holds a random value that lets us confirm a checkout request genuinely came from this website. It contains nothing about you, is deleted when you close your browser, and is required for the shop to work. We set no advertising, analytics or tracking cookies, which is why this site shows no cookie banner.</li>
        </ul>
        <p>What you type into your PDF stays on your own device. It is never sent to us and we never see it. Please do not email us financial documents, contracts or medical information &mdash; we do not need them.</p>

        <h2>Why we use it, and on what legal basis</h2>
        <ul>
          <li><strong>To complete your order and send you your guide.</strong> This is necessary to perform our contract with you.</li>
          <li><strong>To re-send a download link when you ask.</strong> Also part of performing that contract.</li>
          <li><strong>To answer your emails.</strong> Our legitimate interest in supporting customers.</li>
          <li><strong>To keep accounts and meet tax obligations.</strong> A legal obligation.</li>
          <li><strong>To prevent fraud and abuse of the site.</strong> Our legitimate interest in protecting the service.</li>
        </ul>
        <p>We do not send marketing email. There is no mailing list, and we do not add buyers to one.</p>

        <h2>We do not sell your data</h2>
        <p>We do not sell, rent or trade personal information, and we do not share it for advertising.</p>

        <h2>Who else handles it</h2>
        <ul>
          <li><strong>PayPal</strong> takes the payment and passes us your email address, name and the transaction details. PayPal&rsquo;s own privacy policy covers what it collects.</li>
          <li><strong>Our email provider</strong>, which sends your confirmation and download link: <mark>[TODO: name your email provider, for example Resend]</mark>.</li>
          <li><strong>Our web host</strong>, which runs the website and stores the order records: <mark>[TODO: name your hosting provider]</mark>.</li>
          <li><strong>Anyone we are legally required to tell</strong>, or need to tell to protect our legal rights.</li>
        </ul>
        <p>People who issue discount codes are told how many sales their code produced and what commission is owed. They are not told who bought, and they never see your name, email address or any other personal detail.</p>

        <h2>How long we keep it</h2>
        <ul>
          <li><strong>Order records</strong> are kept for <mark>[TODO: number of years, commonly 6 to 7 for tax and accounting]</mark>, because tax and accounting rules require it.</li>
          <li><strong>Download links</strong> stop working after <?= $days ?> days.</li>
          <li><strong>Server logs</strong> are kept for <mark>[TODO: your host&rsquo;s log retention period]</mark>.</li>
          <li><strong>Emails you send us</strong> are kept while we need them to help you, and then deleted.</li>
        </ul>

        <h2>Your rights</h2>
        <p>Depending on where you live, you may have the right to ask for a copy of the personal information we hold about you, to have it corrected, to have it deleted, to object to or restrict how we use it, or to receive it in a portable form. Email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a> and we will respond.</p>
        <p>Please note that we cannot delete an order record while tax law requires us to keep it. If you are in the European Union or the United Kingdom, you also have the right to complain to your data protection authority.</p>

        <h2>Where your data is handled</h2>
        <p>We operate from <mark>[TODO: country of business]</mark> and sell mainly to buyers in the United States and Europe. Our payment, email and hosting providers may process data in other countries, including the United States. <mark>[TODO: have a lawyer confirm which privacy laws apply to you and whether a transfer mechanism is needed.]</mark></p>

        <h2>Children</h2>
        <p>This website is not intended for children, and we do not knowingly collect information from anyone under 16.</p>

        <h2>Security</h2>
        <p>The site runs over HTTPS. Order records and discount code data are stored outside the public web folder, where they cannot be reached by a web address. Download links are individually signed and expire. No system is perfectly secure, but we take reasonable steps to protect what we hold.</p>

        <h2>Changes</h2>
        <p>If this policy changes, the date at the top of the page changes with it.</p>

        <h2>Contact</h2>
        <p>Email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a>.</p>
      </div>
    </div>
  </div>
</section>
