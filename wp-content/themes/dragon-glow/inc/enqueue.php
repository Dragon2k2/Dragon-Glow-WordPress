<?php
/**
 * Dragon Glow — Asset Enqueue
 * Tất cả scripts và styles đăng ký tập trung ở đây.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function dg_enqueue_assets(): void {

    // Google Fonts
    wp_enqueue_style(
        'dg-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Montserrat:wght@400;500;600;700&family=Bodoni+Moda:ital,wght@1,600&display=swap',
        array(),
        null
    );

    // Material Symbols
    wp_enqueue_style(
        'dg-material-symbols',
        'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap',
        array(),
        null
    );

    // Main theme CSS
    wp_enqueue_style(
        'dg-main',
        DG_URI . '/assets/css/main.css',
        array( 'dg-google-fonts' ),
        DG_VERSION
    );

    // WooCommerce custom CSS (chỉ load khi có WooCommerce)
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_style(
            'dg-woocommerce',
            DG_URI . '/assets/css/woocommerce-custom.css',
            array( 'dg-main' ),
            DG_VERSION
        );
    }

    // Responsive CSS
    wp_enqueue_style(
        'dg-responsive',
        DG_URI . '/assets/css/responsive.css',
        array( 'dg-main' ),
        DG_VERSION
    );

    // Shop page specific styles (luxury / editorial layout)
    if (
        is_page_template( 'page-templates/template-shop.php' ) ||
        ( function_exists( 'is_shop' ) && is_shop() ) ||
        ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
    ) {
        wp_enqueue_style(
            'dg-shop',
            DG_URI . '/assets/css/shop.css',
            array( 'dg-main' ),
            DG_VERSION
        );
    }

    // Single product page specific styles
    if ( function_exists( 'is_product' ) && is_product() ) {
        wp_enqueue_style(
            'dg-product',
            DG_URI . '/assets/css/product.css',
            array( 'dg-main' ),
            DG_VERSION
        );
    }

    // Mock product detail page styles (standalone, non-WooCommerce)
    // Detected by the dg_product query string param (same condition used by setup.php).
    if ( ! empty( $_GET['dg_product'] ) ) {
        wp_enqueue_style(
            'dg-product-mock',
            DG_URI . '/assets/css/product-mock.css',
            array( 'dg-main', 'dg-product' ),
            DG_VERSION
        );
    }

    // Tailwind CSS CDN (load in head for immediate parsing)
    wp_enqueue_script(
        'tailwindcss',
        'https://cdn.tailwindcss.com?plugins=forms',
        array(),
        null,
        false
    );

    // Main JS (scroll reveal, parallax, carousel)
    wp_enqueue_script(
        'dg-main',
        DG_URI . '/assets/js/main.js',
        array(),
        DG_VERSION,
        true
    );

    // Page-specific JS (WooCommerce conditionals)
    if ( class_exists( 'WooCommerce' ) ) {
        if ( is_product() ) {
            wp_enqueue_script( 'dg-product', DG_URI . '/assets/js/product.js', array( 'dg-main' ), DG_VERSION, true );
        }
        if ( is_cart() ) {
            wp_enqueue_script( 'dg-cart', DG_URI . '/assets/js/cart.js', array( 'dg-main' ), DG_VERSION, true );
        }
        if ( is_checkout() ) {
            wp_enqueue_script( 'dg-checkout', DG_URI . '/assets/js/checkout.js', array( 'dg-main' ), DG_VERSION, true );
        }
        if ( is_account_page() ) {
            wp_enqueue_script( 'dg-account', DG_URI . '/assets/js/account.js', array( 'dg-main' ), DG_VERSION, true );
        }
    }
    if ( is_page_template( 'page-templates/template-contact.php' ) ) {
        wp_enqueue_script( 'dg-contact', DG_URI . '/assets/js/contact.js', array( 'dg-main' ), DG_VERSION, true );
    }
    if ( is_page_template( 'page-templates/template-wishlist.php' ) ) {
        wp_enqueue_script( 'dg-wishlist', DG_URI . '/assets/js/wishlist.js', array( 'dg-main' ), DG_VERSION, true );
    }

    // Localize script — truyền data PHP → JS
    wp_localize_script( 'dg-main', 'dgAjax', array(
        'url'   => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'dg_nonce' ),
    ) );

    // Add inline Tailwind config
    $tailwind_config = dg_get_tailwind_config();
    wp_add_inline_script( 'tailwindcss', $tailwind_config, 'after' );
}
add_action( 'wp_enqueue_scripts', 'dg_enqueue_assets' );

/**
 * Get Tailwind config string.
 *
 * @return string
 */
