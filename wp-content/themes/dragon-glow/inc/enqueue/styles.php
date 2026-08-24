<?php
/**
 * Dragon Glow — Style enqueues.
 *
 * All CSS enqueues (global + conditional per page/WooCommerce context).
 * Called by dg_enqueue_assets() in inc/enqueue.php before the script enqueues,
 * preserving the original single-function cascade order.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue all theme styles.
 *
 * @return void
 */
function dg_enqueue_styles(): void {

    // style.css — chứa design token :root (--color-*) mà tất cả CSS khác phụ thuộc qua var().
    wp_enqueue_style(
        'dg-style',
        get_stylesheet_uri(),
        array(),
        DG_VERSION
    );

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
        array( 'dg-style', 'dg-google-fonts' ),
        DG_VERSION
    );

    // WooCommerce CSS (chỉ load khi có WooCommerce).
    // Shared styles load site-wide; page-scoped styles load conditionally and
    // depend on 'dg-woocommerce' so they always print after the shared file
    // (preserving the original single-file cascade order).
    if ( dg_is_woocommerce_active() ) {
        // Shared: notices, product loop, breadcrumb, sale flash, form inputs,
        // order details, coupon, pagination — reused across WooCommerce pages.
        wp_enqueue_style(
            'dg-woocommerce',
            DG_URI . '/assets/css/woocommerce.css',
            array( 'dg-main' ),
            DG_VERSION
        );

        // Single product page.
        if ( is_product() ) {
            wp_enqueue_style(
                'dg-woocommerce-single-product',
                DG_URI . '/assets/css/single-product.css',
                array( 'dg-woocommerce' ),
                DG_VERSION
            );
        }

        // Checkout page.
        if ( is_checkout() ) {
            wp_enqueue_style(
                'dg-woocommerce-checkout-page',
                DG_URI . '/assets/css/checkout-page.css',
                array( 'dg-woocommerce' ),
                DG_VERSION
            );
        }

// My Account page.
		// Enqueued whenever WC is active so the stylesheet ships alongside
		// the page template regardless of which endpoint URL was used to
		// reach the account area (`/my-account/`, `/?page_id=N`, custom
		// endpoint slug, etc.). Selectors in account.css are all scoped to
		// `.dg-account*` so the cost of loading it on other WC pages is
		// negligible (no visible style change) and it avoids the failure
		// mode where a stale full-page cache (e.g. W3 Total Cache) drops
		// the conditional <link>.
		if ( dg_is_woocommerce_active() ) {
			wp_enqueue_style(
				'dg-woocommerce-account',
				DG_URI . '/assets/css/account.css',
				array( 'dg-woocommerce' ),
				DG_VERSION
			);
		}
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
        // Geist (Vercel) — cho rituals section titles
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-shop',
            DG_URI . '/assets/css/shop.css',
            array( 'dg-main', 'dg-geist' ),
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

    // Our Story page specific styles
    if ( is_page_template( 'page-templates/template-our-story.php' ) ) {
        wp_enqueue_style(
            'dg-our-story',
            DG_URI . '/assets/css/our-story.css',
            array( 'dg-main' ),
            DG_VERSION
        );
    }

    // Contact page specific styles
    if ( is_page_template( 'page-templates/template-contact.php' ) ) {
        wp_enqueue_style(
            'dg-contact',
            DG_URI . '/assets/css/contact.css',
            array( 'dg-main' ),
            DG_VERSION
        );
    }

    // Shipping & Returns page specific styles
    if ( is_page_template( 'page-templates/template-shipping-returns.php' ) ) {
        // Geist (Vercel) — scoped chỉ cho trang này, không đổi font theme chung.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-shipping-returns',
            DG_URI . '/assets/css/shipping-returns.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
    }

    // FAQ page specific styles
    if ( is_page_template( 'page-templates/template-faq.php' ) ) {
        // Geist (Vercel) — không có trên Google Fonts, nạp qua Fontsource CDN.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-faq',
            DG_URI . '/assets/css/faq.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
    }
}