<?php
/**
 * Dragon Glow — Cart: direct template redirect
 *
 * Serves the theme's custom cart layout (woocommerce/cart/cart.php) directly
 * for the WC cart page, bypassing the `[woocommerce_cart]` shortcode to
 * avoid potential shortcode/session conflicts.
 *
 * Hooked to template_redirect (priority 1) so it runs before any output is
 * sent and before the theme's header is rendered.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', 'dg_serve_wc_cart_template', 1 );

/**
 * Intercept the WooCommerce cart page request and serve the theme's custom
 * cart template directly.
 *
 * @return void
 */
function dg_serve_wc_cart_template(): void {
	if ( ! dg_is_woocommerce_active() ) {
		return;
	}

	$cart_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'cart' ) : 0;
	if ( $cart_page_id <= 0 ) {
		return;
	}

	// Only intercept when we're on the cart page.
	if ( ! is_page( $cart_page_id ) ) {
		return;
	}

	// Ensure cart is loaded.
	wc_load_cart();

	get_header();

	// Serve the theme's custom cart layout directly. This calls
	// woocommerce/cart/cart.php which uses WC()->cart->get_cart(). No shortcode
	// needed — the template is loaded directly.
	wc_get_template( 'cart/cart.php' );

	get_footer();
	exit;
}
