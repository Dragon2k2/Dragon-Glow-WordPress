<?php
/**
 * Dragon Glow — Helpers: WooCommerce
 * WooCommerce availability checks, wishlist meta, product categories,
 * price formatting, and category links.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if WooCommerce is active.
 *
 * Single source of truth for WooCommerce availability checks throughout the theme.
 * Use this instead of repeated class_exists('WooCommerce') checks.
 *
 * @return bool
 */
function dg_is_woocommerce_active(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * Check if product is in wishlist.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function dg_in_wishlist( int $product_id ): bool {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $wishlist = (array) get_user_meta( get_current_user_id(), 'dg_wishlist', true );
    return in_array( $product_id, $wishlist, true );
}

/**
 * Get wishlist product IDs.
 *
 * @return array
 */
function dg_get_wishlist(): array {
    if ( ! is_user_logged_in() ) {
        return array();
    }

    return (array) get_user_meta( get_current_user_id(), 'dg_wishlist', true );
}

/**
 * Get wishlist count.
 *
 * @return int
 */
function dg_get_wishlist_count(): int {
    return count( dg_get_wishlist() );
}

/**
 * Get product categories for filtering.
 *
 * @return array
 */
function dg_get_product_categories(): array {
    $categories = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
    ) );

    if ( is_wp_error( $categories ) ) {
        return array();
    }

    return $categories;
}

/**
 * Format price with currency symbol.
 *
 * @param float $price Price.
 * @return string
 */
function dg_format_price( float $price ): string {
	if ( dg_is_woocommerce_active() ) {
		return wc_price( $price );
	}

	return '$' . number_format( $price, 2 );
}

/**
 * Check if we're on a WooCommerce page.
 *
 * @return bool
 */
function dg_is_woocommerce_page(): bool {
	if ( ! dg_is_woocommerce_active() ) {
		return false;
	}

	return is_shop() || is_product_category() || is_product() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Get primary category for a product.
 *
 * @param int $product_id Product ID.
 * @return object|null
 */
function dg_get_product_primary_category( int $product_id ) {
    $categories = get_the_terms( $product_id, 'product_cat' );

    if ( ! $categories || is_wp_error( $categories ) ) {
        return null;
    }

    // Return first category that's not "Uncategorized"
    foreach ( $categories as $category ) {
        if ( 'uncategorized' !== $category->slug ) {
            return $category;
        }
    }

    return $categories[0];
}

/**
 * Get product category link safely â€” falls back to shop URL if term doesn't exist.
 * Prevents get_term_link() from returning WP_Error (which echoes as HTML in href).
 *
 * @param string $slug Product category slug.
 * @return string URL string (always valid, never WP_Error).
 */
function dg_get_category_link( string $slug ): string {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return (string) $link;
		}
	}
	// Fallback: use shop page URL
	if ( function_exists( 'wc_get_page_id' ) ) {
		return (string) get_permalink( wc_get_page_id( 'shop' ) );
	}
	return '#';
}