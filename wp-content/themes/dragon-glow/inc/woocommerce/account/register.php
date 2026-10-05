<?php
/**
 * Dragon Glow — My Account: Full Registration Page
 *
 * Renders the full `/my-account/register/` endpoint page (Heritage Atelier
 * design, 2-column layout). Port of stitch_dragon_glow_auth_portal/đăng-ký
 * reference — 100% visual match. Left column: product showcase (arched
 * frame + privilege cards). Right column: registration form (first name,
 * last name, email, password with strength indicator, aspiration dropdown,
 * checkboxes, social buttons).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the full registration page.
 *
 * @return void
 */
function dg_render_account_register_page(): void {
	$showcase_image = get_theme_file_uri( 'assets/images/account-auth/register.jpg' );
	// "Intended URL" the user was on before hitting the register page —
	// mirrors dg_render_account_signed_out() in login.php. Echoed back as a
	// hidden field so it survives the POST round-trip (incl. re-renders
	// after a validation error).
	$redirect_to = dg_account_safe_redirect_target();
	?>
	<!-- Viewport outer double-gold pinstripe frame -->
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--outer" aria-hidden="true"></div>
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--inner" aria-hidden="true"></div>

	<!-- Classical corner flourishes -->
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tr" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--bl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--br" aria-hidden="true">&#10022;</span>

	<main class="dg-account dg-account--register" id="main-content">
		<div class="dg-account-auth dg-account-auth--register">

			<!-- Symmetrical Classical Crest Header -->
			<header class="dg-account-auth__crest-header">
				<div class="dg-account-auth__crest-ornaments">
					<span class="dg-account-auth__crest-line" aria-hidden="true"></span>
					<div class="dg-account-auth__crest-badge">
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
						<span class="dg-account-auth__crest-text"><?php esc_html_e( 'Imperial Collection ', 'dragon-glow' ); ?></span>
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
					</div>
					<span class="dg-account-auth__crest-line" aria-hidden="true"></span>
				</div>
				<div class="dg-account-auth__brand-link">
					<h1 class="dg-account-auth__brand-title"><?php esc_html_e( 'Dragon Glow', 'dragon-glow' ); ?></h1>
					<div class="dg-account-auth__brand-meta">
						<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
						<p class="dg-account-auth__brand-tagline">
							<?php esc_html_e( 'Est. 2026  •  San Francisco  •  New York', 'dragon-glow' ); ?>
						</p>
						<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
					</div>
				</div>
			</header>

			<div class="dg-account-auth__panel dg-account-auth__panel--register">
				<div class="dg-account-auth__panel-inner">

					<!-- Ambient golden glow wash -->
					<div class="dg-account-auth__glow dg-account-auth__glow--tl" aria-hidden="true"></div>
					<div class="dg-account-auth__glow dg-account-auth__glow--br" aria-hidden="true"></div>

					<!-- Toast notification (shown after successful registration via JS) -->
					<div class="dg-account-auth__toast" id="dg-register-toast" hidden>
						<span class="material-symbols-outlined">verified</span>
						<span class="dg-account-auth__toast-msg" id="dg-register-toast-msg">
							<?php esc_html_e( 'Account created successfully.', 'dragon-glow' ); ?>
						</span>
					</div>

					<div class="dg-account-auth__panel-grid">

						<!-- LEFT COLUMN: Heritage Ritual Showcase -->
						<div class="dg-account-auth__showcase-col">

							<!-- Column separator (desktop) -->
							<div class="dg-account-auth__col-separator" aria-hidden="true"></div>

							<!-- Column header -->
							<div class="dg-account-auth__showcase-header">
								<div class="dg-account-auth__showcase-meta">
									<span class="dg-account-auth__eyebrow"><?php esc_html_e( 'IV • Registration', 'dragon-glow' ); ?></span>
									<span class="dg-account-auth__sanctuary-badge">
										<span class="dg-account-auth__pulse-dot" aria-hidden="true"></span>
										<?php esc_html_e( 'Member Benefits', 'dragon-glow' ); ?>
									</span>
								</div>
							<h2 class="dg-account-auth__showcase-title">
								<?php esc_html_e( 'Welcome to Dragon Glow', 'dragon-glow' ); ?>
							</h2>
								<span class="dg-account-auth__title-rule" aria-hidden="true"></span>
							</div>

							<!-- Arched Roman silhouette showcase -->
							<div class="dg-account-auth__arched-frame">
								<!-- Crown motif -->
								<div class="dg-account-auth__crown-motif">
									<span aria-hidden="true">&#10023;</span>
									<span class="material-symbols-outlined">crown</span>
									<span aria-hidden="true">&#10023;</span>
								</div>

								<!-- Roman arch picture frame -->
								<div class="dg-account-auth__arch-border">
									<div class="dg-account-auth__arch-inner">
										<img
											src="<?php echo esc_url( $showcase_image ); ?>"
											alt="<?php esc_attr_e( 'Dragon Glow luxury skincare golden serum', 'dragon-glow' ); ?>"
											class="dg-account-auth__arch-img" />
										<div class="dg-account-auth__arch-overlay" aria-hidden="true"></div>
										<div class="dg-account-auth__arch-label">
									<span class="dg-account-auth__arch-eyebrow">
										<?php esc_html_e( 'Signature Collection', 'dragon-glow' ); ?>
									</span>
											<span class="dg-account-auth__arch-name">
												<?php esc_html_e( 'Dragon Elixir Nº 1', 'dragon-glow' ); ?>
											</span>
										</div>
									</div>
								</div>

								<!-- Spec label & poetic epigram -->
								<div class="dg-account-auth__arch-spec">
									<p class="dg-account-auth__arch-spec-label">
										<?php esc_html_e( 'Dragon Elixir Nº 1 — 24K Botanical Golden Serum', 'dragon-glow' ); ?>
									</p>
								<p class="dg-account-auth__arch-epigram">
									&ldquo;<?php esc_html_e( 'Join our exclusive community of beauty enthusiasts.', 'dragon-glow' ); ?>&rdquo;
								</p>
								</div>
							</div>

							<!-- Initiation privileges: 3 gilded cards -->
							<div class="dg-account-auth__privileges">
								<div class="dg-account-auth__privilege-card">
									<div class="dg-account-auth__privilege-num">I</div>
									<div class="dg-account-auth__privilege-content">
									<div class="dg-account-auth__privilege-header">
										<h4 class="dg-account-auth__privilege-title"><?php esc_html_e( 'Welcome Gift', 'dragon-glow' ); ?></h4>
										<span class="dg-account-auth__privilege-badge">15% Off</span>
									</div>
									<p class="dg-account-auth__privilege-text">
										<?php esc_html_e( 'Code', 'dragon-glow' ); ?>
										<span class="dg-account-auth__privilege-code">RADIANT15</span>
										<?php esc_html_e( 'applied automatically at checkout.', 'dragon-glow' ); ?>
									</p>
									</div>
								</div>

								<div class="dg-account-auth__privilege-card">
									<div class="dg-account-auth__privilege-num">II</div>
								<div class="dg-account-auth__privilege-content">
									<h4 class="dg-account-auth__privilege-title"><?php esc_html_e( 'Exclusive Access', 'dragon-glow' ); ?></h4>
									<p class="dg-account-auth__privilege-text">
										<?php esc_html_e( 'Early access to new launches and limited edition collections.', 'dragon-glow' ); ?>
									</p>
								</div>
								</div>

								<div class="dg-account-auth__privilege-card">
									<div class="dg-account-auth__privilege-num">III</div>
								<div class="dg-account-auth__privilege-content">
									<h4 class="dg-account-auth__privilege-title"><?php esc_html_e( 'Personalized Consultation', 'dragon-glow' ); ?></h4>
									<p class="dg-account-auth__privilege-text">
										<?php esc_html_e( 'Complimentary skin analysis and personalized skincare routine.', 'dragon-glow' ); ?>
									</p>
								</div>
								</div>
							</div>

						</div>

						<!-- RIGHT COLUMN: Initiation & Registration Form -->
						<div class="dg-account-auth__form-col">

							<!-- Section heading -->
							<div class="dg-account-auth__form-header">
								<div class="dg-account-auth__form-eyebrow">
									<span aria-hidden="true">&#10023;</span>
									<span><?php esc_html_e( 'New Member Registration', 'dragon-glow' ); ?></span>
									<span aria-hidden="true">&#10023;</span>
								</div>
							<h2 class="dg-account-auth__form-title">
								<?php esc_html_e( 'Create Your Account', 'dragon-glow' ); ?>
							</h2>
						<p class="dg-account-auth__form-subtitle">
							<?php esc_html_e( 'Join Dragon Glow to access exclusive products, member-only offers, and personalized skincare recommendations.', 'dragon-glow' ); ?>
						</p>
							</div>

							<?php wc_print_notices(); ?>

							<!-- Registration form -->
							<form method="post" class="dg-register-form" action="<?php echo esc_url( dg_account_endpoint_url( 'register' ) ); ?>" novalidate>

								<?php do_action( 'woocommerce_register_form_start' ); ?>

								<!-- Row: First & Last Name -->
								<div class="dg-register-form__row">
									<div class="dg-register-form__field">
										<label for="dg-reg-first-name" class="dg-register-form__label">
											<?php esc_html_e( 'First Name', 'dragon-glow' ); ?>
											<span class="dg-register-form__required">*</span>
										</label>
										<div class="dg-register-form__input-wrap">
											<input
												type="text"
												class="dg-register-form__input"
												name="billing_first_name"
												id="dg-reg-first-name"
												placeholder="<?php esc_attr_e( 'Seraphina', 'dragon-glow' ); ?>"
												required
												value="<?php echo isset( $_POST['billing_first_name'] ) ? esc_attr( wp_unslash( $_POST['billing_first_name'] ) ) : ''; // phpcs:ignore ?>" />
											<span class="material-symbols-outlined dg-register-form__icon">person</span>
										</div>
									</div>

									<div class="dg-register-form__field">
										<label for="dg-reg-last-name" class="dg-register-form__label">
											<?php esc_html_e( 'Last Name', 'dragon-glow' ); ?>
											<span class="dg-register-form__required">*</span>
										</label>
										<div class="dg-register-form__input-wrap">
											<input
												type="text"
												class="dg-register-form__input"
												name="billing_last_name"
												id="dg-reg-last-name"
												placeholder="<?php esc_attr_e( 'Vanderbilt', 'dragon-glow' ); ?>"
												required
												value="<?php echo isset( $_POST['billing_last_name'] ) ? esc_attr( wp_unslash( $_POST['billing_last_name'] ) ) : ''; // phpcs:ignore ?>" />
											<span class="material-symbols-outlined dg-register-form__icon">shield_person</span>
										</div>
									</div>
								</div>

								<!-- Email address -->
								<div class="dg-register-form__field">
									<label for="dg-reg-email" class="dg-register-form__label">
										<?php esc_html_e( 'Client ID or Primary Email', 'dragon-glow' ); ?>
										<span class="dg-register-form__required">*</span>
									</label>
									<div class="dg-register-form__input-wrap">
										<input
											type="email"
											class="dg-register-form__input"
											name="email"
											id="dg-reg-email"
											autocomplete="email"
											placeholder="<?php esc_attr_e( 'patron@maison-dragonglow.com', 'dragon-glow' ); ?>"
											required
											value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore ?>" />
										<span class="material-symbols-outlined dg-register-form__icon">alternate_email</span>
									</div>
								</div>

								<!-- Password cipher & strength indicator -->
							<div class="dg-register-form__field">
								<label for="dg-reg-password" class="dg-register-form__label">
									<?php esc_html_e( 'Password', 'dragon-glow' ); ?>
									<span class="dg-register-form__required">*</span>
								</label>
									<div class="dg-register-form__input-wrap">
										<input
											type="password"
											class="dg-register-form__input"
											name="password"
											id="dg-reg-password"
											autocomplete="new-password"
											placeholder="<?php esc_attr_e( 'Minimum 12 characters with mixed types', 'dragon-glow' ); ?>"
											required
											data-dg-password-strength />
										<button
											type="button"
											class="dg-register-form__toggle"
											aria-label="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
											data-dg-toggle-password="#dg-reg-password">
											<span class="material-symbols-outlined">visibility</span>
										</button>
									</div>

									<!-- Password strength indicator -->
									<div class="dg-register-form__strength">
										<div class="dg-register-form__strength-bars">
											<div class="dg-register-form__strength-bar" data-strength-bar="1"></div>
											<div class="dg-register-form__strength-bar" data-strength-bar="2"></div>
											<div class="dg-register-form__strength-bar" data-strength-bar="3"></div>
											<div class="dg-register-form__strength-bar" data-strength-bar="4"></div>
										</div>
									<span class="dg-register-form__strength-text" data-strength-text>
										<?php esc_html_e( 'Password Strength: Weak', 'dragon-glow' ); ?>
									</span>
									</div>

									<!-- Password requirements checklist -->
									<div class="dg-register-form__requirements">
										<div class="dg-register-form__requirement" data-requirement="length">
											<span class="material-symbols-outlined dg-requirement__icon">radio_button_unchecked</span>
											<span class="dg-requirement__text"><?php esc_html_e( 'At least 12 characters', 'dragon-glow' ); ?></span>
										</div>
										<div class="dg-register-form__requirement" data-requirement="lowercase">
											<span class="material-symbols-outlined dg-requirement__icon">radio_button_unchecked</span>
											<span class="dg-requirement__text"><?php esc_html_e( 'Lowercase letter (a-z)', 'dragon-glow' ); ?></span>
										</div>
										<div class="dg-register-form__requirement" data-requirement="uppercase">
											<span class="material-symbols-outlined dg-requirement__icon">radio_button_unchecked</span>
											<span class="dg-requirement__text"><?php esc_html_e( 'Uppercase letter (A-Z)', 'dragon-glow' ); ?></span>
										</div>
										<div class="dg-register-form__requirement" data-requirement="digit">
											<span class="material-symbols-outlined dg-requirement__icon">radio_button_unchecked</span>
											<span class="dg-requirement__text"><?php esc_html_e( 'Number (0-9)', 'dragon-glow' ); ?></span>
										</div>
										<div class="dg-register-form__requirement" data-requirement="special">
											<span class="material-symbols-outlined dg-requirement__icon">radio_button_unchecked</span>
											<span class="dg-requirement__text"><?php esc_html_e( 'Special character (!@#$%...)', 'dragon-glow' ); ?></span>
										</div>
									</div>

									<!-- Error message (shown on invalid submission) -->
									<div class="dg-register-form__password-error" hidden>
										<span class="material-symbols-outlined">error</span>
										<span><?php esc_html_e( 'Password must be at least Good strength (12+ characters with lowercase, uppercase, number, and special character).', 'dragon-glow' ); ?></span>
									</div>
									</div>

								<!-- Checkboxes -->
								<div class="dg-register-form__checkboxes">
								<label class="dg-register-form__checkbox">
									<input type="checkbox" name="newsletter" value="1" checked />
									<span class="dg-register-form__checkbox-text">
										<?php esc_html_e( 'Subscribe to receive exclusive offers, new product launches, and beauty tips.', 'dragon-glow' ); ?>
									</span>
								</label>

								<label class="dg-register-form__checkbox">
									<input type="checkbox" name="terms" value="1" required />
									<span class="dg-register-form__checkbox-text">
										<?php
										/* translators: 1: Terms of Service link, 2: Privacy Policy link */
										printf(
											__( 'I accept the %1$s and %2$s', 'dragon-glow' ),
											'<a href="' . esc_url( home_url( '/terms-of-service/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Terms of Service', 'dragon-glow' ) . '</a>',
											'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'dragon-glow' ) . '</a>'
										);
										?>
										<span class="dg-register-form__required">*</span>
									</span>
								</label>
								</div>

								<?php
								/**
								 * Hook: woocommerce_register_form
								 *
								 * WooCommerce uses this hook to inject the privacy policy notice.
								 * We remove it to keep the form clean and focused on essential fields only.
								 *
								 * @see wc_registration_privacy_policy_text() in WC core
								 */
								remove_action( 'woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 );
								do_action( 'woocommerce_register_form' );
								?>

								<input type="hidden" name="woocommerce-register-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-register' ) ); ?>" />
								<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

							<!-- Primary submission CTA -->
							<button type="submit" name="register" class="dg-register-form__submit">
								<span class="material-symbols-outlined">verified_user</span>
								<span><?php esc_html_e( 'Create Account & Unlock 15%', 'dragon-glow' ); ?></span>
							</button>

								<?php do_action( 'woocommerce_register_form_end' ); ?>

							</form>

							<!-- Alternative accession divider -->
							<div class="dg-register-form__divider">
								<span class="dg-register-form__divider-line"></span>
								<div class="dg-register-form__divider-text">
									<span>&#10022;</span>
									<span><?php esc_html_e( 'Alternative Accession', 'dragon-glow' ); ?></span>
									<span>&#10022;</span>
								</div>
								<span class="dg-register-form__divider-line"></span>
							</div>

							<!-- Social access buttons -->
							<?php
							$dg_register_google_enabled = dg_social_login_google_enabled();
							$dg_register_apple_enabled  = dg_social_login_apple_enabled();
							?>
							<div class="dg-register-form__social">
								<?php if ( $dg_register_google_enabled ) : ?>
									<a href="<?php echo esc_url( dg_social_login_start_url( 'google' ) ); ?>" class="dg-register-form__social-btn">
								<?php else : ?>
									<button type="button" class="dg-register-form__social-btn" disabled
										title="<?php esc_attr_e( 'Google sign-in is coming soon.', 'dragon-glow' ); ?>">
								<?php endif; ?>
									<svg class="dg-register-form__social-icon" viewBox="0 0 24 24">
										<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
										<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
										<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
										<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
									</svg>
									<span><?php esc_html_e( 'Google', 'dragon-glow' ); ?></span>
								<?php echo $dg_register_google_enabled ? '</a>' : '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static closing tag string, no user input. ?>

								<?php if ( $dg_register_apple_enabled ) : ?>
									<a href="<?php echo esc_url( dg_social_login_start_url( 'apple' ) ); ?>" class="dg-register-form__social-btn">
								<?php else : ?>
									<button type="button" class="dg-register-form__social-btn" disabled
										title="<?php esc_attr_e( 'Apple sign-in is coming soon.', 'dragon-glow' ); ?>">
								<?php endif; ?>
									<svg class="dg-register-form__social-icon" viewBox="0 0 24 24" fill="currentColor">
										<path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.77 1.04-1.85.92-2.92-.91.04-2 .6-2.65 1.37-.56.65-1.06 1.74-.93 2.8 1.02.08 2.04-.51 2.66-1.25z"/>
									</svg>
									<span><?php esc_html_e( 'Apple ID', 'dragon-glow' ); ?></span>
								<?php echo $dg_register_apple_enabled ? '</a>' : '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static closing tag string, no user input. ?>
							</div>

						<!-- Existing member sign-in prompt -->
						<p class="dg-register-form__signin-prompt">
							<?php esc_html_e( 'Already have an account?', 'dragon-glow' ); ?>
							<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-register-form__signin-link">
								<?php esc_html_e( 'Sign In', 'dragon-glow' ); ?>
							</a>
						</p>

						</div>

					</div>

				</div>
			</div>
		</div>
	</main>

	<!-- Classical heritage footer -->
	<footer class="dg-account-auth__heritage-footer">
		<div class="dg-account-auth__heritage-locations">
			<span><?php esc_html_e( 'Heritage Atelier San Francisco', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot">&bull;</span>
			<span><?php esc_html_e( 'Fifth Avenue New York', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot">&bull;</span>
			<span><?php esc_html_e( 'Parisian Archive', 'dragon-glow' ); ?></span>
		</div>
		<div class="dg-account-auth__heritage-copyright">
			<?php
			/* translators: %d: Current year */
			printf(
				esc_html__( '© %d Dragon Glow Cosmetics Inc. All rights reserved.', 'dragon-glow' ),
				(int) gmdate( 'Y' )
			);
			?>
		</div>
	</footer>
	<?php
}
