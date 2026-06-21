<?php
/**
 * Dragon Glow — Cart Functions
 *
 * Shared cart utility functions used by:
 *   - AJAX handlers (dg_ajax_add_to_cart, dg_ajax_buy_now)
 *   - Mock checkout handler
 *   - Template files that need to inspect or mutate cart state
 *
 * This file centralises cart logic that was previously spread across:
 *   - inc/ajax-handlers.php  (inline helper logic in AJAX callbacks)
 *   - inc/checkout/*.php     (transient cart helpers in DG_Mock_Checkout_Handler)
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add a WooCommerce product to the cart with full variation support.
 *
 * @param int         $product_id Numeric WC product ID.
 * @param int         $quantity  Number of items (default 1).
 * @param int         $variation_id Variation ID (0 for simple products).
 * @param array       $variation_attributes Key/value map of attribute names → values.
 * @param array       $cart_item_data Extra cart item metadata.
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
 * Find a WooCommerce variation ID by matching a size attribute value.
 *
 * Searches available variations of a variable product for one whose
 * attributes contain the given size label (case-insensitive match).
 *
 * @param WC_Product_Variable $product Variable product object.
 * @param string              $size    Size label to match (e.g. "50ml").
 * @return int Variation ID, or 0 if not found.
 */
function dg_wc_find_variation_by_size( WC_Product_Variable $product, string $size ): int {
	$variations = $product->get_available_variations();

	foreach ( $variations as $variation ) {
		$variation_obj = wc_get_product( $variation['variation_id'] );
		if ( ! $variation_obj ) {
			continue;
		}

		$attrs = $variation_obj->get_attributes();
		foreach ( $attrs as $attr_value ) {
			if ( strtolower( trim( (string) $attr_value ) ) === strtolower( trim( $size ) ) ) {
				return (int) $variation['variation_id'];
			}
		}
	}

	return 0;
}

/**
 * Build the correct checkout URL for the current system state.
 *
 * Priority:
 *   1. WooCommerce checkout URL (when WC is active and has items in cart).
 *   2. Mock checkout page URL (when a published mock checkout page exists).
 *   3. Shop URL with dg_checkout_unavailable flag (so the UI can show a meaningful
 *      notice instead of silently redirecting the user).
 *
 * @return string
 */
function dg_get_checkout_url(): string {
	if ( dg_is_woocommerce_active() && class_exists( 'WC' ) && ! WC()->cart->is_empty() ) {
		return wc_get_checkout_url();
	}

	return dg_get_mock_checkout_url();
}

/**
 * Check whether the mock checkout page has been set up.
 *
 * @return bool True if a published mock checkout page exists.
 */
function dg_mock_checkout_page_exists(): bool {
	return (bool) dg_find_mock_checkout_page();
}

/**
 * Get the transient mock cart array.
 *
 * @return array
 */
function dg_get_mock_cart(): array {
	return get_transient( DG_Mock_Checkout_Handler::CART_TRANSIENT_KEY ) ?: array();
}

/**
 * Save the transient mock cart array.
 *
 * @param array $cart
 * @return bool
 */
function dg_save_mock_cart( array $cart ): bool {
	return set_transient( DG_Mock_Checkout_Handler::CART_TRANSIENT_KEY, $cart, 7 * DAY_IN_SECONDS );
}

/**
 * Clear the transient mock cart.
 *
 * @return bool
 */
function dg_clear_mock_cart(): bool {
	return delete_transient( DG_Mock_Checkout_Handler::CART_TRANSIENT_KEY );
}

/**
 * Silent add-to-cart: adds a product to cart without ever producing a redirect.
 *
 * Handles all three product modes:
 *   - WooCommerce product (has numeric product_id) → uses dg_wc_add_to_cart().
 *   - Shadow WooCommerce product (has numeric product_id, WC active) → same path.
 *   - Mock product (slug-only, no numeric ID, WC inactive or no shadow product)
 *     → stores the item in the transient mock cart so the user can view it on
 *       the mock checkout page without being redirected.
 *
 * This function intentionally returns NO `redirect` key in the response array,
 * differentiating it from the buy-now flow which goes through DG_Checkout_Router.
 *
 * @param array $args {
 *     @type int    $product_id  Numeric WC product ID (0 if using slug for mock product).
 *     @type string $slug        Mock product slug (used when product_id is 0).
 *     @type string $size        Selected size label (e.g. "50ml").
 *     @type int    $quantity    Number of items (default 1).
 * }
 * @return array{success: bool, message?: string}
 */
function dg_add_to_cart_silently( array $args ): array {
	$product_id = absint( $args['product_id'] ?? 0 );
	$slug       = sanitize_text_field( $args['slug'] ?? '' );
	$size       = sanitize_text_field( $args['size'] ?? '' );
	$quantity   = absint( $args['quantity'] ?? 1 );

	if ( $product_id > 0 ) {
		$added = dg_wc_add_to_cart( $product_id, $quantity );
		if ( $added ) {
			return array( 'success' => true );
		}
		return array(
			'success' => false,
			'message'  => __( 'Could not add to cart.', 'dragon-glow' ),
		);
	}

	if ( empty( $slug ) ) {
		return array(
			'success' => false,
			'message' => __( 'Invalid product.', 'dragon-glow' ),
		);
	}

	$mock_repo = DG_Product_Factory::mock();
	$product  = $mock_repo->get_by_slug( $slug );

	if ( ! $product ) {
		return array(
			'success' => false,
			'message' => __( 'Product not found.', 'dragon-glow' ),
		);
	}

	$cart_items = dg_get_mock_cart();
	$item_key   = $slug . '|' . $size;

	$cart_items[ $item_key ] = array(
		'slug'            => $slug,
		'name'            => $product->get_name(),
		'price'          => $product->get_price(),
		'formatted_price' => $product->get_price_formatted(),
		'image_url'      => $product->get_image_url(),
		'size'           => $size,
		'quantity'       => $quantity,
	);

	dg_save_mock_cart( $cart_items );

	return array( 'success' => true );
}
