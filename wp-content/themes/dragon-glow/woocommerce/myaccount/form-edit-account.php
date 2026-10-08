<?php
/**
 * Dragon Glow — Edit Account Form (WooCommerce Template Override)
 *
 * Professional enterprise-grade account edit form with comprehensive fields:
 * personal info, contact details, company info (B2B), demographics (optional),
 * and password management. Follows luxury cosmetics industry standards
 * (Sephora, Estée Lauder, LVMH Beauty) and B2B best practices.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();

do_action( 'woocommerce_before_edit_account_form' ); ?>

<form class="dg-account-form woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

	<!-- ═══════════════════════════════════════════════════════════════════════════
	SECTION 1: PERSONAL INFORMATION
	═══════════════════════════════════════════════════════════════════════════ -->

	<div class="dg-account-form__section">
		<h3 class="dg-account-form__section-title">
			<span class="material-symbols-outlined" aria-hidden="true">badge</span>
			<?php esc_html_e( 'Personal information', 'dragon-glow' ); ?>
		</h3>

		<div class="dg-account-form__row">
			<div class="dg-account-form__field">
				<label for="account_first_name" class="dg-account-form__label">
					<?php esc_html_e( 'First name', 'dragon-glow' ); ?>
					<abbr class="required" title="<?php esc_attr_e( 'required', 'dragon-glow' ); ?>">*</abbr>
				</label>
				<input
					type="text"
					class="dg-account-form__input woocommerce-Input woocommerce-Input--text input-text"
					name="account_first_name"
					id="account_first_name"
					autocomplete="given-name"
					value="<?php echo esc_attr( $user->first_name ); ?>"
					required
					aria-required="true"
				/>
			</div>

			<div class="dg-account-form__field">
				<label for="account_last_name" class="dg-account-form__label">
					<?php esc_html_e( 'Last name', 'dragon-glow' ); ?>
					<abbr class="required" title="<?php esc_attr_e( 'required', 'dragon-glow' ); ?>">*</abbr>
				</label>
				<input
					type="text"
					class="dg-account-form__input woocommerce-Input woocommerce-Input--text input-text"
					name="account_last_name"
					id="account_last_name"
					autocomplete="family-name"
					value="<?php echo esc_attr( $user->last_name ); ?>"
					required
					aria-required="true"
				/>
			</div>
		</div>

		<div class="dg-account-form__field">
			<label for="account_display_name" class="dg-account-form__label">
				<?php esc_html_e( 'Display name', 'dragon-glow' ); ?>
				<abbr class="required" title="<?php esc_attr_e( 'required', 'dragon-glow' ); ?>">*</abbr>
			</label>
			<input
				type="text"
				class="dg-account-form__input woocommerce-Input woocommerce-Input--text input-text"
				name="account_display_name"
				id="account_display_name"
				value="<?php echo esc_attr( $user->display_name ); ?>"
				required
				aria-required="true"
			/>
			<span class="dg-account-form__help-text">
				<?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'dragon-glow' ); ?>
			</span>
		</div>

		<div class="dg-account-form__row">
			<?php
			$dg_dob_value   = get_user_meta( $user->ID, 'dg_date_of_birth', true );
			$dg_dob_parts   = ( '' !== $dg_dob_value ) ? array_map( 'intval', explode( '-', $dg_dob_value ) ) : array();
			$dg_dob_year    = isset( $dg_dob_parts[0] ) ? $dg_dob_parts[0] : 0;
			$dg_dob_month   = isset( $dg_dob_parts[1] ) ? $dg_dob_parts[1] : 0;
			$dg_dob_day     = isset( $dg_dob_parts[2] ) ? $dg_dob_parts[2] : 0;
			$dg_dob_max_year = (int) gmdate( 'Y' );
			?>
			<div class="dg-account-form__field">
				<label id="dg_date_of_birth_label" class="dg-account-form__label">
					<?php esc_html_e( 'Date of birth', 'dragon-glow' ); ?>
					<span class="dg-account-form__optional"><?php esc_html_e( '(optional)', 'dragon-glow' ); ?></span>
				</label>

				<input type="hidden" name="dg_date_of_birth" id="dg_date_of_birth" value="<?php echo esc_attr( $dg_dob_value ); ?>" />

				<div
					class="dg-dob-wheel"
					data-dob-wheel
					data-dob-day="<?php echo esc_attr( $dg_dob_day ); ?>"
					data-dob-month="<?php echo esc_attr( $dg_dob_month ); ?>"
					data-dob-year="<?php echo esc_attr( $dg_dob_year ); ?>"
					data-dob-max-year="<?php echo esc_attr( $dg_dob_max_year ); ?>"
					role="group"
					aria-labelledby="dg_date_of_birth_label"
				>
					<div class="dg-dob-wheel__row">
						<div class="dg-dob-wheel__col" data-dob-col="day" tabindex="0" role="listbox" aria-label="<?php esc_attr_e( 'Day', 'dragon-glow' ); ?>"></div>
						<div class="dg-dob-wheel__col dg-dob-wheel__col--month" data-dob-col="month" tabindex="0" role="listbox" aria-label="<?php esc_attr_e( 'Month', 'dragon-glow' ); ?>"></div>
						<div class="dg-dob-wheel__col" data-dob-col="year" tabindex="0" role="listbox" aria-label="<?php esc_attr_e( 'Year', 'dragon-glow' ); ?>"></div>
					</div>
				</div>

				<span class="dg-account-form__help-text">
					<?php esc_html_e( 'Receive special offers on your birthday', 'dragon-glow' ); ?>
				</span>
			</div>

			<div class="dg-account-form__field">
				<label for="dg_gender" class="dg-account-form__label">
					<?php esc_html_e( 'Gender', 'dragon-glow' ); ?>
					<span class="dg-account-form__optional"><?php esc_html_e( '(optional)', 'dragon-glow' ); ?></span>
				</label>
				<select
					name="dg_gender"
					id="dg_gender"
					class="dg-account-form__select"
				>
					<option value=""><?php esc_html_e( 'Prefer not to say', 'dragon-glow' ); ?></option>
					<option value="female" <?php selected( get_user_meta( $user->ID, 'dg_gender', true ), 'female' ); ?>><?php esc_html_e( 'Female', 'dragon-glow' ); ?></option>
					<option value="male" <?php selected( get_user_meta( $user->ID, 'dg_gender', true ), 'male' ); ?>><?php esc_html_e( 'Male', 'dragon-glow' ); ?></option>
					<option value="non-binary" <?php selected( get_user_meta( $user->ID, 'dg_gender', true ), 'non-binary' ); ?>><?php esc_html_e( 'Non-binary', 'dragon-glow' ); ?></option>
					<option value="other" <?php selected( get_user_meta( $user->ID, 'dg_gender', true ), 'other' ); ?>><?php esc_html_e( 'Other', 'dragon-glow' ); ?></option>
				</select>
				<span class="dg-account-form__help-text">
					<?php esc_html_e( 'Helps us personalize your shopping experience', 'dragon-glow' ); ?>
				</span>
			</div>
		</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════════════════════════
	SECTION 2: CONTACT INFORMATION
	═══════════════════════════════════════════════════════════════════════════ -->

	<div class="dg-account-form__section">
		<h3 class="dg-account-form__section-title">
			<span class="material-symbols-outlined" aria-hidden="true">contact_mail</span>
			<?php esc_html_e( 'Contact information', 'dragon-glow' ); ?>
		</h3>

		<div class="dg-account-form__field">
			<label for="account_email" class="dg-account-form__label">
				<?php esc_html_e( 'Email address', 'dragon-glow' ); ?>
				<abbr class="required" title="<?php esc_attr_e( 'required', 'dragon-glow' ); ?>">*</abbr>
			</label>
			<input
				type="email"
				class="dg-account-form__input woocommerce-Input woocommerce-Input--email input-text"
				name="account_email"
				id="account_email"
				autocomplete="email"
				value="<?php echo esc_attr( $user->user_email ); ?>"
				required
				aria-required="true"
			/>
			<span class="dg-account-form__help-text">
				<?php esc_html_e( 'Used for order confirmation and account notifications', 'dragon-glow' ); ?>
			</span>
		</div>

		<div class="dg-account-form__field">
			<label for="billing_phone" class="dg-account-form__label">
				<?php esc_html_e( 'Phone number', 'dragon-glow' ); ?>
				<abbr class="required" title="<?php esc_attr_e( 'required', 'dragon-glow' ); ?>">*</abbr>
			</label>
			<input
				type="tel"
				class="dg-account-form__input"
				name="billing_phone"
				id="billing_phone"
				autocomplete="tel"
				value="<?php echo esc_attr( get_user_meta( $user->ID, 'billing_phone', true ) ); ?>"
				required
				aria-required="true"
			/>
			<span class="dg-account-form__help-text">
				<?php esc_html_e( 'Required for delivery coordination and order updates', 'dragon-glow' ); ?>
			</span>
		</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════════════════════════
	SECTION 3: COMPANY INFORMATION (B2B)
	═══════════════════════════════════════════════════════════════════════════ -->

	<div class="dg-account-form__section">
		<h3 class="dg-account-form__section-title">
			<span class="material-symbols-outlined" aria-hidden="true">business</span>
			<?php esc_html_e( 'Company information', 'dragon-glow' ); ?>
			<span class="dg-account-form__section-subtitle"><?php esc_html_e( '(for business accounts)', 'dragon-glow' ); ?></span>
		</h3>

		<div class="dg-account-form__row">
			<div class="dg-account-form__field">
				<label for="billing_company" class="dg-account-form__label">
					<?php esc_html_e( 'Company name', 'dragon-glow' ); ?>
					<span class="dg-account-form__optional"><?php esc_html_e( '(optional)', 'dragon-glow' ); ?></span>
				</label>
				<input
					type="text"
					class="dg-account-form__input"
					name="billing_company"
					id="billing_company"
					autocomplete="organization"
					value="<?php echo esc_attr( get_user_meta( $user->ID, 'billing_company', true ) ); ?>"
				/>
			</div>

			<div class="dg-account-form__field">
				<label for="dg_job_title" class="dg-account-form__label">
					<?php esc_html_e( 'Job title', 'dragon-glow' ); ?>
					<span class="dg-account-form__optional"><?php esc_html_e( '(optional)', 'dragon-glow' ); ?></span>
				</label>
				<input
					type="text"
					class="dg-account-form__input"
					name="dg_job_title"
					id="dg_job_title"
					autocomplete="organization-title"
					value="<?php echo esc_attr( get_user_meta( $user->ID, 'dg_job_title', true ) ); ?>"
					placeholder="<?php esc_attr_e( 'e.g., Spa Manager, Salon Owner', 'dragon-glow' ); ?>"
				/>
			</div>
		</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════════════════════════
	SECTION 4: PASSWORD MANAGEMENT
	═══════════════════════════════════════════════════════════════════════════ -->

	<div class="dg-account-form__section dg-account-form__section--password">
		<h3 class="dg-account-form__section-title">
			<span class="material-symbols-outlined" aria-hidden="true">lock</span>
			<?php esc_html_e( 'Password change', 'dragon-glow' ); ?>
		</h3>

		<p class="dg-account-form__password-note">
			<?php esc_html_e( 'Leave blank to keep your current password', 'dragon-glow' ); ?>
		</p>

		<div class="dg-account-form__field">
			<label for="password_current" class="dg-account-form__label">
				<?php esc_html_e( 'Current password', 'dragon-glow' ); ?>
				<span class="dg-account-form__optional"><?php esc_html_e( '(leave blank to leave unchanged)', 'dragon-glow' ); ?></span>
			</label>
			<input
				type="password"
				class="dg-account-form__input woocommerce-Input woocommerce-Input--password input-text"
				name="password_current"
				id="password_current"
				autocomplete="current-password"
			/>
		</div>

		<div class="dg-account-form__row">
			<div class="dg-account-form__field">
				<label for="password_1" class="dg-account-form__label">
					<?php esc_html_e( 'New password', 'dragon-glow' ); ?>
					<span class="dg-account-form__optional"><?php esc_html_e( '(leave blank to leave unchanged)', 'dragon-glow' ); ?></span>
				</label>
				<input
					type="password"
					class="dg-account-form__input woocommerce-Input woocommerce-Input--password input-text"
					name="password_1"
					id="password_1"
					autocomplete="new-password"
				/>
			</div>

			<div class="dg-account-form__field">
				<label for="password_2" class="dg-account-form__label">
					<?php esc_html_e( 'Confirm new password', 'dragon-glow' ); ?>
					<span class="dg-account-form__optional"><?php esc_html_e( '(leave blank to leave unchanged)', 'dragon-glow' ); ?></span>
				</label>
				<input
					type="password"
					class="dg-account-form__input woocommerce-Input woocommerce-Input--password input-text"
					name="password_2"
					id="password_2"
					autocomplete="new-password"
				/>
			</div>
		</div>
	</div>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

	<!-- ═══════════════════════════════════════════════════════════════════════════
	FORM ACTIONS
	═══════════════════════════════════════════════════════════════════════════ -->

	<div class="dg-account-form__actions">
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<button type="submit" class="dg-btn dg-btn--primary dg-account-form__submit woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'dragon-glow' ); ?>">
			<span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
			<?php esc_html_e( 'Save changes', 'dragon-glow' ); ?>
		</button>
		<input type="hidden" name="action" value="save_account_details" />
	</div>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>

</form>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
