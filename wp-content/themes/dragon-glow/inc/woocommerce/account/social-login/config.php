<?php
/**
 * Dragon Glow — Social Login: Credentials & Config
 *
 * Reads OAuth credentials from wp-config.php constants (never from the
 * database) and exposes small, typed helpers so the rest of the
 * `social-login/` concern never touches defined()/constant() directly.
 *
 * Required constants (define in wp-config.php, outside of version control):
 *
 *   define( 'DG_GOOGLE_CLIENT_ID', 'xxxxxxxx.apps.googleusercontent.com' );
 *   define( 'DG_GOOGLE_CLIENT_SECRET', 'xxxxxxxx' );
 *
 *   define( 'DG_APPLE_SERVICES_ID', 'com.dragonglow.web' );   // "Services ID" (Sign in with Apple client_id)
 *   define( 'DG_APPLE_TEAM_ID', 'XXXXXXXXXX' );               // Apple Developer Team ID
 *   define( 'DG_APPLE_KEY_ID', 'XXXXXXXXXX' );                // Sign in with Apple Key ID
 *   define( 'DG_APPLE_PRIVATE_KEY', "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----" );
 *
 * None of the buttons activate until the matching set of constants is
 * fully defined — see dg_social_login_google_enabled() / dg_social_login_apple_enabled().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Google Sign-In is fully configured.
 *
 * @return bool
 */
function dg_social_login_google_enabled(): bool {
	return defined( 'DG_GOOGLE_CLIENT_ID' ) && '' !== DG_GOOGLE_CLIENT_ID
		&& defined( 'DG_GOOGLE_CLIENT_SECRET' ) && '' !== DG_GOOGLE_CLIENT_SECRET;
}

/**
 * Whether Sign in with Apple is fully configured.
 *
 * @return bool
 */
function dg_social_login_apple_enabled(): bool {
	return defined( 'DG_APPLE_SERVICES_ID' ) && '' !== DG_APPLE_SERVICES_ID
		&& defined( 'DG_APPLE_TEAM_ID' ) && '' !== DG_APPLE_TEAM_ID
		&& defined( 'DG_APPLE_KEY_ID' ) && '' !== DG_APPLE_KEY_ID
		&& defined( 'DG_APPLE_PRIVATE_KEY' ) && '' !== DG_APPLE_PRIVATE_KEY;
}

/**
 * OAuth redirect URI shared by every provider — the provider is carried in
 * the `state` param (CSRF token), not in the path, so a single callback
 * route (`template_redirect`) handles both. Must be added verbatim to the
 * "Authorized redirect URIs" (Google) / "Return URLs" (Apple) allow-list.
 *
 * @return string
 */
function dg_social_login_redirect_uri(): string {
	return home_url( '/dg-social-login-callback/' );
}

/**
 * Build the My Account link that kicks off a provider's OAuth flow.
 *
 * Rendered as a plain `<a>` (not a JS fetch) — the browser must perform a
 * top-level navigation to Google/Apple's consent screen.
 *
 * @param string $provider Provider slug ('google' or 'apple').
 * @return string
 */
function dg_social_login_start_url( string $provider ): string {
	return add_query_arg(
		array( 'dg_social_login' => $provider ),
		dg_account_endpoint_url( '' )
	);
}
