<?php
// Determine language from GET param or cookie (no session needed)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['nl', 'en'])) {
    $lang = $_GET['lang'];
    setcookie('kiosk_lang', $lang, time() + 86400 * 30, '/');
} elseif (isset($_COOKIE['kiosk_lang']) && in_array($_COOKIE['kiosk_lang'], ['nl', 'en'])) {
    $lang = $_COOKIE['kiosk_lang'];
} else {
    $lang = 'nl';
}

$translations = [
    'nl' => [
        'eat_here'      => 'HIER ETEN',
        'take_away'     => 'MEENEMEN',
        'items_in_cart' => 'artikel(en) in winkelwagen',
        'total'         => 'Totaal:',
        'euro_label'    => 'EURO:',
        'checkout_btn'  => 'Afrekenen',
        'back_to_menu'  => 'Terug naar menu',
        'order_done'    => 'Bestelling voltooid',
        'enjoy'         => 'Geniet ervan!',
        'continue_btn'  => 'Verder',
        'no_products'   => 'Geen producten gevonden in deze categorie.',
        'cart_empty'    => 'Je winkelwagen is leeg.',
        'add_modal'     => 'Toevoegen',
    ],
    'en' => [
        'eat_here'      => 'EAT HERE',
        'take_away'     => 'TAKE AWAY',
        'items_in_cart' => 'item(s) in cart',
        'total'         => 'Total:',
        'euro_label'    => 'EURO:',
        'checkout_btn'  => 'Checkout',
        'back_to_menu'  => 'Back to menu',
        'order_done'    => 'Order completed',
        'enjoy'         => 'Enjoy!',
        'continue_btn'  => 'Continue',
        'no_products'   => 'No products found in this category.',
        'cart_empty'    => 'Your cart is empty.',
        'add_modal'     => 'Add to cart',
    ],
];

function t($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}

// Build a URL with lang param appended/replaced
function withLang($url, $extraParams = []) {
    global $lang;
    // Parse existing query string
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $params);
    $params['lang'] = $lang;
    foreach ($extraParams as $k => $v) $params[$k] = $v;
    $base = $parts['path'] ?? $url;
    return $base . '?' . http_build_query($params);
}
