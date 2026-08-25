<?php
/**
 * Edit Address Form — Dragon Glow override.
 *
 * Renders a single address form (billing OR shipping) using WooCommerce's own
 * `woocommerce_form_field()` helper for every field, so:
 *   - the same localisation/priority/required rules apply as on the checkout
 *     form (Vietnam locale hides `state` requirement and reorders `postcode`,
 *     `address_2`, etc., per wc-address-i18n),
 *   - third-party plugins that hook `woocommerce_edit_address_form_*` keep
 *     working,
 *   - selectWoo + WC country-select + address-i18n JS auto-initialise (the
 *     three scripts that the shortcode enqueues on the parent endpoint).
 *
 * This template replaces the WC stock `form-edit-address.php` (which renders a
 * duplicate `<h2>Billing address</h2>` heading — our panel chrome already
 * shows that title, and the WC default's behaviour of falling back to
 * `my-account/my-address.php` when `$load_address` is empty is unreachable
 * here because the panel always passes the correct type from
 * `dg_current_address_edit_type()`).
 *
 * Persist flow matches WC stock: POST + `woocommerce-edit_address` nonce +
 * `save_address` submit → `WC_Form_Handler::save_address` (theme syncs
 * `?address=` into `$wp->query_vars['edit-address']` for shared hosting).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 *
 * Dragon Glow override: drops the duplicate <h2>, wraps fields in
 * `dg-account-address-edit__form`, keeps WC save POST / nonce / action.
 */

defined( 'ABSPATH' ) || exit;

// Dragon Glow: read the address type from the variable the shortcode passes.
// The parent panel always passes this — never invoke this template without it.
$type = isset( $load_address ) ? sanitize_key( $load_address ) : 'billing';

if ( function_exists( 'wc_print_notices' ) ) {
	wc_print_notices();
}
?>

<form method="post" class="woocommerce-EditAddressForm edit dg-account-address-edit__form" novalidate>

	<div class="woocommerce-address-fields">
		<?php
		/**
		 * Fires before the address form fields are rendered.
		 *
		 * Mirrors the WC stock hook so any third-party plugins that hook here
		 * (e.g. custom field addons, checkout field editors) keep working.
		 *
		 * @param string $type Address type — 'billing' | 'shipping'.
		 */
		do_action( 'woocommerce_before_edit_address_form_' . $type );
		?>

		<div class="woocommerce-address-fields__field-wrapper">
			<?php
			foreach ( $address as $key => $field ) {
				woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
			}
			?>
		</div>

		<?php
		/**
		 * Fires after the address form fields are rendered.
		 *
		 * @param string $type Address type — 'billing' | 'shipping'.
		 */
		do_action( 'woocommerce_after_edit_address_form_' . $type );
		?>
	</div>

	<?php do_action( 'woocommerce_edit_address_form_' . $type ); ?>

	<p class="dg-account-address-edit__actions">
		<button type="submit" class="button dg-account-address-edit__save" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>">
			<span class="material-symbols-outlined dg-account-address-edit__save-icon" aria-hidden="true">save</span>
			<span class="dg-account-address-edit__save-label"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></span>
		</button>
		<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
		<input type="hidden" name="action" value="edit_address" />
	</p>

</form>
