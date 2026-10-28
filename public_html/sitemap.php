<?php
/** The sitemap, generated from products.json so it is never out of date. */
require __DIR__ . '/api/_boot.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$site  = V::siteUrl();
$today = gmdate('Y-m-d');

$urls = [
    ['/', '1.0'],
    ['/products/', '0.9'],
    ['/about/', '0.6'],
    ['/faq/', '0.6'],
    ['/terms/', '0.2'],
    ['/disclaimer/', '0.2'],
    ['/privacy/', '0.2'],
];
foreach (V::products() as $product) {
    $urls[] = ['/products/' . $product['slug'] . '/', '0.8'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    echo "  <url>\n";
    echo '    <loc>' . View::e($site . $url[0]) . "</loc>\n";
    echo '    <lastmod>' . $today . "</lastmod>\n";
    echo '    <priority>' . $url[1] . "</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>\n";
