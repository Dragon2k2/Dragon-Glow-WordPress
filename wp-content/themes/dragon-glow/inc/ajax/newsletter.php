<?php
/**
 * Dragon Glow — AJAX: Newsletter
 *
 * Newsletter subscription handler.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle newsletter subscription.
 */
function dg_handle_newsletter(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	$email = sanitize_email( $_POST['email'] ?? '' );
	if ( ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'dragon-glow' ) ) );
	}

	$subscribers = get_option( 'dg_newsletter_subscribers', array() );
	if ( in_array( $email, $subscribers, true ) ) {
		wp_send_json_success( array( 'message' => __( 'You are already subscribed!', 'dragon-glow' ) ) );
	}

	$subscribers[] = $email;
	update_option( 'dg_newsletter_subscribers', $subscribers );

	wp_send_json_success( array(
		'message' => __( 'Thank you for joining the ritual! Check your inbox for a welcome gift.', 'dragon-glow' ),
	) );
}
add_action( 'wp_ajax_dg_newsletter', 'dg_handle_newsletter' );
add_action( 'wp_ajax_nopriv_dg_newsletter', 'dg_handle_newsletter' );
