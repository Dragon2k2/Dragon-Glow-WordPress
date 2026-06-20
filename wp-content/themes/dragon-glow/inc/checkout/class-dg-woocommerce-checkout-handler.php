<?php
/**
 * Dragon Glow — WooCommerce Checkout Handler
 *
 * Handles Buy Now for real WooCommerce products.
 * Adds products to the WC cart and returns the checkout URL.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_WooCommerce_Checkout_Handler {

	/**
	 * Attempt to add a WooCommerce product to the cart and return the redirect URL.
	 *
	 * @param int         $product_id Numeric WC product ID.
	 * @param int         $quantity  Number of items.
	 * @param string      $size      Selected size/variation value (optional).
	 * @return array{success: bool, redirect?: string, message?: string}
	 */
	public function handle( int $product_id, int $quantity = 1, string $size = '' ): array {
		if ( ! dg_is_woocommerce_active() ) {
			return array(
				'success'  => false,
				'message'  => __( 'WooCommerce is not active.', 'dragon-glow' ),
			);
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return array(
				'success' => false,
				'message' => __( 'Product not found.', 'dragon-glow' ),
			);
		}

		$product_type = $product->get_type();

		// Variable products require a size selection.
		if ( 'variable' === $product_type ) {
			if ( empty( $size ) ) {
				return array(
					'success'  => false,
					'message'  => __( 'Please select a size before purchasing.', 'dragon-glow' ),
					'redirect' => get_permalink( $product_id ),
				);
			}

			$variation_id = $this->find_matching_variation( $product, $size );

			if ( ! $variation_id ) {
				return array(
					'success'  => false,
					'message'  => __( 'The selected size is not available. Please choose a different size.', 'dragon-glow' ),
					'redirect' => get_permalink( $product_id ),
				);
			}

			$cart_item_data = array();
			$added = WC()->cart->add_to_cart(
				$product_id,
				$quantity,
				$variation_id,
				array(),
				$cart_item_data
			);
		} else {
			$added = WC()->cart->add_to_cart( $product_id, $quantity );
		}

		if ( $added ) {
			return array(
				'success'  => true,
				'redirect' => wc_get_checkout_url(),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'Could not add to cart. Please try again.', 'dragon-glow' ),
		);
	}

	/**
	 * Find a variation ID by matching a size attribute value.
	 *
	 * @param WC_Product_Variable $product
	 * @param string              $size
	 * @return int                Variation ID, or 0 if not found.
	 */
	private function find_matching_variation( WC_Product_Variable $product, string $size ): int {
		$variations = $product->get_available_variations();

		foreach ( $variations as $variation ) {
			$variation_obj = wc_get_product( $variation['variation_id'] );
			if ( ! $variation_obj ) {
				continue;
			}

			$attrs = $variation_obj->get_attributes();
			foreach ( $attrs as $attr_value ) {
				$attr_value_clean = strtolower( trim( (string) $attr_value ) );
				$size_clean      = strtolower( trim( $size ) );
				if ( $attr_value_clean === $size_clean ) {
					return (int) $variation['variation_id'];
				}
			}
		}

		return 0;
	}
}
