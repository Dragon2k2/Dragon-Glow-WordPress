<?php
/**
 * Dragon Glow — Social Login: User Mapping
 *
 * Finds or creates the WordPress user that corresponds to a verified
 * social-provider identity, and performs the WP login. Shared by both
 * google.php and apple.php after each has independently verified its
 * provider's ID token signature.
 *
 * Mapping strategy:
 *   1. Look up by `_dg_social_{provider}_id` usermeta (returning user).
 *   2. Fall back to matching WP user by email (first-time social sign-in
 *      on an account originally created via the normal registration form)
 *      — the provider id is then backfilled onto that user.
 *   3. Otherwise create a new customer account (role "customer", random
 *      password — the user never needs it since they sign in via OAuth).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Find or create a WP user for a verified social identity, then return its ID.
 *
 * @param string $provider    Provider slug ('google' or 'apple').
 * @param string $provider_id Stable subject identifier from the provider's token ('sub' claim).
 * @param string $email       Verified email address from the provider's token.
 * @param string $first_name  Optional first name (Google only; Apple rarely sends this).
 * @param string $last_name   Optional last name.
 * @return int|WP_Error WP user ID on success, WP_Error on failure.
 */
function dg_social_login_find_or_create_user( string $provider, string $provider_id, string $email, string $first_name = '', string $last_name = '' ) {
	if ( '' === $provider_id || ! is_email( $email ) ) {
		return new WP_Error( 'dg_social_login_invalid_identity', __( 'The identity provider did not return a valid account.', 'dragon-glow' ) );
	}

	$meta_key = '_dg_social_' . $provider . '_id';

	// 1) Returning user — exact provider id match.
	$existing_users = get_users(
		array(
			'meta_key'   => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => $provider_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'number'     => 1,
			'fields'     => 'ID',
		)
	);
	if ( ! empty( $existing_users ) ) {
		return (int) $existing_users[0];
	}

	// 2) Existing account with the same email — link provider id onto it.
	$user = get_user_by( 'email', $email );
	if ( $user instanceof WP_User ) {
		update_user_meta( $user->ID, $meta_key, $provider_id );
		return (int) $user->ID;
	}

	// 3) First time anywhere on this site — create a new customer account.
	$username = dg_social_login_generate_username( $email );
	$user_id  = wp_insert_user(
		array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => wp_generate_password( 32, true, true ),
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => 'customer',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	update_user_meta( $user_id, $meta_key, $provider_id );

	/**
	 * Fires after a new WP user is created via social sign-in.
	 *
	 * @param int    $user_id  Newly created user ID.
	 * @param string $provider Provider slug.
	 */
	do_action( 'dg_social_login_user_created', $user_id, $provider );

	return (int) $user_id;
}

/**
 * Generate a unique, sanitized `user_login` from an email's local part.
 *
 * @param string $email Email address.
 * @return string
 */
function dg_social_login_generate_username( string $email ): string {
	$base = sanitize_user( current( explode( '@', $email ) ), true );
	if ( '' === $base ) {
		$base = 'patron';
	}

	$username = $base;
	$suffix   = 1;
	while ( username_exists( $username ) ) {
		$username = $base . $suffix;
		++$suffix;
	}

	return $username;
}

/**
 * Log a WP user in (sets auth cookie) and redirect to My Account.
 *
 * @param int $user_id WP user ID.
 * @return void (redirects then exits).
 */
function dg_social_login_authenticate_and_redirect( int $user_id ): void {
	wp_clear_auth_cookie();
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	$user = get_userdata( $user_id );
	if ( $user instanceof WP_User ) {
		do_action( 'wp_login', $user->user_login, $user );
	}

	wp_safe_redirect( dg_account_endpoint_url( '' ) );
	exit;
}

/**
 * Redirect back to the signed-out auth gate with an error notice.
 *
 * Uses WC's own notice session store (`wc_add_notice`) so the message
 * renders through the existing `wc_print_notices()` call already present
 * in signed-out.php — no new UI needed.
 *
 * @param string $message Human-readable error message (already translated).
 * @return void (redirects then exits).
 */
function dg_social_login_fail( string $message ): void {
	if ( function_exists( 'wc_add_notice' ) ) {
		wc_add_notice( $message, 'error' );
	}
	wp_safe_redirect( dg_account_endpoint_url( '' ) );
	exit;
}

