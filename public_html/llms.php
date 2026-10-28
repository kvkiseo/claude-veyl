<?php
/** A plain-text summary for AI assistants, generated from products.json. */
require __DIR__ . '/api/_boot.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$site  = V::siteUrl();
$price = V::money(V::basePriceCents());

echo "# Veyl\n\n";
echo "> Veyl publishes fillable PDF decision guides. Each guide covers one large, costly decision\n";
echo "> and contains four sections: Lessons, Script, Forms and Checklist. Every guide is $price.\n";
echo "> Guides are educational only and are not legal, financial, or medical advice.\n";
echo "> All sales are final: a guide is a digital product delivered immediately, so there are no refunds.\n";
echo "> Guides are written with United States practice in mind.\n\n";

echo "## Guides\n";
foreach (V::products() as $p) {
    $status = V::isBuyable($p) ? 'available now' : 'coming soon';
    echo '- [' . $p['name'] . '](' . $site . '/products/' . $p['slug'] . '/): '
       . $p['oneLine'] . ' ' . $price . ', ' . $status . ".\n";
}

echo "\n## Pages\n";
echo "- [About](" . $site . "/about/): what Veyl is, who it is for, and what it is not.\n";
echo "- [All guides](" . $site . "/products/)\n";
echo "- [FAQ](" . $site . "/faq/)\n";
echo "- [Terms and No-Refund Policy](" . $site . "/terms/)\n";
echo "- [Disclaimer](" . $site . "/disclaimer/)\n";
echo "- [Privacy Policy](" . $site . "/privacy/)\n";

echo "\n## Contact\n";
echo "- Email: " . V::supportEmail() . "\n";

echo "\n## About the figures on this site\n";
echo V::site('figureNote') . "\n";
echo "Each guide names the amount typically at stake in its decision and shows the source.\n";
echo "Those figures are not savings, predictions or promises.\n";
