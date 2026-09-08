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

	$product_id          = absint( $_POST['product_id'] ?? 0 );
	$slug                = sanitize_text_field( $_POST['slug'] ?? '' );
	$size                = sanitize_text_field( $_POST['size'] ?? '' );
	$quantity            = absint( $_POST['quantity'] ?? 1 );
	$variation_id        = absint( $_POST['variation_id'] ?? 0 );
	$redirect_after      = ! empty( $_POST['redirect_after_add'] );

	// Decode variation_attributes JSON if present (used by cart undo to
	// re-add the exact configuration that was removed).
	$variation_attributes = array();
	if ( ! empty( $_POST['variation_attributes'] ) ) {
		$decoded = json_decode( wp_unslash( $_POST['variation_attributes'] ), true );
		if ( is_array( $decoded ) ) {
			$variation_attributes = array_map( 'sanitize_text_field', $decoded );
		}
	}

	$result = dg_add_to_cart_silently( array(
		'product_id'           => $product_id,
		'slug'                 => $slug,
		'size'                 => $size,
		'quantity'             => $quantity,
		'variation_id'         => $variation_id,
		'variation_attributes' => $variation_attributes,
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
 * AJAX: Bulk-remove a list of cart-item keys.
 *
 * `cart_item_keys` is sent as a comma-separated string so it round-trips
 * through FormData (the same convention `dg_wishlist_remove_many` uses
 * for product IDs). Only keys that exist in the current cart are
 * removed — anything stale or spoofed is silently dropped so the
 * caller always receives a coherent, server-truth response.
 *
 * Returns:
 *   - removed: count actually removed (≤ requested).
 *   - count:   current cart contents count after the operation.
 *   - cart_hash: WC cart hash so the client can detect divergence.
 *   - fragments: refreshed order-summary fragment so totals update
 *     without a full page reload.
 */
function dg_ajax_remove_cart_items(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() || ! isset( WC()->cart ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Your bag is currently unavailable. Please try again later.', 'dragon-glow' ) ),
			503
		);
	}

	$raw = isset( $_POST['cart_item_keys'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['cart_item_keys'] ) )
		: '';

	if ( '' === $raw ) {
		wp_send_json_error(
			array( 'message' => __( 'Please choose at least one item to remove.', 'dragon-glow' ) ),
			400
		);
	}

	$requested_keys = array_values(
		array_unique(
			array_filter(
				array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', $raw ) ) ),
				static function ( string $key ): bool {
					return '' !== $key;
				}
			)
		)
	);

	if ( empty( $requested_keys ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Please choose at least one item to remove.', 'dragon-glow' ) ),
			400
		);
	}

	// Only operate on keys that still exist server-side.
	$cart = WC()->cart->get_cart();
	$valid_keys = array_values( array_intersect( $requested_keys, array_keys( $cart ) ) );

	if ( empty( $valid_keys ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Those items are no longer in your bag.', 'dragon-glow' ) ),
			409
		);
	}

	$removed = 0;
	foreach ( $valid_keys as $key ) {
		if ( WC()->cart->remove_cart_item( $key ) ) {
			$removed++;
		}
	}

	if ( $removed <= 0 ) {
		wp_send_json_error(
			array( 'message' => __( 'Could not remove items from your bag.', 'dragon-glow' ) ),
			500
		);
	}

	wp_send_json_success(
		array(
			'removed'    => $removed,
			'requested'  => count( $requested_keys ),
			'count'      => WC()->cart->get_cart_contents_count(),
			'cart_hash'  => WC()->cart->get_cart_hash(),
			'fragments'  => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			'message'    => sprintf(
				/* translators: %d: number of items removed. */
				_n( '%d item removed from your bag.', '%d items removed from your bag.', $removed, 'dragon-glow' ),
				$removed
			),
		)
	);
}
add_action( 'wp_ajax_dg_ajax_remove_cart_items',        'dg_ajax_remove_cart_items' );
add_action( 'wp_ajax_nopriv_dg_ajax_remove_cart_items', 'dg_ajax_remove_cart_items' );

/**
 * AJAX: Empty the entire cart.
 *
 * Returns the same shape as `dg_ajax_remove_cart_items` so the client
 * can share its reconciliation path. Uses `empty_cart()` so WC hooks
 * (`woocommerce_cart_emptied`, etc.) still fire.
 */
function dg_ajax_clear_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() || ! isset( WC()->cart ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Your bag is currently unavailable. Please try again later.', 'dragon-glow' ) ),
			503
		);
	}

	$previous_count = WC()->cart->get_cart_contents_count();
	if ( $previous_count <= 0 ) {
		wp_send_json_success(
			array(
				'removed'   => 0,
				'count'     => 0,
				'cart_hash' => WC()->cart->get_cart_hash(),
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
				'message'   => __( 'Your bag is already empty.', 'dragon-glow' ),
			)
		);
	}

	WC()->cart->empty_cart();

	wp_send_json_success(
		array(
			'removed'   => $previous_count,
			'count'     => 0,
			'cart_hash' => WC()->cart->get_cart_hash(),
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			'message'   => sprintf(
				/* translators: %d: number of items cleared. */
				_n( '%d item cleared from your bag.', '%d items cleared from your bag.', $previous_count, 'dragon-glow' ),
				$previous_count
			),
		)
	);
}
add_action( 'wp_ajax_dg_ajax_clear_cart',        'dg_ajax_clear_cart' );
add_action( 'wp_ajax_nopriv_dg_ajax_clear_cart', 'dg_ajax_clear_cart' );

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
