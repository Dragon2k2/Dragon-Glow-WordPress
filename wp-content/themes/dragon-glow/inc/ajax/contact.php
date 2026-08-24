<?php
/**
 * Dragon Glow — AJAX: Contact Form
 *
 * Handles the contact form submission and admin notification email.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle contact form submission.
 */
function dg_handle_contact(): void {
	check_ajax_referer( 'dg_contact_nonce', 'dg_nonce_field' );

	$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
	$last_name  = sanitize_text_field( $_POST['last_name'] ?? '' );
	$email      = sanitize_email( $_POST['email'] ?? '' );
	$subject    = sanitize_text_field( $_POST['subject'] ?? '' );
	$message    = sanitize_textarea_field( $_POST['message'] ?? '' );

	$errors = array();
	if ( empty( $first_name ) ) { $errors[] = __( 'First name is required.', 'dragon-glow' ); }
	if ( empty( $last_name ) )  { $errors[] = __( 'Last name is required.', 'dragon-glow' ); }
	if ( empty( $email ) || ! is_email( $email ) ) { $errors[] = __( 'A valid email is required.', 'dragon-glow' ); }
	if ( empty( $message ) )    { $errors[] = __( 'Please include a message.', 'dragon-glow' ); }

	if ( ! empty( $errors ) ) {
		wp_send_json_error( array( 'message' => implode( ' ', $errors ) ) );
	}

	$full_name    = $first_name . ' ' . $last_name;
	$admin_email  = get_option( 'admin_email' );
	$headers      = array(
		'Content-Type: text/html; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $full_name, $email ),
	);

	$subject_labels = array(
		'orders'    => __( 'Order Inquiry', 'dragon-glow' ),
		'products'  => __( 'Product Consultation', 'dragon-glow' ),
		'wholesale' => __( 'Wholesale & Stockists', 'dragon-glow' ),
		'press'     => __( 'Press & Media', 'dragon-glow' ),
		'other'     => __( 'Other', 'dragon-glow' ),
	);
	$subject_label = $subject_labels[ $subject ] ?? __( 'General Inquiry', 'dragon-glow' );

	$body = sprintf(
		'<p><strong>%s</strong> %s (%s)</p>' .
		'<p><strong>%s</strong> %s</p>' .
		'<p><strong>%s</strong></p>' .
		'<p>%s</p>',
		esc_html__( 'From:', 'dragon-glow' ), esc_html( $full_name ), esc_html( $email ),
		esc_html__( 'Subject:', 'dragon-glow' ), esc_html( $subject_label ),
		esc_html__( 'Message:', 'dragon-glow' ),
		nl2br( esc_html( $message ) )
	);

	$sent = wp_mail(
		$admin_email,
		sprintf( '[Dragon Glow] Contact: %s - %s', $subject_label, $full_name ),
		$body,
		$headers
	);

	if ( $sent ) {
		wp_send_json_success( array( 'message' => __( 'Your message has been sent. We\'ll be in touch within 24 hours!', 'dragon-glow' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'There was an error sending your message. Please try again.', 'dragon-glow' ) ) );
	}
}
add_action( 'wp_ajax_dg_contact_form', 'dg_handle_contact' );
add_action( 'wp_ajax_nopriv_dg_contact_form', 'dg_handle_contact' );
