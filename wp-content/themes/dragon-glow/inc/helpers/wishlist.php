<?php
/**
 * Dragon Glow — Helpers: Wishlist
 *
 * Single source of truth for reading & mutating the per-user wishlist stored
 * in user_meta. All AJAX handlers and the page template go through these
 * helpers so we can change the storage backend (custom table, plugin,
 * external service) in one place.
 *
 * Storage: a single user_meta key (`dg_wishlist`) holding an array<int> of
 * product IDs the user has saved. Anonymous users have no wishlist — the
 * page template redirects guests to the login screen with `?redirect_to=`.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * User-meta key that stores the wishlist array.
 *
 * Exposed so JS/other PHP can read it without duplicating the literal.
 *
 * @return string
 */
function dg_wishlist_meta_key(): string {
	return 'dg_wishlist';
}

/**
 * Read the current user's wishlist as a sanitized array of product IDs.
 *
 * Filters out:
 *  - non-positive IDs (legacy / corrupted rows)
 *  - products that have been permanently deleted
 *
 * Always returns an indexed array (array_values) so JSON encoding is stable
 * and AJAX consumers can rely on the structure.
 *
 * @param int|null $user_id Optional user ID. Defaults to the current user.
 * @return array<int>
 */
function dg_get_wishlist( ?int $user_id = null ): array {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( $user_id <= 0 ) {
		return array();
	}

	$raw = get_user_meta( $user_id, dg_wishlist_meta_key(), true );
	if ( ! is_array( $raw ) ) {
		return array();
	}

	// Coerce to ints, drop zeros/negatives, drop phantom product rows.
	$ids = array_values(
		array_unique(
			array_filter(
				array_map( 'absint', $raw ),
				static function ( int $id ): bool {
					if ( $id <= 0 ) {
						return false;
					}
					// Drop IDs that no longer resolve to a real product. Use
					// get_post_status() so we don't spin up full WC product
					// objects for a count() check.
					$status = get_post_status( $id );
					return ( false !== $status && 'trash' !== $status );
				}
			)
		)
	);

	/**
	 * Filter the resolved wishlist IDs for a user.
	 *
	 * @param array<int> $ids     Sanitized wishlist product IDs.
	 * @param int        $user_id User ID.
	 */
	return (array) apply_filters( 'dg_wishlist_get', $ids, $user_id );
}

/**
 * Persist a wishlist array for a user.
 *
 * @param int        $user_id User ID.
 * @param array<int> $ids     Sanitized array of product IDs.
 * @return void
 */
function dg_set_wishlist( int $user_id, array $ids ): void {
	if ( $user_id <= 0 ) {
		return;
	}
	$ids = array_values(
		array_unique(
			array_filter(
				array_map( 'absint', $ids ),
				static function ( int $id ): bool {
					return $id > 0;
				}
			)
		)
	);

	/**
	 * Fires right before the wishlist array is written to user_meta.
	 *
	 * @param int        $user_id User ID.
	 * @param array<int> $ids     Sanitized wishlist IDs about to be persisted.
	 */
	do_action( 'dg_wishlist_before_save', $user_id, $ids );

	update_user_meta( $user_id, dg_wishlist_meta_key(), $ids );
}

/**
 * Toggle a product in the user's wishlist.
 *
 * Returns the new state so callers don't need to re-read.
 *
 * @param int $user_id    User ID.
 * @param int $product_id Product ID.
 * @return array{added: bool, count: int, ids: array<int>}
 */
function dg_wishlist_toggle( int $user_id, int $product_id ): array {
	if ( $user_id <= 0 || $product_id <= 0 ) {
		return array(
			'added' => false,
			'count' => 0,
			'ids'   => array(),
		);
	}

	$current = dg_get_wishlist( $user_id );

	if ( in_array( $product_id, $current, true ) ) {
		$updated = array_values( array_diff( $current, array( $product_id ) ) );
		$added   = false;
	} else {
		$updated = array_values( array_merge( $current, array( $product_id ) ) );
		$added   = true;
	}

	dg_set_wishlist( $user_id, $updated );

	return array(
		'added' => $added,
		'count' => count( $updated ),
		'ids'   => $updated,
	);
}

/**
 * Remove multiple products from the user's wishlist in one call.
 *
 * Used by the bulk "Remove selected" action and "Clear wishlist".
 *
 * @param int        $user_id    User ID.
 * @param array<int> $product_ids Product IDs to remove.
 * @return array{count: int, ids: array<int>}
 */
function dg_wishlist_remove_many( int $user_id, array $product_ids ): array {
	if ( $user_id <= 0 || empty( $product_ids ) ) {
		return array(
			'count' => count( dg_get_wishlist( $user_id ) ),
			'ids'   => dg_get_wishlist( $user_id ),
		);
	}

	$ids = array_map( 'absint', $product_ids );
	$current = dg_get_wishlist( $user_id );
	$remaining = array_values( array_diff( $current, $ids ) );

	dg_set_wishlist( $user_id, $remaining );

	return array(
		'count' => count( $remaining ),
		'ids'   => $remaining,
	);
}

/**
 * Read the wishlist count for the header badge.
 *
 * Safe to call from anywhere — returns 0 when no user is logged in.
 *
 * @return int
 */
function dg_get_wishlist_count(): int {
	return count( dg_get_wishlist( null ) );
}

/**
 * Check whether the current user has a specific product in their wishlist.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function dg_is_product_in_wishlist( int $product_id ): bool {
	if ( $product_id <= 0 ) {
		return false;
	}
	return in_array( $product_id, dg_get_wishlist( null ), true );
}

/**
 * Render the wishlist-count badge markup for the header icon.
 *
 * Mirrors dg_render_cart_count_badge() so visual parity holds across
 * cart + wishlist. Hidden when count is 0, hidden entirely for guests.
 *
 * @return string
 */
function dg_render_wishlist_count_badge(): string {
	if ( ! is_user_logged_in() ) {
		return '';
	}
	$count = (int) dg_get_wishlist_count();
	$classes = 'dg-wishlist-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center';
	if ( 0 === $count ) {
		$classes .= ' hidden';
	}
	return '<span class="' . esc_attr( $classes ) . '">' . esc_html( $count ) . '</span>';
}
