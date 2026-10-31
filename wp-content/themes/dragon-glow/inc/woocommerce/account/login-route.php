<?php
/**
 * Dragon Glow — My Account: Clean Login Route (/login/)
 *
 * Serves the signed-out auth gate ("Heritage Atelier") at `/login/` instead of
 * the bare `/my-account/` URL. `/login/` is NOT a separate page: a rewrite rule
 * maps it onto the WooCommerce My Account page itself (plus the `dg_login`
 * query var), so `is_account_page()`, the template swap, body classes and the
 * conditional CSS/JS enqueue keep working unchanged.
 *
 * Behavior:
 *   - GET /login/                   → renders the auth gate (signed out).
 *   - GET /login/ while signed in   → redirect to `redirect_to` / dashboard.
 *   - GET /my-account/ signed out   → 302 to /login/ (keeps `redirect_to`).
 *   - POST /login/                  → handled by WC_Form_Handler on `wp_loaded`
 *                                     (same as /my-account/); errors re-render
 *                                     the form on /login/ (form action = /login/).
 *   - wp_login_url() on the frontend → /login/ (wp-admin / wp-login.php excluded).
 *
 * Rewrite rules are flushed once automatically whenever the rule signature
 * changes (new deploy or a different My Account page ID).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the /login/ route can be served on this site.
 *
 * Requires WooCommerce, a configured My Account page (/login/ is an alias of
 * that page) and pretty permalinks (rewrite rules do not apply to "Plain").
 *
 * @return bool
 */
function dg_login_route_enabled(): bool {
	return dg_is_woocommerce_active()
		&& function_exists( 'is_account_page' )
		&& '' !== (string) get_option( 'permalink_structure' )
		&& (int) get_option( 'woocommerce_myaccount_page_id' ) > 0;
}

/**
 * Build the sign-in URL (`/login/`), optionally carrying `redirect_to`.
 *
 * Falls back to the My Account root when the /login/ route is unavailable
 * (WooCommerce inactive or no My Account page), so callers never need to
 * branch. `redirect_to` is validated on arrival by
 * `dg_account_safe_redirect_target()`.
 *
 * @param string $redirect_to Absolute URL (or path) to return to after sign-in.
 * @return string
 */
function dg_login_url( string $redirect_to = '' ): string {
	$url = dg_login_route_enabled() ? home_url( '/login/' ) : dg_account_endpoint_url( '' );

	if ( '' === $redirect_to ) {
		return $url;
	}

	return add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $url );
}

/**
 * Register the `/login/` rewrite rule.
 *
 * Targets the My Account page by ID (not slug) so renaming the page slug
 * does not break the route.
 *
 * @return void
 */
function dg_register_login_route(): void {
	if ( ! dg_login_route_enabled() ) {
		return;
	}

	add_rewrite_rule(
		'^login/?$',
		'index.php?page_id=' . (int) get_option( 'woocommerce_myaccount_page_id' ) . '&dg_login=1',
		'top'
	);
}
add_action( 'init', 'dg_register_login_route' );

/**
 * Whitelist the `dg_login` query var.
 *
 * @param array<string> $vars Public query vars.
 * @return array<string>
 */
function dg_login_route_query_vars( array $vars ): array {
	$vars[] = 'dg_login';
	return $vars;
}
add_filter( 'query_vars', 'dg_login_route_query_vars' );

/**
 * Flush rewrite rules once when the /login/ rule signature changes.
 *
 * Runs after WooCommerce registered its endpoints (same priority as
 * `dg_deferred_rewrite_flush()` in setup.php) and stores the signature in an
 * option, so later requests only pay for one autoloaded `get_option()`.
 *
 * @return void
 */
function dg_login_route_maybe_flush(): void {
	if ( ! dg_login_route_enabled() ) {
		return;
	}

	$signature = 'v1-' . (int) get_option( 'woocommerce_myaccount_page_id' );
	if ( get_option( 'dg_login_route_signature' ) === $signature ) {
		return;
	}

	dg_flush_rewrite_rules_safe();
	update_option( 'dg_login_route_signature', $signature );
}
add_action( 'init', 'dg_login_route_maybe_flush', 999 );

/**
 * Stop WordPress from canonicalising /login/ back to the My Account permalink.
 *
 * The rule resolves to `page_id=N`, which `redirect_canonical()` would
 * otherwise rewrite to /my-account/.
 *
 * @param string|false $redirect_url  Canonical URL or false.
 * @return string|false
 */
function dg_login_route_disable_canonical( $redirect_url ) {
	if ( get_query_var( 'dg_login' ) ) {
		return false;
	}

	return $redirect_url;
}
add_filter( 'redirect_canonical', 'dg_login_route_disable_canonical' );

/**
 * Route redirects between /login/ and /my-account/.
 *
 * Runs before the social-login dispatcher (priority 5) and before
 * `redirect_canonical` (priority 10).
 *
 * @return void
 */
function dg_login_route_dispatch(): void {
	if ( ! dg_login_route_enabled() || ! is_account_page() ) {
		return;
	}

	$is_login_route = (bool) get_query_var( 'dg_login' );

	// Signed-in users have no use for the login screen.
	if ( is_user_logged_in() ) {
		if ( $is_login_route ) {
			wp_safe_redirect( dg_account_safe_redirect_target() );
			exit;
		}
		return;
	}

	if ( $is_login_route ) {
		return;
	}

	// Only canonicalise plain page views. Never redirect form submissions
	// (WC processes them on `wp_loaded`, before this hook) or OAuth starts.
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
	if ( 'GET' !== $method || isset( $_GET['dg_social_login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only route dispatch.
		return;
	}

	// Endpoints such as /register/ or /lost-password/ keep their own URL.
	if ( '' !== dg_current_account_endpoint() ) {
		return;
	}

	// Forward the query string untouched (`redirect_to`, WC notices such as
	// `password-reset`, campaign params). `redirect_to` is validated on the
	// /login/ request by dg_account_safe_redirect_target().
	$args = urlencode_deep( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only pass-through of the query string to our own URL.

	wp_safe_redirect( add_query_arg( $args, dg_login_url() ) );
	exit;
}
add_action( 'template_redirect', 'dg_login_route_dispatch', 1 );

/**
 * Point `wp_login_url()` at /login/ on the frontend.
 *
 * Skipped in wp-admin (so `auth_redirect()` still reaches wp-login.php and
 * admins can never be locked out) and on wp-login.php itself. admin-ajax
 * requests are treated as frontend because they build links for visitors.
 *
 * @param string $login_url    Default login URL.
 * @param string $redirect     Path/URL to return to after login.
 * @return string
 */
function dg_login_route_filter_login_url( string $login_url, string $redirect ): string {
	if ( ! dg_login_route_enabled() ) {
		return $login_url;
	}

	if ( ( is_admin() && ! wp_doing_ajax() ) || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) ) {
		return $login_url;
	}

	return dg_login_url( $redirect );
}
add_filter( 'login_url', 'dg_login_route_filter_login_url', 10, 2 );

/**
 * Give /login/ its own document title instead of the "My Account" page title.
 *
 * @param array<string,string> $parts Document title parts.
 * @return array<string,string>
 */
function dg_login_route_document_title( array $parts ): array {
	if ( get_query_var( 'dg_login' ) ) {
		$parts['title'] = __( 'Sign In', 'dragon-glow' );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'dg_login_route_document_title' );
