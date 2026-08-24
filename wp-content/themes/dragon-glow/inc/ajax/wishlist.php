<?php
/**
 * Dragon Glow — AJAX: Wishlist
 *
 * Handles toggling products in the logged-in user's wishlist.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle wishlist toggle.
 */
function dg_handle_wishlist(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array(
			'message'  => __( 'Please login to save items to your wishlist.', 'dragon-glow' ),
			'redirect' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/wp-login.php' ),
		) );
	}

	$product_id = absint( $_POST['product_id'] ?? 0 );
	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid product.', 'dragon-glow' ) ) );
	}

	$user_id  = get_current_user_id();
	$wishlist = (array) get_user_meta( $user_id, 'dg_wishlist', true );

	if ( in_array( $product_id, $wishlist, true ) ) {
		$wishlist = array_diff( $wishlist, array( $product_id ) );
		$added    = false;
		$message  = __( 'Removed from wishlist', 'dragon-glow' );
	} else {
		$wishlist[] = $product_id;
		$added      = true;
		$message    = __( 'Added to wishlist', 'dragon-glow' );
	}

	update_user_meta( $user_id, 'dg_wishlist', array_values( $wishlist ) );

	wp_send_json_success( array(
		'added'   => $added,
		'count'   => count( $wishlist ),
		'message' => $message,
	) );
}
add_action( 'wp_ajax_dg_toggle_wishlist', 'dg_handle_wishlist' );
