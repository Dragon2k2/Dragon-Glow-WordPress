<?php
/**
 * Dragon Glow — Product Factory
 *
 * Single entry point for retrieving a DG_Product from any source.
 * Resolution order:
 *   1. If a numeric product_id is provided  → WooCommerce product (if WC active).
 *   2. If a string slug is provided         → Mock product first, then WC product.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_Product_Factory {

	/** @var DG_Mock_Product_Repository */
	private static $mock_repo;

	/** @var DG_WooCommerce_Product_Repository */
	private static $wc_repo;

	/**
	 * Get the singleton mock repository instance.
	 *
	 * @return DG_Mock_Product_Repository
	 */
	public static function mock(): DG_Mock_Product_Repository {
		if ( ! isset( self::$mock_repo ) ) {
			self::$mock_repo = new DG_Mock_Product_Repository();
		}
		return self::$mock_repo;
	}

	/**
	 * Get the singleton WooCommerce repository instance.
	 *
	 * @return DG_WooCommerce_Product_Repository
	 */
	public static function woocommerce(): DG_WooCommerce_Product_Repository {
		if ( ! isset( self::$wc_repo ) ) {
			self::$wc_repo = new DG_WooCommerce_Product_Repository();
		}
		return self::$wc_repo;
	}

	/**
	 * Retrieve a product by slug.
	 *
	 * Resolution order:
	 *   1. Mock repository (primary for display-only pages).
	 *   2. WooCommerce repository (for real products with matching slug).
	 *
	 * @param string $slug
	 * @return DG_Product|null
	 */
	public static function get_by_slug( string $slug ): ?DG_Product {
		// Always check mock first — mock products are the primary display entities.
		$mock = self::mock()->get_by_slug( $slug );
		if ( $mock ) {
			return $mock;
		}

		// Fall back to WooCommerce if WC is active.
		return self::woocommerce()->get_by_slug( $slug );
	}

	/**
	 * Retrieve a product by numeric ID (WooCommerce only).
	 *
	 * @param int $product_id
	 * @return DG_Product|null
	 */
	public static function get_by_id( int $product_id ): ?DG_Product {
		return self::woocommerce()->get_by_id( $product_id );
	}

	/**
	 * Determine the source of a product identified by slug.
	 *
	 * @param string $slug
	 * @return string 'mock' | 'woocommerce' | 'none'
	 */
	public static function detect_source( string $slug ): string {
		if ( self::mock()->exists( $slug ) ) {
			return 'mock';
		}
		if ( self::woocommerce()->exists( $slug ) ) {
			return 'woocommerce';
		}
		return 'none';
	}
}
