<?php
/**
 * Dragon Glow — AJAX: Product Reviews
 *
 * Handles logged-in customers submitting product reviews (WooCommerce).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX: Submit a product review.
 */
function dg_ajax_submit_review(): void {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Please log in to submit a review.', 'dragon-glow' ) ) );
	}
	if ( ! check_ajax_referer( 'dg_review_nonce', 'dg_review_nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token. Please refresh the page and try again.', 'dragon-glow' ) ) );
	}
	if ( ! dg_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'Reviews are not available at this time.', 'dragon-glow' ) ) );
	}

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$rating     = absint( $_POST['rating'] ?? 0 );
	$title      = sanitize_text_field( $_POST['title'] ?? '' );
	$text       = sanitize_textarea_field( $_POST['text'] ?? '' );

	if ( ! $product_id )                                          { wp_send_json_error( array( 'message' => __( 'Invalid product.', 'dragon-glow' ) ) ); }
	if ( $rating < 1 || $rating > 5 )                            { wp_send_json_error( array( 'message' => __( 'Please select a rating between 1 and 5 stars.', 'dragon-glow' ) ) ); }
	if ( empty( $text ) )                                         { wp_send_json_error( array( 'message' => __( 'Please write your review text.', 'dragon-glow' ) ) ); }

	$product = wc_get_product( $product_id );
	if ( ! $product )                                              { wp_send_json_error( array( 'message' => __( 'Product not found.', 'dragon-glow' ) ) ); }

	$comment_id = wp_insert_comment( array(
		'comment_post_ID'    => $product_id,
		'comment_author'     => wp_get_current_user()->display_name,
		'comment_author_email' => wp_get_current_user()->user_email,
		'comment_content'    => $title ? $title . "\n\n" . $text : $text,
		'comment_type'       => 'review',
		'user_id'            => get_current_user_id(),
		'comment_approved'   => apply_filters( 'woocommerce_review_approval', true ) ? 1 : 0,
	) );

	if ( is_wp_error( $comment_id ) || ! $comment_id ) {
		wp_send_json_error( array( 'message' => __( 'Could not submit your review. Please try again.', 'dragon-glow' ) ) );
	}

	update_comment_meta( $comment_id, 'rating', $rating );
	if ( $title ) {
		update_comment_meta( $comment_id, 'title', $title );
	}

	$approved = wp_approve_comment( $comment_id );
	wp_send_json_success( array(
		'message' => $approved
			? __( 'Thank you! Your review has been submitted.', 'dragon-glow' )
			: __( 'Thank you! Your review has been submitted and is pending approval.', 'dragon-glow' ),
	) );
}
add_action( 'wp_ajax_dg_submit_review', 'dg_ajax_submit_review' );
