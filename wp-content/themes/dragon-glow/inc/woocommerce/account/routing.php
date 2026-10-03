<?php
/**
 * Dragon Glow — My Account: Routing & Endpoint Helpers
 *
 * URL building, endpoint detection, and the `template_include` swap that
 * routes the WC My Account page through `page-templates/template-wc-account.php`.
 * These helpers are consumed by every other file in `inc/woocommerce/account/`.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build paginated URL for account endpoints (legacy/fallback).
 *
 * This function exists for backward compatibility with older versions of
 * woocommerce/myaccount/orders.php that may still be cached on the server.
 * New code should use dg_account_endpoint_url() + add_query_arg() directly.
 *
 * @deprecated Use dg_account_endpoint_url() with add_query_arg() instead.
 * @param string $endpoint Endpoint slug.
 * @param int    $page     Page number (1-indexed).
 * @return string
 */
function dg_account_paginated_url( string $endpoint, int $page ): string {
	if ( $page <= 1 ) {
		return dg_account_endpoint_url( $endpoint );
	}
	return add_query_arg( 'paged', $page, dg_account_endpoint_url( $endpoint ) );
}

/**
 * Get WC account endpoint URL safely.
 *
 * Wrapper around `wc_get_account_endpoint_url()` — only available when WC is
 * active. Falls back to a hand-built URL from the My Account page permalink.
 *
 * @param string $endpoint Endpoint slug (e.g. 'orders', 'edit-account').
 * @return string
 */
function dg_account_endpoint_url( string $endpoint ): string {
	// Build base URL from the configured My Account page id, not from
	// WC's URL builder. WC's wc_get_account_endpoint_url() returns the
	// page permalink which in some environments (especially when
	// permalinks haven't fully flushed or with W3 Total Cache serving
	// stale data) falls back to "?page_id=N" — leaking the query string
	// into nav links and the auth gate's action URLs.
	$page_id = (int) get_option( 'woocommerce_myaccount_page_id' );
	$base    = '';

	if ( $page_id > 0 ) {
		$base = (string) get_permalink( $page_id );
		// `get_permalink()` can return false/'' OR a "?page_id=N" string
		// when WP permalinks aren't pretty (default permalinks) or when
		// the post cache is stale. Only accept clean permalinks —
		// otherwise fall through to the hard-coded "/my-account/".
		if ( false === $base || '' === $base || false !== strpos( $base, 'page_id=' ) || false !== strpos( $base, '?p=' ) ) {
			$base = '';
		}
	}

	// Hard-coded fallback — works even when WP hasn't registered the page
	// in the rewrite rules yet, because it's the path WC's own endpoint
	// system expects.
	if ( '' === $base ) {
		$base = home_url( '/my-account' );
	}
	$base = rtrim( $base, '/' );

	// Empty endpoint = My Account root.
	if ( '' === $endpoint ) {
		return $base . '/';
	}

	return $base . '/' . ltrim( $endpoint, '/' );
}

/**
 * Build rewrite-slug → endpoint-key map from WooCommerce query vars.
 *
 * @return array<string, string>
 */
function dg_account_endpoint_slug_map(): array {
	$map = array();
	if ( ! function_exists( 'WC' ) || ! WC() || ! isset( WC()->query ) || ! is_object( WC()->query ) ) {
		return $map;
	}
	if ( ! method_exists( WC()->query, 'get_query_vars' ) ) {
		return $map;
	}

	$vars = WC()->query->get_query_vars();
	if ( ! is_array( $vars ) ) {
		return $map;
	}

	foreach ( $vars as $key => $slug ) {
		if ( is_string( $key ) && is_string( $slug ) && '' !== $slug ) {
			$map[ $slug ] = $key;
		}
	}

	return $map;
}

/**
 * Path segments after the My Account page URI.
 *
 * Prefers `$wp->request`, then falls back to `REQUEST_URI` so refresh on
 * `/my-account/orders/` still resolves when query vars / `$wp->request` are
 * empty (common on shared hosting with incomplete endpoint rewrites).
 *
 * @return array<int, string>
 */
