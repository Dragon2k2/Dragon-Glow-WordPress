<?php
/**
 * Dragon Glow — WooCommerce Checkout Handler
 *
 * Handles Buy Now for WooCommerce products.
 * Adds products to the WC cart and returns the checkout URL.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_WooCommerce_Checkout_Handler {

	/**
	 * Attempt to add a WooCommerce product to the cart and return the checkout URL.
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

		return $this->add_to_cart_and_redirect( $product, $quantity, $size );
	}

	/**
	 * Core cart-add + redirect logic.
	 *
	 * Variable products require a size selection; simple products do not.
	 * When a size is provided for a variable product the matching variation is resolved
	 * via dg_wc_find_variation_by_size().  When no size is selected for a variable
	 * product the user is sent back to the product page.
	 *
	 * @param WC_Product $product Product object (simple or variable).
	 * @param int        $quantity
	 * @param string     $size
	 * @return array{success: bool, redirect?: string, message?: string}
	 */
	private function add_to_cart_and_redirect( WC_Product $product, int $quantity, string $size ): array {
		$product_id   = $product->get_id();
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

			$variation_id = dg_wc_find_variation_by_size( $product, $size );

			if ( ! $variation_id ) {
				return array(
					'success'  => false,
					'message'  => __( 'The selected size is not available. Please choose a different size.', 'dragon-glow' ),
					'redirect' => get_permalink( $product_id ),
				);
			}

			$added = WC()->cart->add_to_cart(
				$product_id,
				$quantity,
				$variation_id,
				array(),
				array()
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

}
