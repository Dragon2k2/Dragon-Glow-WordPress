<?php
/**
 * Dragon Glow — My Account: Registration Hooks
 *
 * Extends WooCommerce registration with custom meta fields (skin aspiration,
 * newsletter opt-in) and "return to previous page" redirect pattern.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Save the customer's name after WooCommerce creates the user account.
 *
 * `WC_Form_Handler::process_registration()` only reads `email`/`username`/
 * `password` from POST — it never looks at `billing_first_name` or
 * `billing_last_name`, even though the Dragon Glow register form
 * (register.php) collects them. Left unhandled, new accounts end up with no
 * WP user `first_name`/`last_name` and no `billing_first_name`/
 * `billing_last_name` meta, which breaks:
 * - the "Name" column on WooCommerce Analytics → Customers (reads from the
 *   `wc_customer_lookup` table, populated from billing name / WP user name)
 * - the "Account snapshot" name field on the My Account dashboard
 *   (dashboard.php reads `billing_first_name`/`billing_last_name` meta).
 *
 * Fired by `WC_Form_Handler::process_registration()` right after successful
 * user creation.
 *
 * @param int    $customer_id New customer user ID.
 * @param array  $new_customer_data Customer data from WC (unused).
 * @param string $password_generated Whether WC generated password (unused).
 * @return void
 */
function dg_save_registration_custom_fields( int $customer_id, array $new_customer_data, string $password_generated ): void {
	// Nonce already verified by WooCommerce core handler.
	if ( ! isset( $_POST['register'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by WC core.
		return;
	}

	// Save newsletter opt-in (optional).
	if ( isset( $_POST['newsletter'] ) ) {
		update_user_meta( $customer_id, 'dg_newsletter_opt_in', '1' );
	}

	// Save the customer's name (required fields on the register form).
	$first_name = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by WC core.
	$last_name  = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by WC core.

	if ( '' === $first_name && '' === $last_name ) {
		return;
	}

	// Billing meta — read by dashboard.php, checkout, and WC order billing.
	if ( '' !== $first_name ) {
		update_user_meta( $customer_id, 'billing_first_name', $first_name );
	}
	if ( '' !== $last_name ) {
		update_user_meta( $customer_id, 'billing_last_name', $last_name );
	}

	// WP user name — read by WC Analytics' Customer Lookup table ("Name"
	// column) and anywhere core WP displays the user's display name.
	wp_update_user(
		array(
			'ID'           => $customer_id,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => trim( $first_name . ' ' . $last_name ),
		)
	);
}
add_action( 'woocommerce_created_customer', 'dg_save_registration_custom_fields', 10, 3 );

/**
 * Validate password strength during registration (server-side security layer).
 *
 * Enterprise-grade validation (OWASP/NIST aligned):
 * - Minimum 12 characters
 * - Must contain: lowercase, uppercase, digit, special character
 * - Block registration if requirements not met
 *
 * Fired by WooCommerce before user creation. If validation fails, throws
 * WP_Error that WooCommerce displays as a notice.
 *
 * @param WP_Error $errors Validation errors object.
 * @param string   $username Username (unused).
 * @param string   $email Email (unused).
 * @return WP_Error
 */
function dg_validate_registration_password_strength( WP_Error $errors, string $username, string $email ): WP_Error {
	// Only run on registration form submission.
	if ( ! isset( $_POST['register'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by WC core.
		return $errors;
	}

	// Nonce already verified by WooCommerce core at this point.
	// Get password from POST data.
	$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- password validation, not sanitized to preserve special chars; nonce verified by WC.

	// Check minimum length (12 characters).
	if ( strlen( $password ) < 12 ) {
		$errors->add(
			'password_too_short',
			__( '<strong>Password Error:</strong> Password must be at least 12 characters long.', 'dragon-glow' )
		);
		return $errors;
	}

	// Check for required character types.
	$has_lowercase = preg_match( '/[a-z]/', $password );
	$has_uppercase = preg_match( '/[A-Z]/', $password );
	$has_digit     = preg_match( '/[0-9]/', $password );
	$has_special   = preg_match( '/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password );

	$missing_types = array();

	if ( ! $has_lowercase ) {
		$missing_types[] = __( 'lowercase letter (a-z)', 'dragon-glow' );
	}
	if ( ! $has_uppercase ) {
		$missing_types[] = __( 'uppercase letter (A-Z)', 'dragon-glow' );
	}
	if ( ! $has_digit ) {
		$missing_types[] = __( 'number (0-9)', 'dragon-glow' );
	}
	if ( ! $has_special ) {
		$missing_types[] = __( 'special character (!@#$%...)', 'dragon-glow' );
	}

	if ( ! empty( $missing_types ) ) {
		$errors->add(
			'password_missing_types',
			sprintf(
				/* translators: %s: comma-separated list of missing character types */
				__( '<strong>Password Error:</strong> Password must contain: %s.', 'dragon-glow' ),
				implode( ', ', $missing_types )
			)
		);
	}

	return $errors;
}
add_filter( 'woocommerce_registration_errors', 'dg_validate_registration_password_strength', 10, 3 );

/**
 * Redirect to the page the user was on before registering, instead of always
 * landing on the My Account dashboard.
 *
 * `woocommerce_registration_redirect` is WC's own extension point for this
 * exact purpose — fired by `WC_Form_Handler::process_registration()` right
 * after a successful user creation. We ignore WC's passed-in `$redirect`
 * (always the My Account URL) and resolve the real target from `redirect_to`.
 *
 * Mirrors `dg_login_redirect_to_previous_page()` in routing.php.
 *
 * @param string $redirect Default redirect URL (My Account page).
 * @return string
 */
function dg_registration_redirect_to_previous_page( string $redirect ): string {
	return dg_account_safe_redirect_target();
}
add_filter( 'woocommerce_registration_redirect', 'dg_registration_redirect_to_previous_page', 10, 1 );
