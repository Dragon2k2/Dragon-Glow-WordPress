<?php
/**
 * Dragon Glow — Mock Product Repository
 *
 * Reads product data through dg_get_mock_products_data() (defined in
 * inc/mock-products-loader.php and bootstrapped via functions.php).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_Mock_Product_Repository {

	/**
	 * Return all mock products as DG_Product objects.
	 *
	 * @return DG_Product[]
	 */
	public function get_all(): array {
		$raw = dg_get_mock_products_data();
		$products = array();

		foreach ( $raw as $slug => $data ) {
			$products[ $slug ] = $this->to_dg_product( $slug, $data );
		}

		return $products;
	}

	/**
	 * Return a single DG_Product by slug, or null if not found.
	 *
	 * @param string $slug
	 * @return DG_Product|null
	 */
	public function get_by_slug( string $slug ): ?DG_Product {
		$raw = dg_get_mock_products_data();
		$slug = sanitize_key( $slug );

		if ( ! isset( $raw[ $slug ] ) ) {
			return null;
		}

		return $this->to_dg_product( $slug, $raw[ $slug ] );
	}

	/**
	 * Return a random subset of related products, excluding the given slug.
	 *
	 * @param string $exclude_slug
	 * @param int    $limit
	 * @return DG_Product[]
	 */
	public function get_related( string $exclude_slug, int $limit = 4 ): array {
		$all = $this->get_all();
		unset( $all[ $exclude_slug ] );
		$all = array_values( $all );
		shuffle( $all );
		return array_slice( $all, 0, $limit );
	}

	/**
	 * Check whether a slug is a known mock product.
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function exists( string $slug ): bool {
		$raw = dg_get_mock_products_data();
		return isset( $raw[ sanitize_key( $slug ) ] );
	}

	/**
	 * Convert a raw mock data array into a DG_Product value object.
	 *
	 * @param string $slug
	 * @param array  $data
	 * @return DG_Product
	 */
	private function to_dg_product( string $slug, array $data ): DG_Product {
		return new DG_Product(
			array(
				'id'               => $slug,
				'slug'             => $slug,
				'name'             => $data['name'] ?? '',
				'price'            => $this->parse_price( $data['price'] ?? '' ),
				'price_formatted'  => $data['price'] ?? '',
				'short_desc'       => $data['short_desc'] ?? '',
				'description'      => $data['description'] ?? '',
				'category'         => $data['category'] ?? '',
				'category_slug'    => $data['category_slug'] ?? '',
				'image_url'        => $data['img_main'] ?? '',
				'gallery_urls'     => $data['img_gallery'] ?? array(),
				'sizes'            => $data['sizes'] ?? array(),
				'rating'           => (float) ( $data['rating'] ?? 0 ),
				'review_count'     => (int) ( $data['review_count'] ?? 0 ),
				'badge'            => $data['badge'] ?? '',
				'badge_pos'        => $data['badge_pos'] ?? 'left',
				'source'           => 'mock',
				'source_object'    => $data,
			)
		);
	}

	/**
	 * Parse a display price string (e.g. "$95.00") to a float.
	 *
	 * @param string $display_price
	 * @return float
	 */
	private function parse_price( string $display_price ): float {
		$clean = preg_replace( '/[^0-9.]/', '', $display_price );
		return $clean !== '' ? (float) $clean : 0.0;
	}
}
