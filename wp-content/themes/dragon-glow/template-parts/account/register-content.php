<?php
/**
 * Dragon Glow — Registration Page Content
 *
 * Custom registration page template for /my-account/register/ endpoint.
 * Design parity with stitch_dragon_glow_auth_portal/đăng-ký/.
 *
 * Layout: 2-column split (showcase image left, form right).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Redirect if already logged in.
if ( is_user_logged_in() ) {
	wp_safe_redirect( dg_account_endpoint_url( '' ) );
	exit;
}

// Check if registration is enabled.
$registration_enabled = get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) === 'yes'
	|| get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes';

if ( ! $registration_enabled ) {
	wc_add_notice( __( 'Registration is currently disabled.', 'dragon-glow' ), 'error' );
	wp_safe_redirect( dg_account_endpoint_url( '' ) );
	exit;
}
?>

<div class="dg-register">
	<div class="dg-register__container">
		
		<!-- Showcase Column -->
		<div class="dg-register__showcase">
			<img 
				src="<?php echo esc_url( DG_URI . '/assets/images/account-auth/register.jpg' ); ?>" 
				alt="<?php esc_attr_e( 'Dragon Glow Atelier', 'dragon-glow' ); ?>"
				class="dg-register__showcase-image"
			/>
		</div>

		<!-- Form Column -->
		<div class="dg-register__form-wrapper">
			<div class="dg-register__form-inner">
				
				<!-- Header -->
				<div class="dg-register__header">
					<h1 class="dg-register__title">
						<?php esc_html_e( 'Request Atelier Initiation', 'dragon-glow' ); ?>
					</h1>
					<p class="dg-register__subtitle">
						<?php esc_html_e( 'Join our exclusive circle of beauty connoisseurs', 'dragon-glow' ); ?>
					</p>
				</div>

				<!-- WooCommerce Notices -->
				<?php wc_print_notices(); ?>

				<!-- Registration Form -->
				<form method="post" class="dg-register__form" novalidate>
					
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

					<?php
					// Persist redirect_to through form submission (enterprise "intended
					// URL" pattern — same mechanism used by wp-login.php). Without this
					// hidden input, POST strips the query string and the user lands on
					// the My Account dashboard instead of returning to the page they
					// came from (e.g. /shop/ -> Sign Up -> submit -> back to /shop/).
					if ( isset( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- redirect target only, validated via wp_validate_redirect() in dg_account_safe_redirect_target().
						$redirect_to = wp_unslash( $_REQUEST['redirect_to'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized by esc_url() below.
						if ( is_string( $redirect_to ) && '' !== $redirect_to ) {
							?>
							<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>" />
							<?php
						}
					}
					?>

					<!-- Name Row -->
					<div class="dg-register__row">
						<div class="dg-register__field">
							<label for="billing_first_name" class="dg-register__label">
								<?php esc_html_e( 'First Name', 'dragon-glow' ); ?>
								<span class="dg-register__required">*</span>
							</label>
							<input 
								type="text" 
								name="billing_first_name" 
								id="billing_first_name" 
								class="dg-register__input" 
								required 
								autocomplete="given-name"
								value="<?php echo isset( $_POST['billing_first_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) ) : ''; ?>"
							/>
						</div>

						<div class="dg-register__field">
							<label for="billing_last_name" class="dg-register__label">
								<?php esc_html_e( 'Last Name', 'dragon-glow' ); ?>
								<span class="dg-register__required">*</span>
							</label>
							<input 
								type="text" 
								name="billing_last_name" 
								id="billing_last_name" 
								class="dg-register__input" 
								required 
								autocomplete="family-name"
								value="<?php echo isset( $_POST['billing_last_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) ) : ''; ?>"
							/>
						</div>
					</div>

					<!-- Email -->
					<div class="dg-register__field">
						<label for="reg_email" class="dg-register__label">
							<?php esc_html_e( 'Email Address', 'dragon-glow' ); ?>
							<span class="dg-register__required">*</span>
						</label>
						<input 
							type="email" 
							name="email" 
							id="reg_email" 
							class="dg-register__input" 
							required 
							autocomplete="email"
							value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; ?>"
						/>
					</div>

					<!-- Password -->
					<div class="dg-register__field">
						<label for="reg_password" class="dg-register__label">
							<?php esc_html_e( 'Password', 'dragon-glow' ); ?>
							<span class="dg-register__required">*</span>
						</label>
						<div class="dg-register__password-wrapper">
							<input 
								type="password" 
								name="password" 
								id="reg_password" 
								class="dg-register__input dg-register__input--password" 
								required 
								autocomplete="new-password"
								minlength="8"
							/>
							<button 
								type="button" 
								class="dg-register__password-toggle" 
								aria-label="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
							>
								<span class="material-symbols-outlined">visibility</span>
							</button>
						</div>
						<div class="dg-register__password-strength" id="password-strength">
							<div class="dg-register__strength-bar">
								<div class="dg-register__strength-fill"></div>
							</div>
							<p class="dg-register__strength-text"></p>
						</div>
						<p class="dg-register__hint">
							<?php esc_html_e( 'Minimum 8 characters with uppercase, lowercase, numbers, and symbols', 'dragon-glow' ); ?>
						</p>
					</div>

					<!-- Skin Aspiration -->
					<div class="dg-register__field">
						<label for="skin_aspiration" class="dg-register__label">
							<?php esc_html_e( 'Skin Aspiration', 'dragon-glow' ); ?>
							<span class="dg-register__optional"><?php esc_html_e( '(Optional)', 'dragon-glow' ); ?></span>
						</label>
						<select name="skin_aspiration" id="skin_aspiration" class="dg-register__select">
							<option value=""><?php esc_html_e( 'Select your skin goal', 'dragon-glow' ); ?></option>
							<option value="radiance" <?php selected( isset( $_POST['skin_aspiration'] ) && 'radiance' === $_POST['skin_aspiration'] ); ?>>
								<?php esc_html_e( 'Radiant Luminosity', 'dragon-glow' ); ?>
							</option>
							<option value="hydration" <?php selected( isset( $_POST['skin_aspiration'] ) && 'hydration' === $_POST['skin_aspiration'] ); ?>>
								<?php esc_html_e( 'Deep Hydration', 'dragon-glow' ); ?>
							</option>
							<option value="anti-aging" <?php selected( isset( $_POST['skin_aspiration'] ) && 'anti-aging' === $_POST['skin_aspiration'] ); ?>>
								<?php esc_html_e( 'Age Defiance', 'dragon-glow' ); ?>
							</option>
							<option value="brightening" <?php selected( isset( $_POST['skin_aspiration'] ) && 'brightening' === $_POST['skin_aspiration'] ); ?>>
								<?php esc_html_e( 'Brightening & Even Tone', 'dragon-glow' ); ?>
							</option>
							<option value="sensitive" <?php selected( isset( $_POST['skin_aspiration'] ) && 'sensitive' === $_POST['skin_aspiration'] ); ?>>
								<?php esc_html_e( 'Sensitive Skin Care', 'dragon-glow' ); ?>
							</option>
						</select>
					</div>

					<!-- Newsletter -->
					<div class="dg-register__checkbox-wrapper">
						<input 
							type="checkbox" 
							name="newsletter" 
							id="newsletter" 
							class="dg-register__checkbox"
							<?php checked( isset( $_POST['newsletter'] ) ); ?>
						/>
						<label for="newsletter" class="dg-register__checkbox-label">
							<?php esc_html_e( 'Yes, inscribe me in the Dragon Glow chronicle for exclusive rituals and illuminations', 'dragon-glow' ); ?>
						</label>
					</div>

					<!-- Terms -->
					<div class="dg-register__checkbox-wrapper">
						<input 
							type="checkbox" 
							name="terms" 
							id="terms" 
							class="dg-register__checkbox" 
							required
							<?php checked( isset( $_POST['terms'] ) ); ?>
						/>
						<label for="terms" class="dg-register__checkbox-label">
							<?php
							printf(
								/* translators: 1: Terms link, 2: Privacy link */
								esc_html__( 'I accept the %1$s and %2$s', 'dragon-glow' ),
								'<a href="' . esc_url( home_url( '/terms-of-service/' ) ) . '" target="_blank" class="dg-register__link">' . esc_html__( 'Terms of Service', 'dragon-glow' ) . '</a>',
								'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" target="_blank" class="dg-register__link">' . esc_html__( 'Privacy Policy', 'dragon-glow' ) . '</a>'
							);
							?>
							<span class="dg-register__required">*</span>
						</label>
					</div>

					<!-- Submit Button -->
					<button type="submit" name="register" class="dg-register__submit">
						<?php esc_html_e( 'Begin Your Journey', 'dragon-glow' ); ?>
					</button>

					<!-- Sign In Link -->
					<p class="dg-register__signin-prompt">
						<?php esc_html_e( 'Already inscribed?', 'dragon-glow' ); ?>
						<?php
						// Preserve redirect_to when switching between register → sign in (same
						// "intended URL" pattern — if user came from /shop/ to /register/, and
						// clicks "Already inscribed? Sign In", they should land back on /shop/
						// after signing in, not on the dashboard).
						$signin_url = dg_account_endpoint_url( '' );
						if ( isset( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- redirect target only, validated server-side.
							$redirect_to = wp_unslash( $_REQUEST['redirect_to'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized by add_query_arg() below.
							if ( is_string( $redirect_to ) && '' !== $redirect_to ) {
								$signin_url = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $signin_url );
							}
						}
						?>
						<a href="<?php echo esc_url( $signin_url ); ?>" class="dg-register__signin-link">
							<?php esc_html_e( 'Sign In', 'dragon-glow' ); ?>
						</a>
					</p>

				</form>

			</div>
		</div>

	</div>
</div>
