<?php
/**
 * Dragon Glow — Social Login: Routing
 *
 * Two entry points, both on `template_redirect` (runs before any template
 * renders, same hook family used elsewhere in this concern, e.g.
 * `dg_use_wc_account_template()`):
 *
 *   1. GET  /my-account/?dg_social_login=google|apple
 *      → start the provider's OAuth flow (redirect to consent screen).
 *   2. The redirect URI itself (`dg_social_login_redirect_uri()`,
 *      "/dg-social-login-callback/") — Google replies via GET ?code&state,
 *      Apple replies via POST (response_mode=form_post) with the same keys.
 *
 * The callback path is matched via `REQUEST_URI` (preferred) with
 * `$wp->request` as a fallback — mirrors the robustness pattern already
 * used by `dg_account_path_segments_after_base()` in
 * `inc/woocommerce/account/routing.php`. No rewrite rule is registered for
 * this path; it's a plain string match, not a WP endpoint, so `$wp->request`
 * alone cannot be trusted to be populated for every server/rewrite setup.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Route `?dg_social_login=` requests on the My Account page to the
 * matching provider's `_start()` function.
 *
 * @return void
 */
function dg_social_login_route_start(): void {
	if ( ! is_account_page() || is_user_logged_in() ) {
		return;
	}
	if ( ! isset( $_GET['dg_social_login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only route dispatch; the actual OAuth state token provides CSRF protection.
		return;
	}

	$provider = sanitize_key( wp_unslash( $_GET['dg_social_login'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- see above.

	switch ( $provider ) {
		case 'google':
			dg_google_login_start();
			break;
		case 'apple':
			dg_apple_login_start();
			break;
	}
}
add_action( 'template_redirect', 'dg_social_login_route_start', 5 );

/**
 * Handle the shared OAuth redirect/callback URI for both providers.
 *
 * The provider itself isn't in the URL — it's recovered from the `state`
 * transient we stored when the flow started (see dg_google_login_start() /
 * dg_apple_login_start()), so a single physical route serves both.
 *
 * @return void
 */
function dg_social_login_route_callback(): void {
	if ( 'dg-social-login-callback' !== dg_social_login_callback_path() ) {
		return;
	}

	// Google: GET ?code&state. Apple: POST (response_mode=form_post) with the same keys.
	$code  = '';
	$state = '';
	if ( isset( $_POST['code'], $_POST['state'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- 3rd-party IdP form_post, no WP nonce exists; CSRF protection is the `state` transient lookup below.
		$code  = sanitize_text_field( wp_unslash( $_POST['code'] ) );
		$state = sanitize_text_field( wp_unslash( $_POST['state'] ) );
	} elseif ( isset( $_GET['code'], $_GET['state'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- CSRF protection is the `state` transient lookup below.
		$code  = sanitize_text_field( wp_unslash( $_GET['code'] ) );
		$state = sanitize_text_field( wp_unslash( $_GET['state'] ) );
	} elseif ( isset( $_GET['error'] ) || isset( $_POST['error'] ) ) {
		// User cancelled consent on the provider's screen — not an error worth logging.
		dg_social_login_fail( __( 'Sign-in was cancelled.', 'dragon-glow' ) );
	}

	if ( '' === $code || '' === $state ) {
		dg_social_login_fail( __( 'Your sign-in request was incomplete. Please try again.', 'dragon-glow' ) );
	}

	// Peek at the stored provider WITHOUT deleting it — each provider's
	// own callback() re-reads + deletes the transient to keep the single-use
	// guarantee localized to one place per provider.
	$provider = get_transient( 'dg_social_state_' . $state );

	switch ( $provider ) {
		case 'google':
			dg_google_login_callback( $code, $state );
			break;
		case 'apple':
			dg_apple_login_callback( $code, $state );
			break;
		default:
			dg_social_login_fail( __( 'Your sign-in request expired or was invalid. Please try again.', 'dragon-glow' ) );
	}
}
add_action( 'template_redirect', 'dg_social_login_route_callback', 5 );

/**
 * Resolve the current request's path for matching against the social-login
 * callback route — prefers `$wp->request`, falls back to `REQUEST_URI` so
 * the callback still resolves on hosts where rewrite matching leaves
 * `$wp->request` empty for an unregistered path (same rationale as
 * `dg_account_path_segments_after_base()`).
 *
 * @return string Trimmed path (no leading/trailing slash), '' if undetermined.
 */
function dg_social_login_callback_path(): string {
	global $wp;

	if ( isset( $wp->request ) && is_string( $wp->request ) && '' !== trim( $wp->request, '/' ) ) {
		return trim( $wp->request, '/' );
	}

	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return '';
	}

	$uri_path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path only, parsed via wp_parse_url.
	$uri_path = trim( $uri_path, '/' );

	// Strip subdirectory home path when WP is not installed at domain root.
	$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( '' !== $home_path ) {
		if ( $uri_path === $home_path ) {
			return '';
		}
		if ( 0 === strpos( $uri_path, $home_path . '/' ) ) {
			$uri_path = substr( $uri_path, strlen( $home_path ) + 1 );
		}
	}

	return $uri_path;
}
