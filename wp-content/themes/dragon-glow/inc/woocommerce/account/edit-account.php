<?php
/**
 * Dragon Glow — My Account: Edit Account Details Panel
 *
 * Wraps WC's `myaccount/form-edit-account.php` template in our panel shell
 * (header + back-to-dashboard link) for the `/my-account/edit-account/`
 * endpoint.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrap WC edit-account template in our panel shell.
 *
 * @return void
 */
function dg_render_account_edit_panel(): void {
	?>
	<section class="dg-account-panel" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Account details', 'dragon-glow' ); ?></h2>
			<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-account-panel__link">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Back to dashboard', 'dragon-glow' ); ?>
			</a>
		</header>
		<?php
		if ( function_exists( 'wc_get_template' ) ) {
			wc_get_template( 'myaccount/form-edit-account.php' );
		}
		?>
	</section>
	<?php
}
