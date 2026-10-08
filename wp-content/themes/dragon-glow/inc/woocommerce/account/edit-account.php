<?php
/**
 * Dragon Glow — My Account: Edit Account Form
 *
 * Renders the `/my-account/edit-account/` endpoint — wraps WC's
 * `myaccount/form-edit-account.php` (unchanged save handler:
 * `WC_Form_Handler::save_account_details`, nonce + validation + redirect).
 *
 * The read-only "Account details" view (Stat Tiles Overview) lives in the
 * sibling file `account-details.php`.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the "Edit account" URL (read-only view → edit form).
 *
 * @return string
 */
function dg_account_edit_url(): string {
	return dg_account_endpoint_url( 'edit-account' );
}

/**
 * Render the edit-account form view.
 *
 * Wraps WC's `myaccount/form-edit-account.php` — theme override includes
 * custom fields (phone, company, job title, date of birth, gender).
 * Extended save handler in `dg_save_account_custom_fields()`.
 *
 * @return void
 */
function dg_render_account_edit_form(): void {
	if ( function_exists( 'wc_get_template' ) ) {
		wc_get_template( 'myaccount/form-edit-account.php' );
	}
}

/**
 * Save custom account fields (phone, company, job title, date of birth, gender).
 *
 * Hooks into WC's account details save flow. Sanitizes and validates custom
 * fields, then saves to user meta. Mirrors WC's validation pattern.
 *
 * @param int $user_id User ID.
 * @return void
 */
function dg_save_account_custom_fields( int $user_id ): void {
	// Guard: only process when form is submitted with correct nonce.
	if ( ! isset( $_POST['save-account-details-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save-account-details-nonce'] ) ), 'save_account_details' ) ) {
		return;
	}

	// Phone (required for delivery coordination).
	if ( isset( $_POST['billing_phone'] ) ) {
		update_user_meta( $user_id, 'billing_phone', sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) );
	}

	// Company name (optional, B2B).
	if ( isset( $_POST['billing_company'] ) ) {
		update_user_meta( $user_id, 'billing_company', sanitize_text_field( wp_unslash( $_POST['billing_company'] ) ) );
	}

	// Job title (optional, B2B).
	if ( isset( $_POST['dg_job_title'] ) ) {
		update_user_meta( $user_id, 'dg_job_title', sanitize_text_field( wp_unslash( $_POST['dg_job_title'] ) ) );
	}

	// Date of birth (optional, format: YYYY-MM-DD).
	if ( isset( $_POST['dg_date_of_birth'] ) ) {
		$dob = sanitize_text_field( wp_unslash( $_POST['dg_date_of_birth'] ) );
		// Validate date format.
		if ( '' === $dob || preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dob ) ) {
			update_user_meta( $user_id, 'dg_date_of_birth', $dob );
		}
	}

	// Gender (optional, enum: female|male|non-binary|other).
	if ( isset( $_POST['dg_gender'] ) ) {
		$gender = sanitize_text_field( wp_unslash( $_POST['dg_gender'] ) );
		$allowed_values = array( '', 'female', 'male', 'non-binary', 'other' );
		if ( in_array( $gender, $allowed_values, true ) ) {
			update_user_meta( $user_id, 'dg_gender', $gender );
		}
	}
}
add_action( 'woocommerce_save_account_details', 'dg_save_account_custom_fields' );

/**
 * Render the `/my-account/edit-account/` panel — the actual edit form.
 *
 * Header shows "Back to account details", routing back to the read-only
 * `/my-account/account-details/` view instead of the dashboard.
 *
 * @return void
 */
function dg_render_account_edit_panel(): void {
	?>
	<section class="dg-account-panel" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Account details', 'dragon-glow' ); ?></h2>
			<a href="<?php echo esc_url( dg_account_details_url() ); ?>" class="dg-account-panel__link">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Back to account details', 'dragon-glow' ); ?>
			</a>
		</header>

		<?php
		// Success/error notice after WC save redirects back here (mirrors
		// address-details.php). account.js' WC-notice → toast bridge reads
		// this block and converts it into a toast on page load / AJAX swap.
		if ( function_exists( 'wc_print_notices' ) ) {
			echo '<div class="dg-account-notices">';
			wc_print_notices();
			echo '</div>';
		}

		dg_render_account_edit_form();
		?>
	</section>
	<?php
}
