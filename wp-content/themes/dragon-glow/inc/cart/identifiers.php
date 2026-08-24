<?php
/**
 * Dragon Glow — Cart: identifiers + variation finder + badge render
 *
 * Read-only helpers that surface cart state (counts, product IDs) and
 * presentation helper for the header cart-count badge.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

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
 * Get the total item count from WooCommerce cart.
 *
 * @return int
 */
function dg_get_cart_item_count(): int {
	if ( dg_is_woocommerce_active() && isset( WC()->cart ) ) {
		return WC()->cart->get_cart_contents_count();
	}
	return 0;
}

/**
 * Render the cart-count badge markup.
 *
 * @param int $count Item count.
 * @return string
 */
function dg_render_cart_count_badge( int $count ): string {
	$classes = 'dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center';
	if ( 0 === $count ) {
		$classes .= ' hidden';
	}
	return '<span class="' . esc_attr( $classes ) . '">' . esc_html( $count ) . '</span>';
}

/**
 * Get product IDs currently in the WooCommerce cart.
 *
 * @return array{product_ids: int[]}
 */
function dg_get_cart_identifiers(): array {
	$product_ids = array();

	if ( dg_is_woocommerce_active() && isset( WC()->cart ) && WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $item ) {
			$product_ids[] = (int) $item['product_id'];
		}
		$product_ids = array_values( array_unique( $product_ids ) );
	}

	return array(
		'product_ids' => $product_ids,
	);
}
