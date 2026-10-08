<?php
/**
 * Dragon Glow — My Account: Account Details View (Read-Only)
 *
 * Renders the `/my-account/account-details/` endpoint — a read-only
 * "Account details" view (Stat Tiles Overview, matches the Luminous
 * Ethereal design system). The actual edit form lives in `edit-account.php`.
 *
 * The `account-details` rewrite endpoint is registered in
 * `account-details-endpoint.php`.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the "Back to account details" URL (edit form → read-only view).
 *
 * @return string
 */
function dg_account_details_url(): string {
	return dg_account_endpoint_url( 'account-details' );
}

/**
 * Render one read-only field tile (Stat Tiles Overview — Concept 4).
 *
 * @param string $icon  Material Symbols icon name.
 * @param string $label Field label.
 * @param string $value Field value. Empty string renders an em dash.
 * @param string $tone  Icon tone — '' (default gold) | 'verified'.
 * @return void
 */
function dg_render_account_detail_tile( string $icon, string $label, string $value, string $tone = '' ): void {
	?>
	<div class="dg-account-detail-tile">
		<span class="dg-account-detail-tile__icon material-symbols-outlined" data-tone="<?php echo esc_attr( $tone ); ?>" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
		<div class="dg-account-detail-tile__body">
			<p class="dg-account-detail-tile__label"><?php echo esc_html( $label ); ?></p>
			<p class="dg-account-detail-tile__value"><?php echo '' !== $value ? esc_html( $value ) : '—'; ?></p>
		</div>
	</div>
	<?php
}

/**
 * Render the read-only "Account details" view (Stat Tiles Overview).
 *
 * Pulls display data from the current WP_User + user meta — includes all fields
 * from the edit form: first/last name, display name, email, phone, company,
 * job title, date of birth, gender, member-since, membership tier, plus billing
 * and shipping addresses.
 *
 * @param WP_User $customer Current customer.
 * @return void
 */
