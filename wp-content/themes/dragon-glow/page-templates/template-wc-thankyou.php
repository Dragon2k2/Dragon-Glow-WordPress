<?php
/**
 * Template Name: WC Thank You — Dragon Glow
 *
 * Custom template for the WooCommerce order-received ("Thank You") page.
 * Bypasses `index.php`'s `<article>` + `<h1>` shell — the WC override at
 * `woocommerce/checkout/thankyou.php` already renders a full Dragon Glow
 * layout (checkmark, order details, BACS QR, action buttons) and we want
 * it to occupy the entire `<main>` region, not be wrapped in a generic
 * page chrome.
 *
 * Why we resolve the order manually:
 *
 * `template_include` runs after WC has already resolved the page-template
 * for `/checkout/order-received/{id}/`. But WC's `WC_Shortcode_Checkout`
 * only injects `$order` into `wc_get_template()` when the request flows
 * through its checkout shortcode — when we land here via a swapped page
 * template, WC has no idea which order the customer is looking at. So the
 * override's `$args['order']` is empty, the guard in `thankyou.php` falls
 * through to the WC default "Thank you. Your order has been received."
 * stub, and the customer sees a near-empty page.
 *
 * Fix: replicate WC's own resolution logic — pull `order-received` from
 * WP query vars (the int order ID embedded in the URL path), read the
 * `key` query string, look the order up via `wc_get_order()`, and verify
 * the key with `hash_equals()` to match WC's timing-safe check. Pass the
 * resolved order into `wc_get_template()` so the override renders the
 * full Dragon Glow layout. If anything is missing/invalid we pass
 * `false` — the override handles that gracefully.
 *
 * Routing: `template_include` filter in `inc/checkout.php` swaps this
 * template in for any request on the WC order-received endpoint, so the
 * page-template assignment on the checkout page (ID 104) is irrelevant.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// 1. Extract order_id from WP query vars (the {order_id} in the URL path
//    `/checkout/order-received/{order_id}/`) + key from query string.
$dg_order_id  = absint( get_query_var( 'order-received' ) );
$dg_order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( (string) $_GET['key'] ) ) : '';
$dg_order     = false;

// 2. Look the order up + verify the key matches (mirrors WC's
//    WC_Shortcode_Checkout::order_received() logic, including the
//    hash_equals() timing-safe check).
if ( $dg_order_id > 0 ) {
	$dg_order = wc_get_order( $dg_order_id );

	if ( ! $dg_order instanceof WC_Order || ! hash_equals( $dg_order->get_order_key(), $dg_order_key ) ) {
		$dg_order = false;
	}
}

// 3. Hand off to the WC override with the resolved order in $args.
//    The override (woocommerce/checkout/thankyou.php) reads $args['order']
//    and renders the full Dragon Glow layout when valid, or a graceful
//    "Order received" stub when false.
if ( function_exists( 'wc_get_template' ) ) {
	wc_get_template( 'checkout/thankyou.php', array( 'order' => $dg_order ) );
}

get_footer();