function dg_get_tailwind_config(): string {
    $config = <<<'JS'
tailwind.config = {
  theme: {
    extend: {
      colors: {
        "primary":"#735c00",
        "on-primary":"#ffffff",
        "primary-container":"#d4af37",
        "on-primary-container":"#554300",
        "inverse-primary":"#e9c349",
        "primary-fixed":"#ffe088",
        "primary-fixed-dim":"#e9c349",
        "on-primary-fixed":"#241a00",
        "on-primary-fixed-variant":"#574500",

        "secondary":"#6a5b55",
        "on-secondary":"#ffffff",
        "secondary-container":"#f0dbd3",
        "on-secondary-container":"#6f5f59",
        "secondary-fixed":"#f3ded6",
        "secondary-fixed-dim":"#d6c2bb",
        "on-secondary-fixed":"#241914",
        "on-secondary-fixed-variant":"#52443e",

        "tertiary":"#5d5f5f",
        "on-tertiary":"#ffffff",
        "tertiary-container":"#b2b3b3",
        "on-tertiary-container":"#434546",
        "tertiary-fixed":"#e2e2e2",
        "tertiary-fixed-dim":"#c6c6c7",
        "on-tertiary-fixed":"#1a1c1c",
        "on-tertiary-fixed-variant":"#454747",

        "background":"#fcf9f8",
        "on-background":"#1c1b1b",

        "surface":"#fcf9f8",
        "surface-dim":"#dcd9d9",
        "surface-bright":"#fcf9f8",
        "surface-container-lowest":"#ffffff",
        "surface-container-low":"#f6f3f2",
        "surface-container":"#f0eded",
        "surface-container-high":"#eae7e7",
        "surface-container-highest":"#e5e2e1",
        "on-surface":"#1c1b1b",
        "on-surface-variant":"#4d4635",
        "surface-tint":"#735c00",
        "surface-variant":"#e5e2e1",

        "inverse-surface":"#313030",
        "inverse-on-surface":"#f3f0ef",

        "outline":"#7f7663",
        "outline-variant":"#d0c5af",

        "error":"#ba1a1a",
        "on-error":"#ffffff",
        "error-container":"#ffdad6",
        "on-error-container":"#93000a"
      },
      borderRadius: {
        DEFAULT: "0.125rem",
        lg:      "0.25rem",
        xl:      "0.5rem",
        full:    "0.75rem",
      },
      spacing: {
        "unit":                 "8px",
        "gutter":               "24px",
        "container-max":        "1280px",
        "container-max-width":  "1280px",
        "margin-desktop":       "64px",
        "margin-mobile":        "20px",
        "section-gap":          "120px",
      },
      fontFamily: {
        display:            ['"Playfair Display"', 'serif'],
        headline:           ['"Playfair Display"', 'serif'],
        "display-lg":       ['"Playfair Display"', 'serif'],
        "headline-lg":      ['"Playfair Display"', 'serif'],
        "headline-lg-mobile": ['"Playfair Display"', 'serif'],
        "headline-md":      ['"Playfair Display"', 'serif'],
        "body-lg":          ['"Montserrat"', 'sans-serif'],
        "body-md":          ['"Montserrat"', 'sans-serif'],
        "label-sm":         ['"Montserrat"', 'sans-serif'],
        label:              ['"Montserrat"', 'sans-serif'],
        body:               ['"Montserrat"', 'sans-serif'],
      },
      fontSize: {
        "display-lg":         ["64px", { lineHeight: "1.1", fontWeight: "700", letterSpacing: "-0.02em" }],
        "headline-lg":        ["40px", { lineHeight: "1.2", fontWeight: "600" }],
        "headline-lg-mobile": ["32px", { lineHeight: "1.2", fontWeight: "600" }],
        "headline-md":        ["28px", { lineHeight: "1.3", fontWeight: "500" }],
        "body-lg":            ["18px", { lineHeight: "1.6", fontWeight: "400" }],
        "body-md":            ["16px", { lineHeight: "1.6", fontWeight: "400" }],
        "label-sm":           ["12px", { lineHeight: "1.0", fontWeight: "600", letterSpacing: "0.1em" }],
      }
    }
  }
}
JS;
    return $config;
}

/**
 * Dequeue unnecessary styles.
 *
 * @return void
 */
function dg_dequeue_unnecessary(): void {
    // Remove WooCommerce block styles if not needed
    if ( class_exists( 'WooCommerce' ) && ! is_checkout() && ! is_cart() ) {
        wp_dequeue_style( 'wc-block-style' );
    }
}
add_action( 'wp_enqueue_scripts', 'dg_dequeue_unnecessary', 20 );

/**
 * Preload critical assets.
 *
 * @return void
 */
function dg_preload_assets(): void {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'dg_preload_assets', 1 );
