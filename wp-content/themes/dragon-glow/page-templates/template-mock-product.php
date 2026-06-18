<?php
/**
 * Dragon Glow — Mock Product Detail Page (Wrapper)
 *
 * Standalone template — NOT a WordPress Page Template (no `Template Name:` header).
 * Included directly by dg_mock_product_template_redirect() in inc/setup.php.
 *
 * URL pattern: /shop/?dg_product={slug}  (query-string based, no rewrite needed)
 * Slug keys must match sanitize_title( $p['name'] ) values used in template-shop.php.
 *
 * All product data and helpers are loaded from the shared inc/mock-products-data.php.
 * The actual markup is delegated to template-parts/shop/product-detail.php to avoid
 * code duplication.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// ── Load shared data & helpers (single source of truth) ─────────────────
require_once get_template_directory() . '/inc/mock-products-data.php';

// ── Resolve current product from query string ──
// Read directly from $_GET — works without rewrite rules, no flush needed.
$current_slug = isset( $_GET['dg_product'] ) ? sanitize_title( wp_unslash( $_GET['dg_product'] ) ) : '';
$p            = isset( $mock_products_data[ $current_slug ] ) ? $mock_products_data[ $current_slug ] : null;

// 404 if slug not found.
if ( ! $p ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	get_template_part( '404' );
	exit;
}

// ── Related products: first 4 others from data array ──
$related = array();
foreach ( $mock_products_data as $slug => $prod ) {
	if ( $slug !== $current_slug ) {
		$related[ $slug ] = $prod;
		if ( count( $related ) >= 4 ) {
			break;
		}
	}
}

// ── Detail shots (passed to product-detail partial; helper avoids duplication) ──
$detail_shots = dg_mock_detail_shots( $current_slug );

get_header();

// ── Render reusable product detail partial via WordPress $args parameter ──
// Variables are passed via $args (not local scope — get_template_part() runs in isolation).
get_template_part(
	'template-parts/shop/product-detail',
	null,
	array(
		'current_slug'  => $current_slug,
		'p'             => $p,
		'related'       => $related,
		'detail_shots'  => $detail_shots,
	)
);

get_footer();
