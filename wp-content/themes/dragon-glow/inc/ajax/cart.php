<?php
/**
 * Dragon Glow — AJAX: Cart
 *
 * Cart operations exposed over AJAX: add, buy-now, remove, update quantity,
 * count, and cart identifier lookups. Mode-aware helpers live in cart-functions.php.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX: Quick add to cart.
 */
function dg_ajax_add_to_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	$product_id       = absint( $_POST['product_id'] ?? 0 );
	$slug             = sanitize_text_field( $_POST['slug'] ?? '' );
	$size             = sanitize_text_field( $_POST['size'] ?? '' );
	$quantity         = absint( $_POST['quantity'] ?? 1 );
	$redirect_after   = ! empty( $_POST['redirect_after_add'] );

	$result = dg_add_to_cart_silently( array(
		'product_id' => $product_id,
		'slug'       => $slug,
		'size'       => $size,
		'quantity'   => $quantity,
	) );

	if ( $result['success'] ) {
		$cart_item_key = '';
		if ( dg_is_woocommerce_active() && WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $key => $item ) {
				if ( (int) $item['product_id'] === $product_id ) {
					$cart_item_key = $key;
					break;
				}
			}
		}

		$payload = array(
			'message'       => __( 'Added to bag!', 'dragon-glow' ),
			'redirect'      => $result['redirect'] ?? dg_get_cart_url(),
			'cart_item_key' => $cart_item_key,
		);

		if ( $redirect_after ) {
			$payload['redirect'] = dg_get_checkout_url();
		}

		wp_send_json_success( $payload );
	} else {
		wp_send_json_error( array( 'message' => $result['message'] ) );
	}
}
add_action( 'wp_ajax_dg_ajax_add_to_cart', 'dg_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_dg_ajax_add_to_cart', 'dg_ajax_add_to_cart' );

/**
 * AJAX: Buy Now.
 */
function dg_ajax_buy_now(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$slug       = sanitize_text_field( $_POST['slug'] ?? '' );
	$size       = sanitize_text_field( $_POST['size'] ?? '' );
	$quantity   = absint( $_POST['quantity'] ?? 1 );

	$result = DG_Checkout_Router::handle( array(
		'product_id' => $product_id,
		'slug'       => $slug,
		'size'       => $size,
		'quantity'   => $quantity,
	) );

	if ( $result['success'] ) {
		wp_send_json_success( $result );
	} else {
		wp_send_json_error( $result );
	}
}
add_action( 'wp_ajax_dg_ajax_buy_now', 'dg_ajax_buy_now' );
add_action( 'wp_ajax_nopriv_dg_ajax_buy_now', 'dg_ajax_buy_now' );

/**
 * AJAX: Remove from cart (WooCommerce).
 */
function dg_ajax_remove_from_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	if ( ! dg_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'dragon-glow' ) ) );
	}

	$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] ?? '' );
	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid cart item.', 'dragon-glow' ) ) );
	}

	$removed = WC()->cart->remove_cart_item( $cart_item_key );
	if ( $removed ) {
		wp_send_json_success( array(
			'message'   => __( 'Item removed from cart.', 'dragon-glow' ),
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
		) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Could not remove item.', 'dragon-glow' ) ) );
	}
}
add_action( 'wp_ajax_dg_ajax_remove_from_cart', 'dg_ajax_remove_from_cart' );

/**
 * AJAX: Update cart quantity (WooCommerce).
 */
function dg_ajax_update_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	if ( ! dg_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'dragon-glow' ) ) );
	}

	$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] ?? '' );
	$quantity      = absint( $_POST['quantity'] ?? 0 );

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid cart item.', 'dragon-glow' ) ) );
	}

	if ( $quantity < 1 ) {
		WC()->cart->remove_cart_item( $cart_item_key );
	} else {
		WC()->cart->set_quantity( $cart_item_key, $quantity );
	}

	wp_send_json_success( array(
		'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
	) );
}
add_action( 'wp_ajax_dg_ajax_update_cart', 'dg_ajax_update_cart' );

/**
 * AJAX: Get current cart count.
 */
function dg_ajax_get_cart_count(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	wp_send_json_success( array( 'count' => dg_get_cart_item_count() ) );
}
add_action( 'wp_ajax_dg_ajax_get_cart_count', 'dg_ajax_get_cart_count' );
add_action( 'wp_ajax_nopriv_dg_ajax_get_cart_count', 'dg_ajax_get_cart_count' );

/**
 * AJAX: Remove product from cart (mode-aware).
 */
function dg_ajax_remove_product_from_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	$product_id = absint( $_POST['product_id'] ?? 0 );
	$slug       = sanitize_text_field( $_POST['slug'] ?? '' );

	$result = dg_remove_from_cart_silently( array(
		'product_id' => $product_id,
		'slug'       => $slug,
	) );

	if ( $result['success'] ) {
		wp_send_json_success( array(
			'message' => __( 'Item removed from bag.', 'dragon-glow' ),
			'count'   => $result['count'] ?? 0,
		) );
	} else {
		wp_send_json_error( array( 'message' => $result['message'] ?? __( 'Could not remove item.', 'dragon-glow' ) ) );
	}
}
add_action( 'wp_ajax_dg_ajax_remove_product_from_cart',        'dg_ajax_remove_product_from_cart' );
add_action( 'wp_ajax_nopriv_dg_ajax_remove_product_from_cart', 'dg_ajax_remove_product_from_cart' );

/**
 * AJAX: Return cart identifiers.
 */
function dg_ajax_get_cart_identifiers(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	wp_send_json_success( dg_get_cart_identifiers() );
}
add_action( 'wp_ajax_dg_ajax_get_cart_identifiers',        'dg_ajax_get_cart_identifiers' );
add_action( 'wp_ajax_nopriv_dg_ajax_get_cart_identifiers', 'dg_ajax_get_cart_identifiers' );

/**
 * AJAX: Return product IDs in cart (DEPRECATED).
 */
function dg_ajax_get_cart_product_ids(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	$ids = dg_get_cart_identifiers();
	wp_send_json_success( array( 'product_ids' => $ids['product_ids'] ) );
}
add_action( 'wp_ajax_dg_ajax_get_cart_product_ids',        'dg_ajax_get_cart_product_ids' );
add_action( 'wp_ajax_nopriv_dg_ajax_get_cart_product_ids', 'dg_ajax_get_cart_product_ids' );
