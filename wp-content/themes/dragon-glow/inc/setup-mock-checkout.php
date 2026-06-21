<?php
/**
 * Dragon Glow — Mock Checkout Page Setup
 *
 * Auto-creates the internal mock-checkout WordPress page on theme activation/switch
 * so that the mock checkout flow works without requiring manual page creation.
 *
 * The page is created with the "Mock Checkout — Dragon Glow" template and uses
 * the "mock-checkout" slug. If a published page already exists with that slug
 * (including one from a previous theme activation), the hook is idempotent —
 * no duplicate is created.
 *
 * Hooked to after_switch_theme (fires once per theme activation) so it runs at
 * a safe point in the WordPress load sequence.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create the internal mock-checkout page if it does not already exist.
 *
 * This ensures Buy Now works for mock products even when WooCommerce is inactive,
 * without requiring manual page setup by the site administrator.
 *
 * @return void
 */
function dg_setup_mock_checkout_page(): void {
	$slug = 'mock-checkout';

	// Guard: skip if a published mock-checkout page already exists (by slug or by template).
	if ( dg_find_mock_checkout_page() ) {
		return;
	}

	// Load the page template path relative to the theme directory.
	$template = 'page-templates/template-mock-checkout.php';

	$result = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => _x( 'Checkout', 'auto-created mock checkout page title', 'dragon-glow' ),
			'post_name'    => $slug,
			'post_content' => '',
			'meta_input'   => array(
				'_wp_page_template' => $template,
			),
		),
		true // $wp_error — returns WP_Error on failure for clearer error handling.
	);

	if ( is_wp_error( $result ) ) {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			sprintf(
				'[Dragon Glow] Failed to create mock-checkout page: %s',
				$result->get_error_message()
			)
		);
	}
}
add_action( 'after_switch_theme', 'dg_setup_mock_checkout_page' );
