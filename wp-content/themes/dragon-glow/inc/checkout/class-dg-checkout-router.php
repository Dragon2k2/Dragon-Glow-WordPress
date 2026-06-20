<?php
/**
 * Dragon Glow — Checkout Router
 *
 * Single entry point for all Buy Now actions (mock and WooCommerce products).
 *
 * Decision tree:
 *   1. Numeric product_id provided  → WooCommerce product
 *      - WC active + real product  → WooCommerce handler (add to cart, redirect WC checkout)
 *      - WC active + no product    → Mock handler
 *      - WC inactive + mock exists → Mock handler
 *      - WC inactive + WC product → Error: product unavailable
 *
 *   2. String slug only           → Try mock first, then WooCommerce
 *      - Mock product             → Mock handler (always)
 *      - WooCommerce product      → WC handler (if WC active) or error
 *      - Unknown slug             → Error: product not found
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_Checkout_Router {

	/**
	 * Handle a Buy Now request.
	 *
	 * @param array $args {
	 *   @type int    $product_id  Numeric WC product ID (optional — slug is preferred).
	 *   @type string $slug        Product slug (optional).
	 *   @type string $size        Selected size label.
	 *   @type int    $quantity    Number of items (default 1).
	 * }
	 * @return array{success: bool, redirect?: string, message?: string}
	 */
	public static function handle( array $args = array() ): array {
		$product_id = absint( $args['product_id'] ?? 0 );
		$slug      = sanitize_text_field( $args['slug'] ?? '' );
		$size      = sanitize_text_field( $args['size'] ?? '' );
		$quantity  = max( 1, absint( $args['quantity'] ?? 1 ) );

		// ── Case 1: Numeric product ID provided ──────────────────────────────
		if ( $product_id > 0 ) {
			return self::handle_by_id( $product_id, $quantity, $size );
		}

		// ── Case 2: Slug-based lookup ─────────────────────────────────────
		if ( empty( $slug ) ) {
			return array(
				'success' => false,
				'message' => __( 'Product not specified.', 'dragon-glow' ),
			);
		}

		return self::handle_by_slug( $slug, $quantity, $size );
	}

	/**
	 * Handle Buy Now by product ID.
	 *
	 * @param int    $product_id
	 * @param int    $quantity
	 * @param string $size
	 * @return array
	 */
	private static function handle_by_id( int $product_id, int $quantity, string $size ): array {
		// Try WooCommerce first when WC is active.
		if ( dg_is_woocommerce_active() ) {
			$wc_handler = new DG_WooCommerce_Checkout_Handler();
			$result = $wc_handler->handle( $product_id, $quantity, $size );

			// If WC returned success or a redirect, return it.
			if ( $result['success'] ) {
				return $result;
			}

			// WC couldn't find the product — fall through to mock.
			if ( empty( $result['redirect'] ) ) {
				// Product not found in WC, try mock by slug lookup.
				$wc_product = wc_get_product( $product_id );
				if ( $wc_product ) {
					$slug = $wc_product->get_slug();
				} else {
					// Truly unknown ID.
					return $result;
				}
			}
		}

		// WooCommerce inactive, or WC product not found — check mock.
		$mock_result = self::try_mock_handler( $slug, $quantity, $size );
		if ( null !== $mock_result ) {
			return $mock_result;
		}

		// Product is a real WC product but WC is inactive.
		if ( dg_is_woocommerce_active() ) {
			$wc_product = wc_get_product( $product_id );
			if ( $wc_product ) {
				return array(
					'success' => false,
					'message' => __( 'This product is temporarily unavailable for purchase. Please try again later.', 'dragon-glow' ),
				);
			}
		}

		return array(
			'success' => false,
			'message' => __( 'Product not found.', 'dragon-glow' ),
		);
	}

	/**
	 * Handle Buy Now by slug.
	 *
	 * @param string $slug
	 * @param int    $quantity
	 * @param string $size
	 * @return array
	 */
	private static function handle_by_slug( string $slug, int $quantity, string $size ): array {
		// Always check mock first — mock is the primary display source.
		$mock_result = self::try_mock_handler( $slug, $quantity, $size );
		if ( null !== $mock_result ) {
			return $mock_result;
		}

		// Not a mock product — check WooCommerce.
		if ( dg_is_woocommerce_active() ) {
			$wc_handler = new DG_WooCommerce_Checkout_Handler();
			// get_page_by_path uses the post_name which matches WC product slug.
			$post = get_page_by_path( $slug, OBJECT, 'product' );

			if ( $post ) {
				return $wc_handler->handle( (int) $post->ID, $quantity, $size );
			}
		}

		// Unrecognised slug.
		$shop_url = dg_is_woocommerce_active()
			? wc_get_page_permalink( 'shop' )
			: home_url( '/shop/' );

		return array(
			'success'       => true,
			'redirect'      => $shop_url,
			'preview_notice' => __( 'This item is a preview and is not yet available for purchase.', 'dragon-glow' ),
		);
	}

	/**
	 * Attempt to dispatch to the mock checkout handler.
	 * Returns null if the slug is not a recognised mock product.
	 *
	 * @param string $slug
	 * @param int    $quantity
	 * @param string $size
	 * @return array|null Null if slug is not mock, otherwise the handler result.
	 */
	private static function try_mock_handler( string $slug, int $quantity, string $size ): ?array {
		if ( ! DG_Product_Factory::mock()->exists( $slug ) ) {
			return null;
		}

		$handler = new DG_Mock_Checkout_Handler();
		return $handler->handle( $slug, $quantity, $size );
	}
}
