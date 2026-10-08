<?php
/**
 * Dragon Glow — My Account: Account Details Endpoint Registration
 *
 * Registers the custom `account-details` WC endpoint (`/my-account/account-details/`)
 * — the read-only "Account details" view — as a real rewrite endpoint
 * distinct from `/my-account/edit-account/` (the WC edit form). Mirrors
 * `register-endpoint.php`'s rewrite + query-var + flush-permalinks notice
 * pattern.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register custom WooCommerce endpoint for the read-only account details view.
 *
 * @return void
 */
function dg_register_account_details_endpoint(): void {
	add_rewrite_endpoint( 'account-details', EP_ROOT | EP_PAGES );

	// Set a transient to remind admin to flush permalinks after code deployment.
	if ( ! get_transient( 'dg_account_details_endpoint_flushed' ) ) {
		set_transient( 'dg_account_details_endpoint_flushed', 'pending', DAY_IN_SECONDS );
	}
}
add_action( 'init', 'dg_register_account_details_endpoint' );

/**
 * Add account-details endpoint to WooCommerce query vars.
 *
 * @param array<string> $vars Query vars.
 * @return array<string>
 */
function dg_add_account_details_query_var( array $vars ): array {
	$vars['account-details'] = 'account-details';
	return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'dg_add_account_details_query_var' );

/**
 * Admin notice to remind flushing permalinks after this endpoint addition.
 *
 * @return void
 */
function dg_account_details_endpoint_admin_notice(): void {
	$status = get_transient( 'dg_account_details_endpoint_flushed' );

	if ( 'pending' !== $status ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'options-permalink' === $screen->id ) {
		return; // Don't show on permalinks page itself.
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
				esc_html__( 'The "Account details" page endpoint has been added. Please %s to activate it.', 'dragon-glow' ),
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
add_action( 'admin_notices', 'dg_account_details_endpoint_admin_notice' );

/**
 * Mark permalinks as flushed when admin visits Permalinks settings page.
 *
 * @return void
 */
function dg_mark_account_details_permalinks_flushed(): void {
	$screen = get_current_screen();

	if ( ! $screen || 'options-permalink' !== $screen->id ) {
		return;
	}

	// If admin visited permalinks page, assume they flushed it.
	if ( 'pending' === get_transient( 'dg_account_details_endpoint_flushed' ) ) {
		set_transient( 'dg_account_details_endpoint_flushed', 'done', MONTH_IN_SECONDS );
	}
}
add_action( 'current_screen', 'dg_mark_account_details_permalinks_flushed' );
