<?php
/**
 * Dragon Glow — My Account: Register Endpoint Registration
 *
 * Registers the custom `register` WC endpoint (`/my-account/register/`) and
 * reminds the admin to flush permalinks after this code is deployed, since
 * `add_rewrite_endpoint()` requires a permalink flush to take effect.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register custom WooCommerce endpoint for registration page.
 *
 * @return void
 */
function dg_register_custom_account_endpoints(): void {
	add_rewrite_endpoint( 'register', EP_ROOT | EP_PAGES );

	// Set a transient to remind admin to flush permalinks after code deployment
	if ( ! get_transient( 'dg_register_endpoint_flushed' ) ) {
		set_transient( 'dg_register_endpoint_flushed', 'pending', DAY_IN_SECONDS );
	}
}
add_action( 'init', 'dg_register_custom_account_endpoints' );

/**
 * Add register endpoint to WooCommerce query vars.
 *
 * @param array<string> $vars Query vars.
 * @return array<string>
 */
function dg_add_register_query_var( array $vars ): array {
	$vars['register'] = 'register';
	return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'dg_add_register_query_var' );

/**
 * Admin notice to remind flushing permalinks after register endpoint addition.
 *
 * @return void
 */
function dg_register_endpoint_admin_notice(): void {
	$status = get_transient( 'dg_register_endpoint_flushed' );

	if ( 'pending' !== $status ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'options-permalink' === $screen->id ) {
		return; // Don't show on permalinks page itself
	}

	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Dragon Glow: New Account Endpoint Added', 'dragon-glow' ); ?></strong>
		</p>
		<p>
			<?php
			printf(
				/* translators: %s: URL to Permalinks settings page */
				esc_html__( 'The registration page endpoint has been added. Please %s to activate it.', 'dragon-glow' ),
				'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'flush permalinks', 'dragon-glow' ) . '</a>'
			);
			?>
		</p>
		<p>
			<em><?php esc_html_e( 'Go to Settings → Permalinks and click "Save Changes" (no need to change anything).', 'dragon-glow' ); ?></em>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'dg_register_endpoint_admin_notice' );

/**
 * Mark permalinks as flushed when admin visits Permalinks settings page.
 *
 * @return void
 */
function dg_mark_permalinks_flushed(): void {
	$screen = get_current_screen();

	if ( ! $screen || 'options-permalink' !== $screen->id ) {
		return;
	}

	// If admin visited permalinks page, assume they flushed it
	if ( 'pending' === get_transient( 'dg_register_endpoint_flushed' ) ) {
		set_transient( 'dg_register_endpoint_flushed', 'done', MONTH_IN_SECONDS );
	}
}
add_action( 'current_screen', 'dg_mark_permalinks_flushed' );
