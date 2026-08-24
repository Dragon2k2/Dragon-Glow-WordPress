<?php
/**
 * Dragon Glow — Script enqueues.
 *
 * All JS enqueues + localization (global + conditional per page/WooCommerce
 * context). A handful of page-specific styles are enqueued here alongside their
 * scripts, preserving the exact ordering of the original single function.
 * Called by dg_enqueue_assets() after dg_enqueue_styles().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue all theme scripts + localization.
 *
 * @return void
 */
function dg_enqueue_scripts_assets(): void {

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

    // Cart API shared module — provides window.DGCart for all cart AJAX.
    // Depends on dg-main so dgAjax (url/nonce/i18n) is available.
    // Also registered as a dep of dg-quick-add-to-cart, dg-buy-now, dg-wishlist.
    wp_enqueue_script(
        'dg-cart-api',
        DG_URI . '/assets/js/lib/cart-api.js',
        array( 'dg-main' ),
        DG_VERSION,
        true
    );

    // Cart feedback — quick-add button feedback, header cart-count refresh,
    // wishlist toggle. Uses window.DGCart, so depends on dg-cart-api.
    wp_enqueue_script(
        'dg-cart-feedback',
        DG_URI . '/assets/js/lib/cart-feedback.js',
        array( 'dg-cart-api' ),
        DG_VERSION,
        true
    );

    // Newsletter — footer subscribe + dedicated newsletter form.
    // Uses dgAjax (localized on dg-main).
    wp_enqueue_script(
        'dg-newsletter',
        DG_URI . '/assets/js/lib/newsletter.js',
        array( 'dg-main' ),
        DG_VERSION,
        true
    );

    // Page-specific JS (WooCommerce conditionals)
    if ( dg_is_woocommerce_active() ) {
        if ( is_product() ) {
            wp_enqueue_script( 'dg-product', DG_URI . '/assets/js/product.js', array( 'dg-main' ), DG_VERSION, true );
        }
        if ( is_cart() ) {
            wp_enqueue_style(
                'dg-cart',
                DG_URI . '/assets/css/cart.css',
                array( 'dg-main' ),
                DG_VERSION
            );
            wp_enqueue_script( 'dg-cart', DG_URI . '/assets/js/cart.js', array( 'dg-main' ), DG_VERSION, true );
        }
        if ( is_checkout() ) {
            // Force clear WC locale cache to ensure our country locale modifications take effect
            delete_transient( 'wc_countries' );
            delete_transient( 'wc_country_locale' );

            // Enqueue payment methods styling
            wp_enqueue_style(
                'dg-checkout-payment',
                DG_URI . '/assets/css/checkout-payment.css',
                array( 'dg-main' ),
                DG_VERSION
            );

            // Enqueue WooCommerce country select (enables Select2 for State field)
            wp_enqueue_script( 'wc-country-select' );
            wp_enqueue_script( 'dg-checkout', DG_URI . '/assets/js/checkout.js', array( 'dg-main', 'wc-country-select' ), DG_VERSION, true );

            // Enqueue payment methods interactions (Motion API for animations)
            wp_enqueue_script_module( 'dg-checkout-payment', DG_URI . '/assets/js/checkout-payment.js', array(), DG_VERSION );
        }

        /*
         * BACS QR panel is rendered on two pages:
         *   1. Checkout form        → pre-order (QR encodes amount + recipient only)
         *   2. Order Received page  → post-order (QR encodes the real Order ID)
         *
         * Enqueue the assets + localization on both pages. WP dedupes, so calling
         * the same handle from the `is_checkout()` block above is a no-op here.
         */
        if ( is_checkout() || is_order_received_page() ) {
            wp_enqueue_style(
                'dg-checkout-payment',
                DG_URI . '/assets/css/checkout-payment.css',
                array( 'dg-main' ),
                DG_VERSION
            );

            // BACS QR generator — ES module because it imports qrcode + motion from CDN.
            // Loaded on both checkout and order-received pages because the panel
            // renders in both contexts.
            wp_enqueue_script_module(
                'dg-checkout-bacs-qr',
                DG_URI . '/assets/js/checkout-bacs-qr.js',
                array(),
                DG_VERSION
            );

            // Localize country names + i18n strings for the QR panel.
            if ( function_exists( 'WC' ) && WC() && WC()->countries ) {
                wp_localize_script(
                    'dg-checkout-bacs-qr',
                    'dgBacsQr',
                    array(
                        'countries' => WC()->countries->get_countries(),
                        'i18n'      => array(
                            'qrAvailable'   => __( 'QR code is available for US customers.', 'dragon-glow' ),
                            'qrUnavailable' => __( 'QR code is only available for US accounts. Please use the manual bank details below.', 'dragon-glow' ),
                        ),
                    )
                );
            }
        }

        // Thank you / order confirmation page — only on the order-received page.
        if ( is_order_received_page() ) {
            wp_enqueue_style(
                'dg-thankyou',
                DG_URI . '/assets/css/thankyou.css',
                array( 'dg-main' ),
                DG_VERSION
            );
        }

// Loaded whenever WC is active so the account UI scripts ship alongside
		// the page regardless of which endpoint URL was used. Selectors in
		// account.js only bind to `.dg-account*` elements, so the cost on
		// other WC pages is just one extra (cached) script.
		if ( dg_is_woocommerce_active() ) {
			wp_enqueue_script( 'dg-account', DG_URI . '/assets/js/account.js', array( 'dg-main' ), DG_VERSION, true );
		}
        // Shop listing JS — reveal-on-scroll, parallax, filter dropdown (Material),
        // mobile filter sheet, active filter tags, URL-driven filter state.
        // Loaded on template-shop, is_shop(), and product taxonomy pages.
        if ( is_shop() || is_product_taxonomy() ) {
            wp_enqueue_script( 'dg-shop', DG_URI . '/assets/js/shop.js', array( 'dg-main' ), DG_VERSION, true );
        }
    }
    if ( is_page_template( 'page-templates/template-shop.php' ) ) {
        wp_enqueue_script( 'dg-shop', DG_URI . '/assets/js/shop.js', array( 'dg-main' ), DG_VERSION, true );
    }
    if ( is_page_template( 'page-templates/template-contact.php' ) ) {
        wp_enqueue_script( 'dg-contact', DG_URI . '/assets/js/contact.js', array( 'dg-main' ), DG_VERSION, true );
    }
    if ( is_page_template( 'page-templates/template-our-story.php' ) ) {
        wp_enqueue_script( 'dg-our-story', DG_URI . '/assets/js/our-story.js', array( 'dg-main' ), DG_VERSION, true );
    }
    if ( is_page_template( 'page-templates/template-faq.php' ) ) {
        // ES module: faq.js import Motion (motion.dev) trực tiếp từ CDN.
        wp_enqueue_script_module(
            'dg-faq',
            DG_URI . '/assets/js/faq.js',
            array(),
            DG_VERSION
        );
    }
    if ( is_page_template( 'page-templates/template-shipping-returns.php' ) ) {
        wp_enqueue_script_module(
            'dg-shipping-returns',
            DG_URI . '/assets/js/shipping-returns.js',
            array(),
            DG_VERSION
        );
        wp_localize_script( 'dg-main', 'dgReturn', array(
            'i18n' => array(
                'sending'       => __( 'Submitting...', 'dragon-glow' ),
                'submit'        => __( 'Submit request', 'dragon-glow' ),
                'sending_status' => __( 'Submitting your request...', 'dragon-glow' ),
                'invalid_email' => __( 'Please enter a valid email address.', 'dragon-glow' ),
                'reason_required' => __( 'Please select a reason for return.', 'dragon-glow' ),
                'success'      => __( 'Your return request has been submitted.', 'dragon-glow' ),
                'error'        => __( 'Something went wrong. Please try again.', 'dragon-glow' ),
                'network'      => __( 'Network error. Please check your connection and try again.', 'dragon-glow' ),
            ),
        ) );
    }
    if ( is_page_template( 'page-templates/template-help-center.php' ) ) {
        // Geist (Vercel) — scoped chỉ cho trang này, không đổi font theme chung.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-help-center',
            DG_URI . '/assets/css/help-center.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-help-center',
            DG_URI . '/assets/js/help-center.js',
            array(),
            DG_VERSION
        );
    }

    // Our Ingredients page specific styles
    if ( is_page_template( 'page-templates/template-our-ingredients.php' ) ) {
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-our-ingredients',
            DG_URI . '/assets/css/our-ingredients.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-our-ingredients',
            DG_URI . '/assets/js/our-ingredients.js',
            array(),
            DG_VERSION
        );
    }

    // Sustainability page specific styles
    if ( is_page_template( 'page-templates/template-sustainability.php' ) ) {
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-sustainability',
            DG_URI . '/assets/css/sustainability.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-sustainability',
            DG_URI . '/assets/js/sustainability.js',
            array(),
            DG_VERSION
        );
    }

    // Privacy Policy page specific styles
    if ( is_page_template( 'page-templates/template-privacy-policy.php' ) ) {
        // Geist (Vercel) — scoped chỉ cho trang này, không đổi font theme chung.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-privacy-policy',
            DG_URI . '/assets/css/privacy-policy.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-privacy-policy',
            DG_URI . '/assets/js/privacy-policy.js',
            array(),
            DG_VERSION
        );
    }

    // Terms of Service page specific styles
    if ( is_page_template( 'page-templates/template-terms-of-service.php' ) ) {
        // Geist (Vercel) — scoped chỉ cho trang này, không đổi font theme chung.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-terms-of-service',
            DG_URI . '/assets/css/terms-of-service.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-terms-of-service',
            DG_URI . '/assets/js/terms-of-service.js',
            array(),
            DG_VERSION
        );
    }

    // Cookie Policy page specific styles
    if ( is_page_template( 'page-templates/template-cookie-policy.php' ) ) {
        // Geist (Vercel) — scoped chỉ cho trang này, không đổi font theme chung.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-cookie-policy',
            DG_URI . '/assets/css/cookie-policy.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-cookie-policy',
            DG_URI . '/assets/js/cookie-policy.js',
            array(),
            DG_VERSION
        );
    }

    // Careers page specific styles
    if ( is_page_template( 'page-templates/template-careers.php' ) ) {
        // Geist (Vercel) — scoped chỉ cho trang này, không đổi font theme chung.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-careers',
            DG_URI . '/assets/css/careers.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-careers',
            DG_URI . '/assets/js/careers.js',
            array(),
            DG_VERSION
        );

        // Localize i18n cho modal Apply (URL, nonce dùng chung dgAjax).
        wp_localize_script( 'dg-careers', 'dgCareersApply', array(
            'i18n' => array(
                'sending'         => __( 'Sending...', 'dragon-glow' ),
                'send'            => __( 'Send application', 'dragon-glow' ),
                'sending_status'  => __( 'Submitting your application...', 'dragon-glow' ),
                'role_required'   => __( 'Please choose a role before submitting.', 'dragon-glow' ),
                'consent_required' => __( 'Please confirm the privacy policy before submitting.', 'dragon-glow' ),
                'success'         => __( 'Your application has been sent.', 'dragon-glow' ),
                'error'           => __( 'Something went wrong. Please try again.', 'dragon-glow' ),
                'network'         => __( 'Network error. Please check your connection and try again.', 'dragon-glow' ),
            ),
        ) );
    }

    // Accessibility Statement page specific styles
    if ( is_page_template( 'page-templates/template-accessibility-statement.php' ) ) {
        // Geist (Vercel) — scoped chỉ cho trang này, không đổi font theme chung.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-accessibility-statement',
            DG_URI . '/assets/css/accessibility-statement.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-accessibility-statement',
            DG_URI . '/assets/js/accessibility-statement.js',
            array(),
            DG_VERSION
        );
    }

    // Gift Cards page specific styles
    if ( is_page_template( 'page-templates/template-gift-cards.php' ) ) {
        // Geist (Vercel) — không có trên Google Fonts, nạp qua Fontsource CDN.
        wp_enqueue_style(
            'dg-geist',
            'https://cdn.jsdelivr.net/npm/@fontsource-variable/geist@5/index.css',
            array(),
            null
        );
        wp_enqueue_style(
            'dg-gift-cards',
            DG_URI . '/assets/css/gift-cards.css',
            array( 'dg-main', 'dg-geist' ),
            DG_VERSION
        );
        wp_enqueue_script_module(
            'dg-gift-cards',
            DG_URI . '/assets/js/gift-cards.js',
            array(),
            DG_VERSION
        );
    }
    if ( is_page_template( 'page-templates/template-wishlist.php' ) ) {
        wp_enqueue_script( 'dg-wishlist', DG_URI . '/assets/js/wishlist.js', array( 'dg-cart-api' ), DG_VERSION, true );
    }

    // Localize script — truyền data PHP → JS
    wp_localize_script( 'dg-main', 'dgAjax', array(
        'url'     => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'dg_nonce' ),
        'cartUrl' => function_exists('dg_get_cart_url') ? dg_get_cart_url() : home_url( '/cart/' ),
        'i18n'    => array(
            'Processing...'    => __( 'Processing...', 'dragon-glow' ),
            'Buy Now'         => __( 'Buy Now', 'dragon-glow' ),
            'Something went wrong. Please try again.' => __( 'Something went wrong. Please try again.', 'dragon-glow' ),
            'Network error. Please check your connection and try again.' => __( 'Network error. Please check your connection and try again.', 'dragon-glow' ),
            'Network error.'  => __( 'Network error.', 'dragon-glow' ),
            'Added'             => __( 'Added', 'dragon-glow' ),
            'Could not add to bag.' => __( 'Could not add to bag.', 'dragon-glow' ),
        ),
    ) );

    // Quick Add to Cart — loads on any page with .dg-quick-add buttons (Shop grid, etc.)
    wp_enqueue_script(
        'dg-quick-add-to-cart',
        DG_URI . '/assets/js/quick-add-to-cart.js',
        array( 'dg-cart-api' ),
        DG_VERSION,
        true
    );

    // Buy Now handler — loads on any page with Buy Now buttons (product detail, shop).
    wp_enqueue_script(
        'dg-buy-now',
        DG_URI . '/assets/js/buy-now.js',
        array( 'dg-cart-api' ),
        DG_VERSION,
        true
    );

    // Add inline Tailwind config
    $tailwind_config = dg_get_tailwind_config();
    wp_add_inline_script( 'tailwindcss', $tailwind_config, 'after' );
}