<?php
/**
 * A very small template layer. Each public page calls View::render('name', [...]).
 * Templates live in private_html/templates and are never reachable by URL.
 */
class View
{
    public static function render($template, $meta = [])
    {
        V::securityHeaders();

        $meta = array_merge([
            'title'       => 'Veyl',
            'description' => V::site('tagline'),
            'nav'         => '',
            'path'        => '/',
            'noindex'     => false,
            'ogImage'     => '/assets/og/default.jpg',
            'bodyClass'   => '',
        ], $meta);

        $file = V::$root . '/templates/' . preg_replace('/[^a-z0-9_-]/', '', $template) . '.php';
        if (!is_file($file)) {
            V::fail(500, 'That page template is missing.');
        }

        // Templates read $meta directly; $product is passed through for the product page.
        $product = isset($meta['product']) ? $meta['product'] : null;

        ob_start();
        include $file;
        $content = ob_get_clean();

        include V::$root . '/templates/layout.php';
        exit;
    }

    /** Escape for HTML. Short name because templates use it constantly. */
    public static function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * The Veyl logo: the veil mark and the wordmark, drawn as geometry so it needs
     * no font and takes its color from the surrounding text.
     */
    public static function wordmark($height = 20, $label = 'Veyl')
    {
        static $count = 0;
        $uid = ++$count;
        $h = (float) $height;
        $w = round($h * (275.3 / 104), 2);

        return '<svg class="wordmark" viewBox="0 -78 275.3 104" width="' . $w . '" height="' . $h
             . '" role="img" aria-label="' . self::e($label) . '">'
             . "<defs><clipPath id='vl-clip-" . $uid . "'><circle cx='16' cy='16' r='14.6'/></clipPath></defs><g transform='translate(0 -73) scale(2.3125)' fill='none' stroke='currentColor' stroke-width='1.35' stroke-linecap='round' stroke-linejoin='round'><circle cx='16' cy='16' r='14.6'/><g clip-path='url(#vl-clip-" . $uid . ")'><path d='M1 6.2C8 9.2 13 15.2 16 26C19 15.2 24 9.2 31 6.2'/><path d='M1 10.8C7.4 12.7 11.9 17 14.1 22.8'/><path d='M31 10.8C24.6 12.7 20.1 17 17.9 22.8'/><path d='M1 15.3C6 16.4 9.7 18.7 12.1 21.6'/><path d='M31 15.3C26 16.4 22.3 18.7 19.9 21.6'/></g></g><path transform='translate(90 0)' fill='currentColor' d='M5.7 -47.7H0.6000000000000001V-51.2H20.5V-47.7H12.0L27.200000000000003 -6.5H27.6L42.5 -47.7H34.1V-51.2H51.900000000000006V-47.7H46.800000000000004L28.700000000000003 0.0H23.900000000000002Z M79.2 1.2000000000000002Q68.8 1.2000000000000002 62.7 -5.800000000000001Q56.6 -12.8 56.6 -25.6Q56.6 -31.700000000000003 58.25 -36.7Q59.9 -41.7 62.900000000000006 -45.150000000000006Q65.9 -48.6 70.0 -50.5Q74.1 -52.400000000000006 79.0 -52.400000000000006Q83.80000000000001 -52.400000000000006 87.65 -50.60000000000001Q91.5 -48.800000000000004 94.2 -45.650000000000006Q96.9 -42.5 98.35000000000001 -38.05Q99.80000000000001 -33.6 99.80000000000001 -28.400000000000002V-26.700000000000003H63.0V-23.5Q63.0 -13.8 67.7 -8.600000000000001Q72.4 -3.4000000000000004 80.0 -3.4000000000000004Q86.30000000000001 -3.4000000000000004 90.25 -6.2Q94.2 -9.0 96.30000000000001 -13.100000000000001L98.80000000000001 -11.600000000000001Q97.80000000000001 -9.200000000000001 96.10000000000001 -6.9Q94.4 -4.6000000000000005 91.95 -2.8000000000000003Q89.5 -1.0 86.35 0.10000000000000009Q83.2 1.2000000000000002 79.2 1.2000000000000002ZM63.0 -30.1H93.7V-30.700000000000003Q93.7 -34.6 92.80000000000001 -37.900000000000006Q91.9 -41.2 90.05000000000001 -43.650000000000006Q88.2 -46.1 85.45 -47.45Q82.7 -48.800000000000004 79.0 -48.800000000000004Q75.3 -48.800000000000004 72.35 -47.5Q69.4 -46.2 67.30000000000001 -43.800000000000004Q65.2 -41.400000000000006 64.1 -38.10000000000001Q63.0 -34.800000000000004 63.0 -30.8Z M115.4 21.200000000000003Q110.9 21.200000000000003 108.9 19.25Q106.9 17.3 106.9 14.600000000000001Q106.9 12.5 108.0 11.25Q109.10000000000001 10.0 111.2 10.0Q113.0 10.0 114.05000000000001 11.100000000000001Q115.10000000000001 12.200000000000001 115.10000000000001 14.100000000000001Q115.10000000000001 15.5 114.35000000000001 16.4Q113.60000000000001 17.3 112.60000000000001 17.7V17.900000000000002Q113.7 18.3 115.5 18.3Q118.7 18.3 121.25 15.8Q123.80000000000001 13.3 125.9 7.6000000000000005L128.2 1.4000000000000001L109.4 -47.7H104.3V-51.2H124.2V-47.7H115.7L130.9 -6.5H131.3L146.2 -47.7H137.7V-51.2H155.60000000000002V-47.7H150.5L129.2 8.200000000000001Q126.9 14.3 123.55000000000001 17.75Q120.2 21.200000000000003 115.4 21.200000000000003Z M158.5 -3.5H166.29999999999998V-70.0L158.5 -71.0V-73.60000000000001L172.1 -75.8V-3.5H179.89999999999998V0.0H158.5Z'/>"
             . '</svg>';
    }

