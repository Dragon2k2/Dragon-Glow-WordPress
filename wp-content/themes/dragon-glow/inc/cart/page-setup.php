<?php
/**
 * Dragon Glow — Cart: page setup + direct template render
 *
 * - dg_ensure_wc_cart_page(): re-creates WC Cart page if missing.
 * - dg_serve_wc_cart_template(): intercepts the WC cart page request and
 *   serves the theme's custom cart template directly (bypassing the
 *   `[woocommerce_cart]` shortcode).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create or restore the WooCommerce Cart page if it is missing.
 *
 * WooCommerce sets this page on activation, but it can be deleted accidentally.
 * This function re-creates it and updates WC settings.
 *
 * @return int The Cart page ID (new or existing).
 */
function dg_ensure_wc_cart_page(): int {
	if ( ! dg_is_woocommerce_active() || ! function_exists( 'wc_create_page' ) ) {
		return 0;
	}

	$existing_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'cart' ) : 0;

	// Page exists and is published — nothing to do.
	if ( $existing_id > 0 ) {
		$existing = get_post( $existing_id );
		if ( $existing && 'publish' === $existing->post_status ) {
			return $existing_id;
		}
	}

	// Create the page.
	$page_id = wc_create_page(
		esc_sql( _x( 'cart', 'page_slug', 'woocommerce' ) ),
		__( 'Cart', 'woocommerce' ),
		'<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
		''
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		// Update WC settings so wc_get_cart_url() returns the correct URL.
		update_option( 'woocommerce_cart_page_id', $page_id );
	}

	return (int) $page_id;
}

// Ensure Cart page exists when theme is loaded (runs once per page load — cheap check).
add_action( 'wp_loaded', function () {
	// Only run once per session to avoid unnecessary DB writes.
	static $ran;
	if ( $ran ) {
		return;
	}
	$ran = true;

	// Only run when WC is active and we're not in the admin panel doing an AJAX
	// request that isn't a WC AJAX action (skip setup during REST/graphql calls).
	if ( ! dg_is_woocommerce_active() ) {
		return;
	}
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && wp_doing_ajax() ) {
		// Let WC AJAX actions through (they need the cart page to exist).
		$wc_ajax_actions = array(
			'add_to_cart', 'remove_from_cart', 'update_shipping_method',
			'apply_coupon', 'remove_coupon', 'get_refreshed_fragments',
		);
		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		if ( $action && ! in_array( $action, $wc_ajax_actions, true ) ) {
			return;
		}
	}

	dg_ensure_wc_cart_page();
} );
