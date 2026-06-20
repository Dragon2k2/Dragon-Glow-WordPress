<?php
/**
 * Dragon Glow — Mock Product Shadow Sync
 *
 * Creates and retrieves hidden WooCommerce products that back the display-only
 * mock catalog entries in inc/mock-products-data.php.  The mock array remains the
 * single source of truth for the shop grid and product-detail page.  This file
 * only exists to give the cart/checkout a real purchasable entity to work with.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the real WooCommerce product ID backing a mock catalog entry, creating it
 * on first use (idempotent — safe to call repeatedly for the same slug).
 *
 * The shadow product is a simple WC_Product with catalog_visibility = 'hidden' so
 * it never appears in the public shop archive or search results.
 *
 * @param string $slug Mock product slug (array key in $mock_products_data).
 * @return int|null   Real WooCommerce product ID, or null if the slug is not
 *                    a recognized mock product.
 */
function dg_get_or_create_mock_shadow_product( string $slug ): ?int {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return null;
	}

	$slug = sanitize_key( $slug );

	// 1. Look for an existing shadow product via our custom meta key.
	// This avoids relying on post_name matching, which can collide.
	$existing = new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'post_status'    => 'publish',
		'meta_query'     => array(
			array(
				'key'   => '_dg_mock_slug',
				'value' => $slug,
			),
		),
		'fields' => 'ids',
	) );

	if ( $existing->have_posts() ) {
		return (int) $existing->posts[0];
	}

	// 2. Load the mock data and verify this slug is a known entry.
	// The same require_once pattern already used by template-mock-product.php.
	$mock_file = DG_DIR . '/inc/mock-products-data.php';
	if ( ! file_exists( $mock_file ) ) {
		return null;
	}
	require_once $mock_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.NotUsingAbsolutePath

	if ( ! isset( $mock_products_data[ $slug ] ) ) {
		return null;
	}

	$p = $mock_products_data[ $slug ];

	// 3. Parse the numeric price from the display string (e.g. "$95.00" → 95.00).
	$price_raw = preg_replace( '/[^0-9.]/', '', $p['price'] ?? '' );
	$price     = $price_raw !== '' ? (float) $price_raw : 0.0;

	// 4. Create the hidden shadow product.
	$product = new WC_Product_Simple();
	$product->set_name( $p['name'] );
	$product->set_status( 'publish' );
	$product->set_price( $price );
	$product->set_regular_price( (string) $price );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_virtual( true );
	$product->set_downloadable( false );
	$product->set_reviews_allowed( false );

	// Store the source slug so we can find this product again on the next call.
	$product->add_meta_data( '_dg_mock_slug', $slug, true );

	$product_id = $product->save();

	return $product_id ? (int) $product_id : null;
}