    /** Nav items, used by the layout for both the desktop bar and the mobile sheet. */
    public static function navItems()
    {
        return [
            'home'     => ['/', 'Home'],
            'about'    => ['/about/', 'About'],
            'products' => ['/products/', 'Products'],
            'faq'      => ['/faq/', 'FAQ'],
        ];
    }

    /** One product card, used on the home page and the products page. */
    public static function productCard($product)
    {
        $url     = '/products/' . $product['slug'] . '/';
        $buyable = V::isBuyable($product);
        $stake   = $product['stake'];
        $badge   = $buyable
            ? '<span class="badge badge--live">Available now</span>'
            : '<span class="badge badge--soon">Coming soon</span>';

        return '<article class="card card--hover pcard reveal">'
             . '<div class="pcard__top">' . $badge . '</div>'
             . '<h3><a href="' . self::e($url) . '">' . self::e($product['name']) . '</a></h3>'
             . '<p class="pcard__line">' . self::e($product['oneLine']) . '</p>'
             . '<p class="pcard__stake"><b>' . self::e($stake['amount']) . '</b> '
             . self::e(isset($stake['lead']) ? $stake['lead'] : 'at stake') . '. ' . self::e($stake['line'])
             . '<span class="pcard__src">' . self::e($stake['source']) . '</span></p>'
             . '<div class="pcard__foot">'
             . '<span class="pcard__price">' . self::e(V::money(V::basePriceCents())) . '</span>'
             . '<a class="btn btn--dark btn--sm" href="' . self::e($url) . '">View guide'
             . '<span class="sr-only"> — ' . self::e($product['name']) . '</span></a>'
             . '</div></article>';
    }

    /**
     * One illustrative preview page. If the product data carries an image path for
     * this section, that image is shown instead of the built-in placeholder layout.
     */
    public static function previewPage($product, $section)
    {
        $data = isset($product['preview'][$section]) ? $product['preview'][$section] : [];
        $images = isset($product['previewImages']) ? $product['previewImages'] : [];

        if (!empty($images[$section])) {
            return '<figure class="page page--image"><span class="page__tab">' . self::e($section) . '</span>'
                 . '<img src="' . self::e($images[$section]) . '" alt="A sample page from the ' . self::e($section)
                 . ' section of the ' . self::e($product['name']) . ' guide." loading="lazy" width="620" height="800">'
                 . '</figure>';
        }

        $html = '<div class="page"><span class="page__tab">' . self::e($section) . '</span>';
        $html .= '<h3>' . self::e(isset($data['heading']) ? $data['heading'] : $section) . '</h3>';

        if ($section === 'Lessons') {
            $html .= '<p>' . self::e(isset($data['body']) ? $data['body'] : '') . '</p>';
            if (!empty($data['callout'])) {
                $html .= '<p class="page__callout">' . self::e($data['callout']) . '</p>';
            }
        } elseif ($section === 'Script') {
            $html .= '<ul class="page__lines">';
            foreach ((isset($data['lines']) ? $data['lines'] : []) as $line) {
                $html .= '<li>' . self::e($line) . '</li>';
            }
            $html .= '</ul>';
        } elseif ($section === 'Forms') {
            $html .= '<div class="page__fields">';
            foreach ((isset($data['fields']) ? $data['fields'] : []) as $field) {
                $html .= '<div class="page__field"><span>' . self::e($field['label']) . '</span><div class="page__input"></div></div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<ul class="page__checks">';
            foreach ((isset($data['items']) ? $data['items'] : []) as $item) {
                $done = !empty($item['done']);
                $html .= '<li class="' . ($done ? 'is-done' : '') . '"><span class="page__box" aria-hidden="true"></span>'
                       . '<span>' . self::e($item['text']) . ($done ? ' <span class="sr-only">(ticked in this example)</span>' : '') . '</span></li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div>';
        return $html;
    }
}
