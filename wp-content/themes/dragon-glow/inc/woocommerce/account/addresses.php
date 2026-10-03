<?php
/**
 * Dragon Glow — My Account: Addresses Panel
 *
 * Renders the `/my-account/edit-address/` endpoint: list view (billing +
 * shipping cards built from user_meta so the markup matches the Luminous
 * Ethereal design system) and the single-address edit view (wraps WC's
 * `myaccount/form-edit-address.php`).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the address data array for an address type (billing/shipping).
 *
 * Pulls fields directly from user_meta so we own the markup instead of being
 * bound to WC's `myaccount/my-address.php` template (which renders a flat
 * `<p>` with no actions, no icons, no card layout — incompatible with the
 * Luminous Ethereal design system).
 *
 * Returns `null` if every field is empty (lets the renderer show a friendly
 * empty state with a single CTA to fill in the form).
 *
 * @param int    $customer_id User ID.
 * @param string $type        Address type — 'billing' | 'shipping'.
 * @return array<string, string>|null
 */
function dg_get_account_address_data( int $customer_id, string $type ): ?array {
	if ( $customer_id <= 0 || ! in_array( $type, array( 'billing', 'shipping' ), true ) ) {
		return null;
	}

	$prefix = $type . '_';

	$fields = array(
		'first_name' => (string) get_user_meta( $customer_id, $prefix . 'first_name', true ),
		'last_name'  => (string) get_user_meta( $customer_id, $prefix . 'last_name', true ),
		'company'    => (string) get_user_meta( $customer_id, $prefix . 'company', true ),
		'address_1'  => (string) get_user_meta( $customer_id, $prefix . 'address_1', true ),
		'address_2'  => (string) get_user_meta( $customer_id, $prefix . 'address_2', true ),
		'city'       => (string) get_user_meta( $customer_id, $prefix . 'city', true ),
		'state'      => (string) get_user_meta( $customer_id, $prefix . 'state', true ),
		'postcode'   => (string) get_user_meta( $customer_id, $prefix . 'postcode', true ),
		'country'    => (string) get_user_meta( $customer_id, $prefix . 'country', true ),
		'phone'      => ( 'billing' === $type ? (string) get_user_meta( $customer_id, $prefix . 'phone', true ) : '' ),
		'email'      => ( 'billing' === $type ? (string) get_user_meta( $customer_id, $prefix . 'email', true ) : '' ),
	);

	// Empty if address_1 (the canonical "address line" field) is blank.
	if ( '' === trim( $fields['address_1'] ) ) {
		return null;
	}

	return $fields;
}

/**
 * Format an address field for display.
 *
 * Returns a string with the value properly escaped. Used inside the address
 * card body so we don't repeat `esc_html()` everywhere.
 *
 * @param string $value Field value (raw).
 * @return string
 */
function dg_format_address_field( string $value ): string {
	$value = trim( $value );
	return '' === $value ? '' : esc_html( $value );
}

/**
 * Detect whether the current request is editing a single address.
 *
 * WC's `edit-address` endpoint supports an `?address=` query var (or
 * `/{type}/` path segment) so the customer can edit one address at a time.
 * Returns the address type being edited, or empty string if we're on the
 * list view.
 *
 * @return string 'billing' | 'shipping' | ''
 */
