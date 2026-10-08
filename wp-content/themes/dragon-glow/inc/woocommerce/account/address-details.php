<?php
/**
 * Dragon Glow — My Account: Address Details View (Read-Only List)
 *
 * Renders the `/my-account/address-details/` endpoint — a read-only
 * addresses list view (billing + shipping cards, matches the Luminous
 * Ethereal design system). The actual edit forms live in `addresses.php`.
 *
 * The `address-details` rewrite endpoint is registered in
 * `address-details-endpoint.php`.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the "Address details" URL (read-only list view).
 *
 * @return string
 */
function dg_address_details_url(): string {
	return dg_account_endpoint_url( 'address-details' );
}

/**
 * Render the `/my-account/address-details/` panel — read-only addresses list.
 *
 * Header shows "Back to dashboard" link. The list includes action buttons
 * to route to edit forms at `/my-account/edit-address/?address=billing|shipping`.
 *
 * @return void
 */
function dg_render_address_details_panel(): void {
	?>
	<section class="dg-account-panel dg-account-panel--address-details" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Addresses', 'dragon-glow' ); ?></h2>
			<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-account-panel__link">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Back to dashboard', 'dragon-glow' ); ?>
			</a>
		</header>

		<?php
		// Success notice after WC save redirects back here.
		if ( function_exists( 'wc_print_notices' ) ) {
			echo '<div class="dg-account-notices">';
			wc_print_notices();
			echo '</div>';
		}

		dg_render_account_addresses_list();
		?>
	</section>
	<?php
}
