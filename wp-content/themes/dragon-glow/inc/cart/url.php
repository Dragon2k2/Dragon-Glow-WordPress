<?php
/**
 * Dragon Glow — Cart: URL helpers
 *
 * Build URLs for cart + checkout pages with safe fallbacks when WC is
 * inactive or the corresponding page hasn't been created yet.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the correct checkout URL for the current system state.
 *
 * @return string
 */
function dg_get_checkout_url(): string {
	if ( dg_is_woocommerce_active() && class_exists( 'WC' ) && ! WC()->cart->is_empty() ) {
		return wc_get_checkout_url();
	}

	return home_url( '/shop/' );
}

/**
 * Get the WooCommerce Cart page URL with safety fallback.
 *
 * @return string
 */
function dg_get_cart_url(): string {
	if ( dg_is_woocommerce_active() ) {
		$page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'cart' ) : 0;
		if ( $page_id > 0 ) {
			$page = get_post( $page_id );
			if ( $page && 'publish' === $page->post_status ) {
				return get_permalink( $page_id );
			}
		}
	}

	return home_url( '/shop/' );
}