function dg_account_path_segments_after_base(): array {
	$page_id = (int) get_option( 'woocommerce_myaccount_page_id' );
	if ( $page_id <= 0 ) {
		return array();
	}

	$account_uri = trim( (string) get_page_uri( $page_id ), '/' );
	if ( '' === $account_uri ) {
		$account_uri = 'my-account';
	}

	$candidates = array();

	global $wp;
	if ( isset( $wp->request ) && is_string( $wp->request ) && '' !== trim( $wp->request, '/' ) ) {
		$candidates[] = trim( $wp->request, '/' );
	}

	if ( isset( $_SERVER['REQUEST_URI'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path only via wp_parse_url.
		$uri_path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		$uri_path = trim( $uri_path, '/' );

		// Strip subdirectory home path when WP is not at domain root.
		$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
		if ( '' !== $home_path ) {
			if ( $uri_path === $home_path ) {
				$uri_path = '';
			} elseif ( 0 === strpos( $uri_path, $home_path . '/' ) ) {
				$uri_path = substr( $uri_path, strlen( $home_path ) + 1 );
			}
		}

		if ( '' !== $uri_path ) {
			$candidates[] = $uri_path;
		}
	}

	$prefix = $account_uri . '/';
	foreach ( $candidates as $path ) {
		if ( $path === $account_uri || 0 !== strpos( $path, $prefix ) ) {
			continue;
		}
		$after = substr( $path, strlen( $prefix ) );
		$parts = array_values( array_filter( explode( '/', $after ), 'strlen' ) );
		if ( ! empty( $parts ) ) {
			return $parts;
		}
	}

	return array();
}

/**
 * Determine current My Account endpoint key for sidebar + panel switch.
 *
 * Resolution order:
 * 1. WooCommerce native query-var endpoint (`get_current_endpoint`).
 * 2. First path segment after the My Account page URI (REQUEST_URI fallback).
 *
 * Returns the internal endpoint key (e.g. `orders`), not a customized rewrite
 * slug, so `dg_render_wc_account()` switch cases stay stable.
 *
 * @return string Empty string = dashboard.
 */
function dg_current_account_endpoint(): string {
	if ( ! function_exists( 'WC' ) || ! WC() ) {
		return '';
	}
	if ( ! isset( WC()->query ) || ! is_object( WC()->query ) ) {
		return '';
	}

	// 1) Native WC — works when rewrite rules registered the endpoint query var.
	if ( method_exists( WC()->query, 'get_current_endpoint' ) ) {
		$native = (string) WC()->query->get_current_endpoint();
		if ( '' !== $native ) {
			return $native;
		}
	}

	// 2) Path after /my-account/ — survives refresh when query vars are missing.
	$segments = dg_account_path_segments_after_base();
	if ( empty( $segments ) ) {
		return '';
	}

	$candidate = $segments[0];

	$slug_map = dg_account_endpoint_slug_map();
	if ( isset( $slug_map[ $candidate ] ) ) {
		return $slug_map[ $candidate ];
	}

	return '';
}

/**
 * Get current pagination page for account orders endpoint.
 *
 * @return int Page number (1-indexed).
 */
function dg_get_account_orders_page(): int {
	$current_page = 1;
	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

	// Check get_query_var('page') first
	$qv_page = (int) get_query_var( 'page' );
	if ( $qv_page > 1 ) {
		$current_page = $qv_page;
	}

	// Check REQUEST_URI for /page/N/ pattern
	if ( 1 === $current_page && '' !== $request_uri ) {
		if ( preg_match( '#/orders/page/(\d+)/?$#', $request_uri, $matches ) ) {
			$current_page = max( 1, (int) $matches[1] );
		}
	}

	// Fallback: check $_GET['paged']
	if ( 1 === $current_page && isset( $_GET['paged'] ) ) {
		$current_page = max( 1, (int) $_GET['paged'] );
	}

	return $current_page;
}

/**
 * Set document title for My Account pages with pagination support.
 *
 * @param string $title Original title.
 * @return string Modified title.
 */
function dg_account_document_title( string $title ): string {
	// Only modify titles on the My Account page.
	if ( ! is_page( get_option( 'woocommerce_myaccount_page_id' ) ) ) {
		return $title;
	}

	$endpoint = dg_current_account_endpoint();

	if ( 'orders' === $endpoint ) {
		$paged = dg_get_account_orders_page();
		if ( $paged > 1 ) {
			/* translators: %d: Page number */
			$title = sprintf( esc_html__( 'Orders (page %d)', 'dragon-glow' ), $paged );
		} else {
			$title = esc_html__( 'Orders', 'dragon-glow' );
		}
	}

	return $title;
}
add_filter( 'pre_get_document_title', 'dg_account_document_title' );

/**
 * Force the WC account endpoint to render through our custom template.
 *
 * Mirrors `dg_use_wc_checkout_template()`. Without this filter the WC default
 * `my-account.php` template would run, which doesn't match the Luminous
 * Ethereal design system.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function dg_use_wc_account_template( string $template ): string {
	if ( ! did_action( 'wp' ) ) {
		return $template;
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_order_received_page() ) {
		$custom = locate_template( 'page-templates/template-wc-account.php' );
		if ( $custom ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'dg_use_wc_account_template' );

/**
 * Add body class to hide site chrome (header-nav and footer-main) when viewing
 * the auth portal (My Account page while signed out or /register endpoint).
 *
 * The auth portal (Heritage Atelier) is a full-bleed, distraction-free screen —
 * enterprise pattern for dedicated auth flows. We still call `get_header()` and
 * `get_footer()` so `wp_head()`/`wp_footer()` fire (enqueue CSS/JS), but hide
 * the site nav and footer columns via CSS scoped to the body class.
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function dg_auth_portal_body_class( array $classes ): array {
	// Sign-in page (not logged in)
	if ( is_account_page() && ! is_user_logged_in() ) {
		$classes[] = 'dg-hide-site-chrome';
	}

	// Register endpoint (accessed via /my-account/register/)
	global $wp_query;
	if ( is_account_page() && isset( $wp_query->query_vars['register'] ) ) {
		$classes[] = 'dg-hide-site-chrome';
	}

	return $classes;
}
add_filter( 'body_class', 'dg_auth_portal_body_class' );
