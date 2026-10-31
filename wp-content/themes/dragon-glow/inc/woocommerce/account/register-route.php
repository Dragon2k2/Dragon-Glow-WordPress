<?php
/**
 * Dragon Glow — My Account: Clean Register Route (/register/)
 *
 * Serves the registration page at `/register/` instead of
 * `/my-account/register/`. Like `/login/` (see login-route.php), `/register/`
 * is NOT a separate page: a rewrite rule maps it onto the WooCommerce My
 * Account page with the existing `register` endpoint query var set, so
 * `is_account_page()`, `dg_current_account_endpoint()`, the template swap, the
 * body class and the conditional CSS/JS enqueue (all keyed on
 * `query_vars['register']`) keep working unchanged.
 *
 * Behavior:
 *   - GET /register/                      → renders the register page (signed out).
 *   - GET /register/ while signed in      → redirect to `redirect_to` / dashboard.
 *   - GET /my-account/register/ signed out → 302 to /register/ (keeps query string).
 *   - POST /register/                     → handled by WC_Form_Handler on `wp_loaded`;
 *                                           errors re-render the form on /register/.
 *
 * Rewrite rules are flushed once automatically whenever the rule signature
 * changes (new deploy or a different My Account page ID).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the registration URL (`/register/`), optionally carrying `redirect_to`.
 *
 * Falls back to the `register` endpoint of My Account when the clean route is
 * unavailable (WooCommerce inactive, no My Account page or Plain permalinks —
 * same conditions as /login/, see dg_login_route_enabled()).
 *
 * @param string $redirect_to Absolute URL (or path) to return to after sign-up.
 * @return string
 */
function dg_register_url( string $redirect_to = '' ): string {
	$url = dg_login_route_enabled() ? home_url( '/register/' ) : dg_account_endpoint_url( 'register' );

	if ( '' === $redirect_to ) {
		return $url;
	}

	return add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $url );
}

/**
 * Register the `/register/` rewrite rule.
 *
 * Targets the My Account page by ID (not slug) so renaming the page slug does
 * not break the route. `register=1` sets the WC endpoint query var (its value
 * is irrelevant — only `isset()` is checked); `dg_register=1` marks the clean
 * route so canonical redirects and document title can tell it apart from
 * `/my-account/register/`.
 *
 * @return void
 */
function dg_register_register_route(): void {
	if ( ! dg_login_route_enabled() ) {
		return;
	}

	add_rewrite_rule(
		'^register/?$',
		'index.php?page_id=' . (int) get_option( 'woocommerce_myaccount_page_id' ) . '&register=1&dg_register=1',
		'top'
	);
}
add_action( 'init', 'dg_register_register_route' );

/**
 * Whitelist the `dg_register` query var.
 *
 * @param array<string> $vars Public query vars.
 * @return array<string>
 */
function dg_register_route_query_vars( array $vars ): array {
	$vars[] = 'dg_register';
	return $vars;
}
add_filter( 'query_vars', 'dg_register_route_query_vars' );

/**
 * Flush rewrite rules once when the /register/ rule signature changes.
 *
 * Runs after WooCommerce registered its endpoints (same priority as
 * `dg_deferred_rewrite_flush()` in setup.php) and stores the signature in an
 * option, so later requests only pay for one autoloaded `get_option()`.
 *
 * @return void
 */
function dg_register_route_maybe_flush(): void {
	if ( ! dg_login_route_enabled() ) {
		return;
	}

	$signature = 'v1-' . (int) get_option( 'woocommerce_myaccount_page_id' );
	if ( get_option( 'dg_register_route_signature' ) === $signature ) {
		return;
	}

	dg_flush_rewrite_rules_safe();
	update_option( 'dg_register_route_signature', $signature );
}
add_action( 'init', 'dg_register_route_maybe_flush', 999 );

/**
 * Stop WordPress from canonicalising /register/ to /my-account/register/.
 *
 * The rule resolves to `page_id=N&register=1`, which `redirect_canonical()`
 * would otherwise rewrite to the endpoint permalink.
 *
 * @param string|false $redirect_url Canonical URL or false.
 * @return string|false
 */
function dg_register_route_disable_canonical( $redirect_url ) {
	if ( get_query_var( 'dg_register' ) ) {
		return false;
	}

	return $redirect_url;
}
add_filter( 'redirect_canonical', 'dg_register_route_disable_canonical' );

/**
 * Route redirects between /register/ and /my-account/register/.
 *
 * Runs on `template_redirect` priority 1, before the social-login dispatcher
 * (priority 5) and before `redirect_canonical` (priority 10).
 *
 * @return void
 */
function dg_register_route_dispatch(): void {
	if ( ! dg_login_route_enabled() || ! is_account_page() || 'register' !== dg_current_account_endpoint() ) {
		return;
	}

	// Signed-in users have no use for the registration screen. Handled here
	// (before any output) for both URLs, since the render-time redirect in
	// dg_render_wc_account() fires after get_header() has sent headers.
	if ( is_user_logged_in() ) {
		wp_safe_redirect( dg_account_safe_redirect_target() );
		exit;
	}

	if ( get_query_var( 'dg_register' ) ) {
		return;
	}

	// Only canonicalise plain page views. Never redirect form submissions
	// (WC processes them on `wp_loaded`, before this hook).
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
	if ( 'GET' !== $method ) {
		return;
	}

	// Forward the query string untouched (`redirect_to`, campaign params).
	// `redirect_to` is validated on the /register/ request by
	// dg_account_safe_redirect_target().
	$args = urlencode_deep( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only pass-through of the query string to our own URL.

	wp_safe_redirect( add_query_arg( $args, dg_register_url() ) );
	exit;
}
add_action( 'template_redirect', 'dg_register_route_dispatch', 1 );

/**
 * Give /register/ its own document title instead of the "My Account" page title.
 *
 * @param array<string,string> $parts Document title parts.
 * @return array<string,string>
 */
function dg_register_route_document_title( array $parts ): array {
	if ( get_query_var( 'dg_register' ) ) {
		$parts['title'] = __( 'Create Account', 'dragon-glow' );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'dg_register_route_document_title' );