function dg_current_address_edit_type(): string {
	// Query var — used by pretty permalinks.
	$qv = isset( $_GET['address'] ) ? sanitize_key( wp_unslash( $_GET['address'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( in_array( $qv, array( 'billing', 'shipping' ), true ) ) {
		return $qv;
	}

	// Path segment — /my-account/edit-address/billing/ — fallback for shared
	// hosting where query vars aren't registered.
	$segments = dg_account_path_segments_after_base();
	if ( ! empty( $segments ) && 'edit-address' === $segments[0] && isset( $segments[1] ) ) {
		$candidate = sanitize_key( $segments[1] );
		if ( in_array( $candidate, array( 'billing', 'shipping' ), true ) ) {
			return $candidate;
		}
	}

	return '';
}

/**
 * Render the Addresses list view (Billing + Shipping cards).
 *
 * Built from user_meta instead of `wc_get_template( 'myaccount/my-address.php' )`
 * so the markup matches the Luminous Ethereal design system. WC's stock
 * template renders a flat `<p>` with no icon, no card frame, no action bar.
 *
 * @return void
 */
function dg_render_account_addresses_list(): void {
	$customer_id = get_current_user_id();
	$billing     = dg_get_account_address_data( $customer_id, 'billing' );
	$shipping    = dg_get_account_address_data( $customer_id, 'shipping' );

	// Determine which address is the "default" for shipping — WC convention is
	// "ship to billing address" when no shipping address is set.
	$default_shipping_is_billing = ( null === $shipping );

	// Empty-state guard: both addresses blank.
	if ( null === $billing && null === $shipping ) :
		?>
		<div class="dg-account-addresses dg-account-addresses--empty">
			<span class="material-symbols-outlined dg-account-empty__icon">home</span>
			<p class="dg-account-empty__title"><?php esc_html_e( 'No saved addresses yet', 'dragon-glow' ); ?></p>
			<p class="dg-account-empty__text">
				<?php esc_html_e( 'Add your billing address to speed up checkout. You can add a separate shipping address any time.', 'dragon-glow' ); ?>
			</p>
			<a href="<?php echo esc_url( add_query_arg( 'address', 'billing', dg_account_endpoint_url( 'edit-address' ) ) ); ?>"
			   class="dg-btn dg-btn--primary">
				<span class="material-symbols-outlined" aria-hidden="true">add</span>
				<?php esc_html_e( 'Add billing address', 'dragon-glow' ); ?>
			</a>
		</div>
		<?php
		return;
	endif;
	?>
	<div class="dg-account-addresses" data-sr-group>
		<?php if ( null !== $billing ) : ?>
			<?php dg_render_account_address_card( 'billing', $billing, false ); ?>
		<?php endif; ?>

		<?php if ( null !== $shipping ) : ?>
			<?php dg_render_account_address_card( 'shipping', $shipping, $default_shipping_is_billing ); ?>
		<?php elseif ( null !== $billing ) : ?>
			<?php dg_render_account_address_empty_card( 'shipping' ); ?>
		<?php endif; ?>
	</div>

	<p class="dg-account-addresses__hint">
		<span class="material-symbols-outlined" aria-hidden="true">info</span>
		<?php esc_html_e( 'These addresses will be pre-filled at checkout. You can edit or add a separate shipping address any time.', 'dragon-glow' ); ?>
	</p>
	<?php
}

/**
 * Render one address card on the Addresses list view.
 *
 * @param string                 $type                  'billing' | 'shipping'.
 * @param array<string, string>  $data                  Address fields from dg_get_account_address_data().
 * @param bool                   $is_default_for_other  True when this card is the default for the other address type
 *                                                       (e.g. shipping falls back to billing).
 * @return void
 */
function dg_render_account_address_card( string $type, array $data, bool $is_default_for_other ): void {
	$edit_url  = add_query_arg( 'address', $type, dg_account_endpoint_url( 'edit-address' ) );
	$title     = ( 'billing' === $type ) ? __( 'Billing address', 'dragon-glow' ) : __( 'Shipping address', 'dragon-glow' );
	$icon      = ( 'billing' === $type ) ? 'receipt' : 'local_shipping';
	$is_default_label = ( 'billing' === $type )
		? __( 'Default for all orders', 'dragon-glow' )
		: __( 'Default shipping address', 'dragon-glow' );

	$full_name = trim( $data['first_name'] . ' ' . $data['last_name'] );

	// Build address lines: line1 (+optional line2), then city/state/postcode.
	$line_1 = dg_format_address_field( $data['address_1'] );
	$line_2 = dg_format_address_field( $data['address_2'] );
	$city   = dg_format_address_field( $data['city'] );
	$state  = dg_format_address_field( $data['state'] );
	$zip    = dg_format_address_field( $data['postcode'] );
	$country = dg_format_address_field( $data['country'] );

	$city_line = trim( implode( ' ', array_filter( array( $city, $state, $zip ) ) ) );
	?>
	<article class="dg-account-address" data-sr data-address-type="<?php echo esc_attr( $type ); ?>">
		<header class="dg-account-address__head">
			<div class="dg-account-address__title-block">
				<div class="dg-account-address__icon" aria-hidden="true">
					<span class="material-symbols-outlined"><?php echo esc_html( $icon ); ?></span>
				</div>
				<div>
					<h3 class="dg-account-address__title"><?php echo esc_html( $title ); ?></h3>
					<?php if ( $is_default_for_other ) : ?>
						<span class="dg-account-address__badge dg-account-address__badge--default">
							<span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
							<?php echo esc_html( $is_default_label ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<div class="dg-account-address__body">
			<?php if ( '' !== $full_name ) : ?>
				<p class="dg-account-address__name"><?php echo esc_html( $full_name ); ?></p>
			<?php endif; ?>

			<address class="dg-account-address__lines">
				<?php if ( '' !== $line_1 ) : ?>
					<span><?php echo $line_1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped via dg_format_address_field(). ?></span>
				<?php endif; ?>
				<?php if ( '' !== $line_2 ) : ?>
					<span><?php echo $line_2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped via dg_format_address_field(). ?></span>
				<?php endif; ?>
				<?php if ( '' !== $city_line ) : ?>
					<span><?php echo esc_html( $city_line ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $country ) : ?>
					<span><?php echo esc_html( $country ); ?></span>
				<?php endif; ?>
			</address>

			<?php if ( 'billing' === $type ) : ?>
				<dl class="dg-account-address__contacts">
					<?php if ( '' !== $data['phone'] ) : ?>
						<div>
							<dt>
								<span class="material-symbols-outlined" aria-hidden="true">call</span>
								<?php esc_html_e( 'Phone', 'dragon-glow' ); ?>
							</dt>
							<dd>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $data['phone'] ) ); ?>">
									<?php echo esc_html( $data['phone'] ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>
					<?php if ( '' !== $data['email'] ) : ?>
						<div>
							<dt>
								<span class="material-symbols-outlined" aria-hidden="true">mail</span>
								<?php esc_html_e( 'Email', 'dragon-glow' ); ?>
							</dt>
							<dd>
								<a href="mailto:<?php echo esc_attr( $data['email'] ); ?>">
									<?php echo esc_html( $data['email'] ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>
				</dl>
			<?php endif; ?>
		</div>

		<footer class="dg-account-address__actions">
			<a href="<?php echo esc_url( $edit_url ); ?>" class="dg-account-address__action dg-account-address__action--primary">
				<span class="material-symbols-outlined" aria-hidden="true">edit</span>
				<?php esc_html_e( 'Edit address', 'dragon-glow' ); ?>
			</a>
			<?php if ( 'shipping' === $type ) : ?>
				<form method="post"
				      action="<?php echo esc_url( dg_account_endpoint_url( 'edit-address' ) ); ?>"
				      class="dg-account-use-billing"
				      data-dg-use-billing
				      data-confirm-title="<?php echo esc_attr__( 'Use billing instead?', 'dragon-glow' ); ?>"
				      data-confirm="<?php echo esc_attr__( 'Remove your shipping address and deliver orders to your billing address instead?', 'dragon-glow' ); ?>"
				      data-confirm-ok="<?php echo esc_attr__( 'Use billing', 'dragon-glow' ); ?>"
				      data-confirm-cancel="<?php echo esc_attr__( 'Cancel', 'dragon-glow' ); ?>">
					<?php wp_nonce_field( 'dg_use_billing_instead', 'dg_use_billing_nonce' ); ?>
					<input type="hidden" name="dg_use_billing_instead" value="1" />
					<button type="submit" class="dg-account-address__action dg-account-use-billing__btn">
						<span class="material-symbols-outlined dg-account-use-billing__icon" aria-hidden="true">swap_horiz</span>
						<span class="dg-account-use-billing__label"><?php esc_html_e( 'Use billing instead', 'dragon-glow' ); ?></span>
					</button>
				</form>
			<?php endif; ?>
		</footer>
	</article>
	<?php
}

/**
 * Render an "add address" placeholder card when one of the two addresses is unset.
 *
 * @param string $type 'billing' | 'shipping'.
 * @return void
 */
function dg_render_account_address_empty_card( string $type ): void {
	$add_url = add_query_arg( 'address', $type, dg_account_endpoint_url( 'edit-address' ) );
	$title   = ( 'shipping' === $type ) ? __( 'Shipping address', 'dragon-glow' ) : __( 'Billing address', 'dragon-glow' );
	$text    = ( 'shipping' === $type )
		? __( 'You haven\'t added a separate shipping address. We currently ship your orders to your billing address.', 'dragon-glow' )
		: __( 'You haven\'t added a billing address yet.', 'dragon-glow' );
	?>
	<article class="dg-account-address dg-account-address--empty" data-sr data-address-type="<?php echo esc_attr( $type ); ?>">
		<div class="dg-account-address__icon" aria-hidden="true">
			<span class="material-symbols-outlined">add_location_alt</span>
		</div>
		<h3 class="dg-account-address__title"><?php echo esc_html( $title ); ?></h3>
		<p class="dg-account-address__text"><?php echo esc_html( $text ); ?></p>
		<a href="<?php echo esc_url( $add_url ); ?>" class="dg-btn dg-btn--ghost">
			<span class="material-symbols-outlined" aria-hidden="true">add</span>
			<?php esc_html_e( 'Add address', 'dragon-glow' ); ?>
		</a>
	</article>
	<?php
}

/**
 * Render the edit-address form (single address).
 *
 * Wraps WC's `myaccount/form-edit-address.php` in our panel shell. The shell
 * gives a clear header and breadcrumb back link. Save posts through WC's
 * `WC_Form_Handler::save_address` (nonce + validation + redirect).
 *
 * @param string $type 'billing' | 'shipping'.
 * @return void
 */
function dg_render_account_addresses_edit( string $type ): void {
	$title     = ( 'billing' === $type ) ? __( 'Billing address', 'dragon-glow' ) : __( 'Shipping address', 'dragon-glow' );
	$subtitle  = ( 'billing' === $type )
		? __( 'Used for invoices and as the default for your orders.', 'dragon-glow' )
		: __( 'Where your luminous ritual kits will be delivered.', 'dragon-glow' );
	$icon      = ( 'billing' === $type ) ? 'receipt' : 'local_shipping';
	$back_url  = dg_account_endpoint_url( 'edit-address' );
	?>
	<article class="dg-account-address-edit" data-sr>
		<header class="dg-account-address-edit__head">
			<a href="<?php echo esc_url( $back_url ); ?>" class="dg-account-address-edit__back">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'All addresses', 'dragon-glow' ); ?>
			</a>
			<div class="dg-account-address-edit__heading">
				<div class="dg-account-address-edit__icon" aria-hidden="true">
					<span class="material-symbols-outlined"><?php echo esc_html( $icon ); ?></span>
				</div>
				<div>
					<h2 class="dg-account-address-edit__title"><?php echo esc_html( $title ); ?></h2>
					<p class="dg-account-address-edit__sub"><?php echo esc_html( $subtitle ); ?></p>
				</div>
			</div>
		</header>

		<?php
		if ( function_exists( 'wc_get_template' ) ) {
			// Re-use WC's own shortcode handler — it already builds the
			// $address array (country-aware fields, current user values,
			// filtered through woocommerce_address_to_edit) and calls
			// wc_get_template('myaccount/form-edit-address.php', ...) with
			// the variable names the template expects. Building $address
			// by hand here would duplicate that logic and drift from
			// checkout field customisations set by plugins.
			\WC_Shortcode_My_Account::edit_address( $type );
		}
		?>
	</article>
	<?php
}

/**
 * Render the Addresses panel shell + route to list or edit view.
 *
 * Auto-detects whether the current request is editing one address or
 * browsing the list. Mirrors WC's `myaccount/my-address.php` + `myaccount/edit-address.php`
 * route, but with our own Luminous Ethereal markup on the list side.
 *
 * @return void
 */
function dg_render_account_addresses_panel(): void {
	$edit_type = dg_current_address_edit_type();
	?>
	<section class="dg-account-panel dg-account-panel--addresses" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Addresses', 'dragon-glow' ); ?></h2>
			<?php if ( '' === $edit_type ) : ?>
				<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-account-panel__link">
					<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
					<?php esc_html_e( 'Back to dashboard', 'dragon-glow' ); ?>
				</a>
			<?php endif; ?>
		</header>

		<?php
		// Success notice after WC save redirects to the list; errors stay on the
		// edit form (also printed inside form-edit-address.php). Print here so
		// the list view surfaces "Address changed successfully."
		if ( '' === $edit_type && function_exists( 'wc_print_notices' ) ) {
			echo '<div class="dg-account-notices">';
			wc_print_notices();
			echo '</div>';
		}

		if ( '' !== $edit_type ) {
			dg_render_account_addresses_edit( $edit_type );
		} else {
			dg_render_account_addresses_list();
		}
		?>
	</section>
	<?php
}

/**
 * Sync theme `?address=` / path type into WC's `edit-address` query var.
 *
 * Shared hosting often cannot rely on `/edit-address/billing/` rewrites, so
 * the theme uses `?address=billing|shipping`. `WC_Form_Handler::save_address`
 * still reads `$wp->query_vars['edit-address']` — without this sync, shipping
 * saves would fall back to billing (empty query var).
 *
 * Runs at priority 1 on `template_redirect`, before WC's save handler (10).
 *
 * @return void
 */
function dg_sync_edit_address_query_var(): void {
	global $wp;

	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	$type = dg_current_address_edit_type();
	if ( '' === $type ) {
		return;
	}

	if ( ! isset( $wp->query_vars ) || ! is_array( $wp->query_vars ) ) {
		return;
	}

	$current = isset( $wp->query_vars['edit-address'] ) ? sanitize_key( (string) $wp->query_vars['edit-address'] ) : '';
	if ( in_array( $current, array( 'billing', 'shipping' ), true ) ) {
		return;
	}

	$wp->query_vars['edit-address'] = $type;
}
add_action( 'template_redirect', 'dg_sync_edit_address_query_var', 1 );

/**
 * Clear the logged-in customer's shipping address fields.
 *
 * WC convention: empty shipping meta ⇒ ship to billing at checkout.
 * Does not modify billing fields.
 *
 * @param int $user_id Customer user ID.
 * @return bool True when cleared and saved.
 */
function dg_clear_customer_shipping_address( int $user_id ): bool {
	if ( $user_id <= 0 || ! class_exists( 'WC_Customer' ) ) {
		return false;
	}

	$customer = new WC_Customer( $user_id );
	$keys     = array(
		'shipping_first_name',
		'shipping_last_name',
		'shipping_company',
		'shipping_address_1',
		'shipping_address_2',
		'shipping_city',
		'shipping_state',
		'shipping_postcode',
		'shipping_country',
		'shipping_phone',
	);

	foreach ( $keys as $key ) {
		$setter = 'set_' . $key;
		if ( is_callable( array( $customer, $setter ) ) ) {
			$customer->{$setter}( '' );
		} else {
			$customer->update_meta_data( $key, '' );
		}
	}

	$customer->save();

	/**
	 * Fires after shipping address fields are cleared (ship-to-billing).
	 *
	 * @param int         $user_id  Customer ID.
	 * @param WC_Customer $customer Customer object after save.
	 */
	do_action( 'dg_customer_cleared_shipping_address', $user_id, $customer );

	return true;
}

/**
 * Handle "Use billing instead" — remove shipping so orders ship to billing.
 *
 * Expects POST: dg_use_billing_instead=1 + dg_use_billing_nonce.
 * Mirrors WC My Account form handlers (nonce → mutate → notice → redirect).
 *
 * @return void
 */
function dg_handle_use_billing_instead(): void {
	if ( empty( $_POST['dg_use_billing_instead'] ) ) {
		return;
	}

	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$nonce = isset( $_POST['dg_use_billing_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['dg_use_billing_nonce'] ) )
		: '';

	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'dg_use_billing_instead' ) ) {
		wc_add_notice( __( 'Security check failed. Please try again.', 'dragon-glow' ), 'error' );
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	$user_id = get_current_user_id();
	$billing = dg_get_account_address_data( $user_id, 'billing' );

	if ( null === $billing ) {
		wc_add_notice(
			__( 'Add a billing address before removing your shipping address.', 'dragon-glow' ),
			'error'
		);
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	$shipping = dg_get_account_address_data( $user_id, 'shipping' );
	if ( null === $shipping ) {
		wc_add_notice(
			__( 'Orders already ship to your billing address.', 'dragon-glow' ),
			'notice'
		);
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	if ( ! dg_clear_customer_shipping_address( $user_id ) ) {
		wc_add_notice(
			__( 'Could not update your shipping address. Please try again.', 'dragon-glow' ),
			'error'
		);
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	wc_add_notice(
		__( 'Shipping address removed. Orders will ship to your billing address.', 'dragon-glow' ),
		'success'
	);
	wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
	exit;
}
add_action( 'template_redirect', 'dg_handle_use_billing_instead', 5 );

