<?php
/**
 * Dragon Glow — AJAX: Account Navigation
 *
 * Handles AJAX loading of account panel content (Orders, Addresses, etc.)
 * for smooth navigation without full page reload. Returns HTML fragments
 * that the frontend JS injects into `.dg-account__content`.
 *
 * Security: nonce verification + `is_user_logged_in()` guard on every action.
 * Only authenticated users can fetch account panels.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX handler: load account panel content.
 *
 * Action: `wp_ajax_dg_load_account_panel`
 * Expects: $_POST['endpoint'] — WC endpoint slug ('orders', 'edit-address', etc.)
 *          $_POST['nonce']    — Security nonce.
 *
 * Returns: JSON with 'success', 'html', 'endpoint', 'title' keys.
 *
 * @return void (outputs JSON then exits).
 */
function dg_ajax_load_account_panel(): void {
	// Security: nonce + auth guard.
	// Note: nonce action is 'dg_nonce' (matches wp_create_nonce in inc/enqueue/scripts.php).
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dg_nonce' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed. Please refresh and try again.', 'dragon-glow' ),
			),
			403
		);
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message' => __( 'You must be logged in to access this content.', 'dragon-glow' ),
			),
			401
		);
	}

	// Guard: check if panel render functions are available.
	// This can fail if inc/woocommerce/account.php was not loaded due to
	// dg_is_woocommerce_active() returning false in the AJAX context.
	if ( ! function_exists( 'dg_render_account_dashboard' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Account panel functions are not available. Please refresh the page.', 'dragon-glow' ),
			),
			500
		);
	}

	// Parse endpoint and request URI for pagination support.
	// URL formats supported:
	// 1. /my-account/orders/page/2/  (pretty permalinks - WC default)
	// 2. /my-account/orders/?paged=2 (query string)
	$endpoint = isset( $_POST['endpoint'] ) ? sanitize_text_field( wp_unslash( $_POST['endpoint'] ) ) : '';

	$paged = 1;
	if ( isset( $_POST['request_uri'] ) ) {
		$request_uri = sanitize_text_field( wp_unslash( $_POST['request_uri'] ) );

		// Try /orders/page/N/ pattern first (pretty permalink format)
		if ( preg_match( '#/orders/page/(\d+)/?$#', $request_uri, $matches ) ) {
			$paged = max( 1, (int) $matches[1] );
		}

		// Fallback to ?paged=N query string format
		if ( 1 === $paged ) {
			$parsed = wp_parse_url( $request_uri );
			if ( isset( $parsed['query'] ) ) {
				parse_str( $parsed['query'], $query_args );
				if ( isset( $query_args['paged'] ) ) {
					$paged = max( 1, (int) $query_args['paged'] );
				}
			}
		}
	}

	// Wrap rendering in try-catch to catch any errors and return them to the client.
	try {
		// Start output buffering to capture the panel HTML.
		ob_start();

	// Route to the appropriate renderer based on endpoint.
	switch ( $endpoint ) {
		case 'orders':
			// Set pagination from AJAX request context.
			if ( $paged > 1 ) {
				$_GET['paged'] = $paged;
			}
			dg_render_account_orders_panel();
			// translators: %d: Page number (e.g., "Orders (page 2)").
			$title = $paged > 1
				? sprintf( esc_html__( 'Orders (page %d)', 'dragon-glow' ), $paged )
				: esc_html__( 'Orders', 'dragon-glow' );
			break;

		case 'edit-address':
				dg_render_account_addresses_panel();
				$title = __( 'Addresses', 'dragon-glow' );
				break;

			case 'edit-account':
				dg_render_account_edit_panel();
				$title = __( 'Account Details', 'dragon-glow' );
				break;

			case 'dg-wishlist':
				dg_render_account_wishlist_panel();
				$title = __( 'Wishlist', 'dragon-glow' );
				break;

			case 'customer-logout':
				dg_render_account_logout_panel();
				$title = __( 'Sign Out', 'dragon-glow' );
				break;

			case '':
			default:
				// Dashboard is the default endpoint.
				dg_render_account_dashboard();
				$title = __( 'Dashboard', 'dragon-glow' );
				$endpoint = '';
				break;
		}

		$html = ob_get_clean();

		// Return JSON response with the rendered HTML fragment.
		wp_send_json_success(
			array(
				'html'     => $html,
				'endpoint' => $endpoint,
				'title'    => $title,
			)
		);
	} catch ( \Throwable $e ) {
		// Clean any output buffer that might be open.
		if ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// Log error for debugging.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'AJAX account panel error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );

		// Build detailed debug info.
		$debug_info = $e->getMessage();
		if ( WP_DEBUG ) {
			$debug_info .= "\nFile: " . $e->getFile() . ':' . $e->getLine();
			$debug_info .= "\nTrace:\n" . $e->getTraceAsString();
		}

		wp_send_json_error(
			array(
				'message' => __( 'Unable to load content. Please refresh the page and try again.', 'dragon-glow' ),
				'debug'   => $debug_info,
			),
			500
		);
	}
}
add_action( 'wp_ajax_dg_load_account_panel', 'dg_ajax_load_account_panel' );
