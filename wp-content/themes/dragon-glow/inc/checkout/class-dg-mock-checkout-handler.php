<?php
/**
 * Dragon Glow — Mock Checkout Handler
 *
 * Handles Buy Now for mock (display-only) products when:
 *   - WooCommerce is not active, or
 *   - The product is a mock entry with no shadow WC product.
 *
 * Persists the selected product/quantity/size into a transient cart
 * and redirects to the internal mock checkout page.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_Mock_Checkout_Handler {

	const CART_TRANSIENT_KEY = 'dg_mock_cart';

	/**
	 * Handle a Buy Now action for a mock product.
	 *
	 * @param string $slug     Mock product slug (key in $mock_products_data).
	 * @param int    $quantity Number of items.
	 * @param string $size     Selected size label (e.g. "50ml").
	 * @return array{success: bool, redirect?: string, message?: string}
	 */
	public function handle( string $slug, int $quantity = 1, string $size = '' ): array {
		$mock_repo = DG_Product_Factory::mock();
		$product  = $mock_repo->get_by_slug( $slug );

		if ( ! $product ) {
			return array(
				'success' => false,
				'message' => __( 'Product not found.', 'dragon-glow' ),
			);
		}

		$mock_checkout_url = dg_get_mock_checkout_url();

		if ( empty( $mock_checkout_url ) ) {
			return array(
				'success' => false,
				'message' => __( 'Checkout is not available at this time.', 'dragon-glow' ),
			);
		}

		// Persist the item in our transient "cart".
		$cart_items = $this->load_cart();
		$item_key  = $slug . '|' . $size;

		$cart_items[ $item_key ] = array(
			'slug'     => $slug,
			'name'     => $product->get_name(),
			'price'    => $product->get_price(),
			'formatted_price' => $product->get_price_formatted(),
			'image_url' => $product->get_image_url(),
			'size'     => $size,
			'quantity' => $quantity,
		);

		$this->save_cart( $cart_items );

		$redirect_url = add_query_arg(
			array(
				'dg_mock_checkout' => '1',
				'dg_mock_item'    => rawurlencode( $item_key ),
			),
			$mock_checkout_url
		);

		return array(
			'success' => true,
			'redirect' => $redirect_url,
		);
	}

	/**
	 * Load the transient mock cart.
	 *
	 * @return array
	 */
	public function load_cart(): array {
		$cart = get_transient( self::CART_TRANSIENT_KEY );
		return is_array( $cart ) ? $cart : array();
	}

	/**
	 * Save the transient mock cart.
	 *
	 * @param array $cart
	 * @return bool
	 */
	public function save_cart( array $cart ): bool {
		return set_transient( self::CART_TRANSIENT_KEY, $cart, 7 * DAY_IN_SECONDS );
	}

	/**
	 * Clear the mock cart.
	 *
	 * @return bool
	 */
	public function clear_cart(): bool {
		return delete_transient( self::CART_TRANSIENT_KEY );
	}

	/**
	 * Get the mock checkout page URL.
	 *
	 * @return string
	 */
	public function get_checkout_page_url(): string {
		// Template file: page-templates/template-mock-checkout.php
		// Find a WordPress page that uses this template.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'meta_key'       => '_wp_page_template',
				'meta_value'     => 'page-templates/template-mock-checkout.php',
				'post_status'    => 'publish',
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0] );
		}

		// Fallback: check if the page slug exists.
		$fallback = get_page_by_path( 'mock-checkout' );
		if ( $fallback ) {
			return get_permalink( $fallback );
		}

		// Last resort: site home.
		return home_url( '/' );
	}
}
