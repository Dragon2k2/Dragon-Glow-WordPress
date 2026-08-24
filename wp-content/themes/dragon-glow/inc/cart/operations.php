<?php
/**
 * Dragon Glow — Cart: operations (add / remove)
 *
 * Helpers around WC()->cart for adding and removing items. All functions are
 * no-ops when WC is inactive so they can be called from templates safely.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add a WooCommerce product to the cart with full variation support.
 *
 * @param int   $product_id          Numeric WC product ID.
 * @param int   $quantity            Number of items (default 1).
 * @param int   $variation_id        Variation ID (0 for simple products).
 * @param array $variation_attributes Key/value map of attribute names → values.
 * @param array $cart_item_data      Extra cart item metadata.
 * @return string|false Cart item key on success, false on failure.
 */
function dg_wc_add_to_cart( int $product_id, int $quantity = 1, int $variation_id = 0, array $variation_attributes = array(), array $cart_item_data = array() ) {
	if ( ! dg_is_woocommerce_active() ) {
		return false;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return false;
	}

	$product_type = $product->get_type();

	if ( 'variable' === $product_type && $variation_id > 0 ) {
		return WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_attributes, $cart_item_data );
	}

	return WC()->cart->add_to_cart( $product_id, $quantity );
}

/**
 * Add a WooCommerce product to cart without redirect.
 *
 * @param array $args {
 *     @type int $product_id
 *     @type int $quantity
 * }
 * @return array{success: bool, message?: string, redirect?: string}
 */
function dg_add_to_cart_silently( array $args ): array {
	$product_id = absint( $args['product_id'] ?? 0 );
	$quantity   = absint( $args['quantity'] ?? 1 );

	if ( $product_id <= 0 ) {
		return array(
			'success' => false,
			'message' => __( 'Invalid product ID.', 'dragon-glow' ),
		);
	}

	$added = dg_wc_add_to_cart( $product_id, $quantity );
	if ( $added ) {
		return array(
			'success'  => true,
			'redirect' => dg_get_cart_url(),
		);
	}

	return array(
		'success' => false,
		'message' => __( 'Could not add to cart.', 'dragon-glow' ),
	);
}

/**
 * Remove a product from WooCommerce cart.
 *
 * @param array $args {
 *     @type int $product_id
 * }
 * @return array{success: bool, message?: string, count?: int}
 */
function dg_remove_from_cart_silently( array $args ): array {
	$product_id = absint( $args['product_id'] ?? 0 );

	if ( $product_id <= 0 ) {
		return array(
			'success' => false,
			'message' => __( 'Invalid product ID.', 'dragon-glow' ),
		);
	}

	if ( ! dg_is_woocommerce_active() || ! isset( WC()->cart ) ) {
		return array(
			'success' => false,
			'message' => __( 'WooCommerce is not active.', 'dragon-glow' ),
		);
	}

	$removed = false;
	foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
		if ( (int) $item['product_id'] === $product_id ) {
			$removed = WC()->cart->remove_cart_item( $cart_item_key );
			break;
		}
	}

	if ( $removed ) {
		return array(
			'success' => true,
			'count'   => WC()->cart->get_cart_contents_count(),
		);
	}

	return array(
		'success' => false,
		'message' => __( 'Could not remove item.', 'dragon-glow' ),
	);
}