function dg_render_account_details_view( WP_User $customer ): void {
	$first_name   = (string) $customer->first_name;
	$last_name    = (string) $customer->last_name;
	$full_name    = trim( $first_name . ' ' . $last_name );
	$phone        = (string) get_user_meta( $customer->ID, 'billing_phone', true );
	$company      = (string) get_user_meta( $customer->ID, 'billing_company', true );
	$job_title    = (string) get_user_meta( $customer->ID, 'dg_job_title', true );
	$dob          = (string) get_user_meta( $customer->ID, 'dg_date_of_birth', true );
	$gender       = (string) get_user_meta( $customer->ID, 'dg_gender', true );
	$member_since = date_i18n( 'F Y', strtotime( (string) $customer->user_registered ) );
	$tier         = dg_account_member_tier( (int) $customer->ID );

	// Format date of birth for display.
	$dob_display = '';
	if ( '' !== $dob ) {
		$dob_display = date_i18n( get_option( 'date_format' ), strtotime( $dob ) );
	}

	// Format gender for display.
	$gender_labels = array(
		'female'     => __( 'Female', 'dragon-glow' ),
		'male'       => __( 'Male', 'dragon-glow' ),
		'non-binary' => __( 'Non-binary', 'dragon-glow' ),
		'other'      => __( 'Other', 'dragon-glow' ),
	);
	$gender_display = isset( $gender_labels[ $gender ] ) ? $gender_labels[ $gender ] : '';

	// Get addresses data (reuse function from addresses.php).
	$billing  = dg_get_account_address_data( (int) $customer->ID, 'billing' );
	$shipping = dg_get_account_address_data( (int) $customer->ID, 'shipping' );

	// Format addresses for compact display (single line).
	$billing_display  = $billing ? dg_format_address_compact( $billing ) : '';
	$shipping_display = $shipping ? dg_format_address_compact( $shipping ) : '';

	?>
	<div class="dg-account-detail-grid">
		<?php dg_render_account_detail_tile( 'badge', __( 'Full name', 'dragon-glow' ), $full_name ); ?>
		<?php dg_render_account_detail_tile( 'mail', __( 'Email address', 'dragon-glow' ), $customer->user_email, 'verified' ); ?>
		<?php dg_render_account_detail_tile( 'call', __( 'Phone number', 'dragon-glow' ), $phone ); ?>
		<?php dg_render_account_detail_tile( 'alternate_email', __( 'Display name', 'dragon-glow' ), $customer->display_name ); ?>

		<?php if ( '' !== $dob_display ) : ?>
			<?php dg_render_account_detail_tile( 'cake', __( 'Date of birth', 'dragon-glow' ), $dob_display ); ?>
		<?php endif; ?>

		<?php if ( '' !== $gender_display ) : ?>
			<?php dg_render_account_detail_tile( 'person', __( 'Gender', 'dragon-glow' ), $gender_display ); ?>
		<?php endif; ?>

		<?php if ( '' !== $company ) : ?>
			<?php dg_render_account_detail_tile( 'business', __( 'Company', 'dragon-glow' ), $company ); ?>
		<?php endif; ?>

		<?php if ( '' !== $job_title ) : ?>
			<?php dg_render_account_detail_tile( 'work', __( 'Job title', 'dragon-glow' ), $job_title ); ?>
		<?php endif; ?>

		<?php if ( '' !== $billing_display ) : ?>
			<?php dg_render_account_detail_tile( 'receipt', __( 'Billing address', 'dragon-glow' ), $billing_display ); ?>
		<?php endif; ?>

		<?php if ( '' !== $shipping_display ) : ?>
			<?php dg_render_account_detail_tile( 'local_shipping', __( 'Shipping address', 'dragon-glow' ), $shipping_display ); ?>
		<?php endif; ?>

		<?php dg_render_account_detail_tile( 'calendar_month', __( 'Member since', 'dragon-glow' ), $member_since ); ?>
		<?php dg_render_account_detail_tile( 'workspace_premium', __( 'Membership tier', 'dragon-glow' ), $tier['label'] ); ?>

		<div class="dg-account-detail-tile dg-account-detail-tile--cta">
			<span class="dg-account-detail-tile__icon material-symbols-outlined" aria-hidden="true">location_on</span>
			<p class="dg-account-detail-tile__cta-text">
				<?php esc_html_e( 'Need to update your billing or shipping addresses?', 'dragon-glow' ); ?>
			</p>
			<a href="<?php echo esc_url( add_query_arg( 'address', 'shipping', dg_account_edit_address_url() ) ); ?>" class="dg-btn dg-account-detail-tile__cta-btn">
				<span class="material-symbols-outlined" aria-hidden="true">map</span>
				<?php esc_html_e( 'Edit addresses', 'dragon-glow' ); ?>
			</a>
		</div>

		<div class="dg-account-detail-tile dg-account-detail-tile--cta">
			<span class="dg-account-detail-tile__icon material-symbols-outlined" aria-hidden="true">manage_accounts</span>
			<p class="dg-account-detail-tile__cta-text">
				<?php esc_html_e( 'Need to update your personal information or password?', 'dragon-glow' ); ?>
			</p>
			<a href="<?php echo esc_url( dg_account_edit_url() ); ?>" class="dg-btn dg-account-detail-tile__cta-btn">
				<span class="material-symbols-outlined" aria-hidden="true">edit</span>
				<?php esc_html_e( 'Edit account', 'dragon-glow' ); ?>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Render the `/my-account/account-details/` panel — read-only view.
 *
 * The edit entry point lives in the "Edit account" CTA tile at the bottom
 * of the view (see `dg_render_account_details_view()`), not in the header.
 *
 * @return void
 */
function dg_render_account_details_panel(): void {
	$customer = wp_get_current_user();
	?>
	<section class="dg-account-panel" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Account details', 'dragon-glow' ); ?></h2>
		</header>
		<?php dg_render_account_details_view( $customer ); ?>
	</section>
	<?php
}
