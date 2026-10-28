<?php
/**
 * One product page. Reached as /products/<slug>/ through the rewrite rule in .htaccess.
 * Everything on the page comes from private_html/data/products.json.
 */
require __DIR__ . '/../api/_boot.php';

$slug    = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', (string) $_GET['slug']) : '';
$product = $slug === '' ? null : V::product($slug);

if (!$product) {
    http_response_code(404);
    View::render('notfound', [
        'title'   => 'Guide not found — Veyl',
        'path'    => '/products/',
        'noindex' => true,
    ]);
}

$siteUrl = V::siteUrl();
$path    = '/products/' . $product['slug'] . '/';
$buyable = V::isBuyable($product);

View::render('product', [
    'title'       => $product['metaTitle'] . ' — Veyl',
    'description' => $product['metaDescription'],
    'nav'         => 'products',
    'path'        => $path,
    'ogImage'     => '/assets/og/' . $product['slug'] . '.jpg',
    'product'     => $product,
    'jsonLd'      => [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product['name'],
        'description' => $product['metaDescription'],
        'sku'         => $product['slug'],
        'brand'       => ['@type' => 'Brand', 'name' => 'Veyl'],
        'offers'      => [
            '@type'         => 'Offer',
            'url'           => $siteUrl . $path,
            'price'         => V::usd(V::basePriceCents()),
            'priceCurrency' => V::pricing('currency'),
            'availability'  => $buyable
                ? 'https://schema.org/InStock'
                : 'https://schema.org/PreOrder',
            'seller'        => ['@type' => 'Organization', 'name' => 'Veyl'],
        ],
    ],
]);
