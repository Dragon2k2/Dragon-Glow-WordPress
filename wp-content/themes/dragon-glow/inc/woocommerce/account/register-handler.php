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
 * Save custom registration fields after WooCommerce creates the user account.
 *
 * Fired by `WC_Form_Handler::process_registration()` right after successful
 * user creation. Saves Dragon Glow custom fields (skin aspiration, newsletter
 * opt-in) that are not part of WooCommerce core registration.
 *
 * @param int   $customer_id New customer user ID.
 * @param array $new_customer_data Customer data from WC (unused).
 * @param string $password_generated Whether WC generated password (unused).
 * @return void
 */
function dg_save_registration_custom_fields( int $customer_id, array $new_customer_data, string $password_generated ): void {
	// Nonce already verified by WooCommerce core handler.
	if ( ! isset( $_POST['register'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by WC core.
		return;
	}

	// Save skin aspiration (optional).
	if ( isset( $_POST['skin_aspiration'] ) ) {
		$aspiration = sanitize_text_field( wp_unslash( $_POST['skin_aspiration'] ) );
		update_user_meta( $customer_id, 'dg_skin_aspiration', $aspiration );
	}

	// Save newsletter opt-in (optional).
	if ( isset( $_POST['newsletter'] ) ) {
		update_user_meta( $customer_id, 'dg_newsletter_opt_in', '1' );
	}
}
add_action( 'woocommerce_created_customer', 'dg_save_registration_custom_fields', 10, 3 );
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
