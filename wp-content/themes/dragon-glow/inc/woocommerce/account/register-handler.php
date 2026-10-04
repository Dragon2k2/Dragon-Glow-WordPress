<?php
/**
 * Dragon Glow — My Account: Registration Form Handler
 *
 * Processes the custom registration form submitted from `/my-account/register/`
 * (and the inline register form on the signed-out auth gate). Validates
 * input, creates the WooCommerce customer account, and saves additional
 * customer meta (skin aspiration, newsletter opt-in).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle registration form submission.
 *
 * Processes the custom registration form from /my-account/register/ endpoint.
 * Validates input, creates WooCommerce customer account, and saves additional
 * customer meta (skin aspiration, newsletter opt-in).
 *
 * @return void
 */
function dg_process_registration(): void {
	// Only process on register endpoint POST with nonce.
	if ( ! isset( $_POST['register'], $_POST['woocommerce-register-nonce'] ) ) {
		return;
	}

	global $wp_query;
	if ( ! is_account_page() || ! isset( $wp_query->query_vars['register'] ) ) {
		return;
	}

	// Verify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ), 'woocommerce-register' ) ) {
		wc_add_notice( __( 'Security verification failed. Please try again.', 'dragon-glow' ), 'error' );
		return;
	}

	// Validate required fields.
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password   = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$first_name = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : '';
	$last_name  = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : '';

	if ( empty( $email ) ) {
		wc_add_notice( __( 'Please provide a valid email address.', 'dragon-glow' ), 'error' );
		return;
	}

	if ( empty( $password ) ) {
		wc_add_notice( __( 'Please enter a password.', 'dragon-glow' ), 'error' );
		return;
	}

	if ( strlen( $password ) < 8 ) {
		wc_add_notice( __( 'Password must be at least 8 characters long.', 'dragon-glow' ), 'error' );
		return;
	}

	if ( empty( $first_name ) || empty( $last_name ) ) {
		wc_add_notice( __( 'Please provide both first and last name.', 'dragon-glow' ), 'error' );
		return;
	}

	// Check terms acceptance (required).
	if ( ! isset( $_POST['terms'] ) ) {
		wc_add_notice( __( 'You must accept the Terms of Service to register.', 'dragon-glow' ), 'error' );
		return;
	}

	// Check if email already exists.
	if ( email_exists( $email ) ) {
		wc_add_notice( __( 'An account with this email address already exists.', 'dragon-glow' ), 'error' );
		return;
	}

	// Create customer account.
	$customer_id = wc_create_new_customer( $email, '', $password );

	if ( is_wp_error( $customer_id ) ) {
		wc_add_notice( $customer_id->get_error_message(), 'error' );
		return;
	}

	// Update customer meta with name.
	update_user_meta( $customer_id, 'first_name', $first_name );
	update_user_meta( $customer_id, 'last_name', $last_name );
	update_user_meta( $customer_id, 'billing_first_name', $first_name );
	update_user_meta( $customer_id, 'billing_last_name', $last_name );

	// Save optional fields.
	if ( isset( $_POST['skin_aspiration'] ) ) {
		$aspiration = sanitize_text_field( wp_unslash( $_POST['skin_aspiration'] ) );
		update_user_meta( $customer_id, 'dg_skin_aspiration', $aspiration );
	}

	if ( isset( $_POST['newsletter'] ) ) {
		update_user_meta( $customer_id, 'dg_newsletter_opt_in', '1' );
	}

	// Log the user in.
	wc_set_customer_auth_cookie( $customer_id );

	// Trigger WooCommerce registration action.
	do_action( 'woocommerce_created_customer', $customer_id, array(), '' );

	// Redirect to the page the user was on before registering (enterprise
	// "intended URL" pattern — same helper used for login + social login),
	// falling back to the My Account dashboard.
	wp_safe_redirect( dg_account_safe_redirect_target() );
	exit;
}
add_action( 'template_redirect', 'dg_process_registration', 5 );
