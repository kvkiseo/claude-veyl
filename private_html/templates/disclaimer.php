<?php $support = V::supportEmail(); ?>
<section class="section">
  <div class="shell shell--narrow">
    <h1 class="display">Disclaimer</h1>
    <p class="muted" style="margin-top:10px">Last updated: <?= date('j F Y') ?></p>

    <div class="panel" style="margin-top:28px">
      <div class="notice">
        <p><strong>Plain-language draft. Have a lawyer review this page before you rely on it.</strong></p>
      </div>

      <div class="prose">
        <p class="lede"><strong>Educational only. Not legal, financial, or medical advice.</strong></p>

        <h2>What Veyl guides are</h2>
        <p>Each Veyl guide is educational material about one type of decision. It explains how that kind of deal usually works, suggests questions to ask, and gives you pages to fill in and a checklist to follow. It is designed to help you prepare.</p>

        <h2>What they are not</h2>
        <p>Veyl guides are not legal advice, financial advice, investment advice, tax advice, medical advice, or advice of any other professional kind. They are not written for your circumstances, because we do not know them. Reading or buying a guide does not create a lawyer&ndash;client, adviser&ndash;client, doctor&ndash;patient or any other professional relationship between you and Veyl.</p>
        <p>Veyl is not a law firm, a financial adviser, a broker, an insurance agent, a tax preparer, a medical practice or a licensed professional of any kind, and does not hold itself out as one.</p>

        <h2>Please speak to a professional</h2>
        <p>For any decision that turns on your own legal, financial, tax or medical position, consult a licensed professional in your own area who can look at your actual situation. Nothing in a guide should delay you from doing that, and nothing in a guide should be used in place of it.</p>
        <p>The guides covering care for a parent and fertility treatment are about comparing providers, costs and contracts. They contain no medical content and must not be used to make any clinical decision. Speak to a qualified clinician.</p>
        <p>The guides covering solar financing and timeshare presentations are about reading the paperwork in heavily regulated transactions. They contain no legal or financial advice about any particular contract. Have a lawyer read anything you are about to sign.</p>

        <h2>No promise of any result</h2>
        <p>We make no promise that using a guide will save you money, get you a better price, improve a contract, or lead to any particular outcome. Results depend on your situation, the other party, the market and the choices you make. Nothing on this website is a guarantee of savings, income or results.</p>

        <h2>About the figures on this site</h2>
        <p>Each product page names an amount that is typically at stake in that decision, and shows where the figure comes from. Those figures are published averages or ranges from third parties. They describe the scale of the decision. They are not quotes, not predictions, not what you will pay, and not what you will save.</p>
        <p><?= View::e(V::site('figureNote')) ?></p>
        <p>Where published sources disagree, we say so on the page rather than choose a single number. Figures also go out of date. We do our best to keep them current, but you should check any figure that matters to your decision against its original source.</p>

        <h2>Accuracy and currency</h2>
        <p>Laws, prices, fees, contract terms and industry practice change. A guide reflects our understanding at the time it was written, for the United States. We do not promise that every statement is complete, current or correct for your location, and we are not responsible for decisions made in reliance on one.</p>

        <h2>Links to other websites</h2>
        <p>Where a guide or this website points to another organization, that is for information only. We do not control those websites and are not responsible for their content or their accuracy.</p>

        <h2>Questions</h2>
        <p>Email <a href="mailto:<?= View::e($support) ?>"><?= View::e($support) ?></a>.</p>
      </div>
    </div>
  </div>
</section>
