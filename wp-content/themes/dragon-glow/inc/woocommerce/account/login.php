<?php
/**
 * Dragon Glow — My Account: Login (Signed-Out Auth Gate)
 *
 * "Heritage Atelier" auth gate shown when a visitor hits any My Account
 * endpoint while signed out. Split showcase layout (Cormorant Garamond
 * heritage gold styling, scoped to `.dg-login__*` via `assets/css/login.css`,
 * plus the shared `.dg-account-auth__*` chrome also reused by
 * register.php). Left column is a decorative arch showcase; right
 * column holds the sign-in form + (optional) inline register form. Form
 * fields, names, nonces, and submit action are all WC defaults so
 * `woocommerce_login_form_*` / `woocommerce_register_form_*` actions and
 * WC_Form_Handler continue to work unchanged.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Layout for signed-out users — "Heritage Atelier" auth gate.
 *
 * @return void
 */
function dg_render_account_signed_out(): void {
	$register_enabled = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) );
	$lost_pwd_url     = (string) wp_lostpassword_url();
	$showcase_image   = get_theme_file_uri( 'assets/images/account-auth/sign-in.jpg' );
	// "Intended URL" the user was on before hitting the auth gate — carried
	// via redirect_to query arg (dg_auth_gate_url_with_return()) and echoed
	// back as a hidden field so it survives the POST round-trip (including
	// re-renders after a validation error). Validated again server-side by
	// dg_account_safe_redirect_target() / WC's own redirect_to handling.
	$redirect_to = dg_account_safe_redirect_target();
	?>
	<!-- Viewport outer double-gold pinstripe frame -->
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--outer" aria-hidden="true"></div>
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--inner" aria-hidden="true"></div>

	<!-- Classical corner flourishes (top-left, top-right, bottom-left, bottom-right) -->
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tr" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--bl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--br" aria-hidden="true">&#10022;</span>

	<main class="dg-account dg-account--signed-out" id="main-content">
		<div class="dg-account-auth">

			<!-- Brand header (Atelier Imperial / Dragon Glow / Est. 2026) -->
			<header class="dg-login__brand-header" data-sr>
				<div class="dg-login__brand-eyebrow">
					<div class="dg-login__brand-line"></div>
					<div class="dg-login__brand-eyebrow-text">
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
						<span><?php esc_html_e( 'Atelier Imperial', 'dragon-glow' ); ?></span>
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
					</div>
					<div class="dg-login__brand-line"></div>
				</div>
				<div class="dg-account-auth__brand-link">
					<h1 class="dg-account-auth__brand-title">
						<?php esc_html_e( 'Dragon Glow', 'dragon-glow' ); ?>
					</h1>
					<div class="dg-login__brand-subtitle">
						<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
						<p><?php esc_html_e( 'Est. 2026  •  San Francisco  •  New York', 'dragon-glow' ); ?></p>
						<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
					</div>
				</div>
			</header>

			<div class="dg-login__frame">

				<!-- Outer inset gold filigree border (double inline border inside the frame) -->
				<span class="dg-login__filigree dg-login__filigree--outer" aria-hidden="true"></span>
				<span class="dg-login__filigree dg-login__filigree--inner" aria-hidden="true"></span>

				<!-- LEFT: Heritage showcase -->
				<div class="dg-login__showcase" data-sr>
					<div class="dg-login__showcase-top">
						<div class="dg-login__tome-row">
							<span class="dg-login__tome"><?php esc_html_e( 'Tome IV', 'dragon-glow' ); ?></span>
							<span class="dg-login__dot" aria-hidden="true">&bull;</span>
							<span class="dg-login__tome-sub"><?php esc_html_e( 'The Golden Alchemy Formulation', 'dragon-glow' ); ?></span>
						</div>
						<span class="dg-account-auth__sanctuary-badge"><?php esc_html_e( 'Ritual Sanctuary', 'dragon-glow' ); ?></span>
					</div>

					<div class="dg-login__arch-wrap">
						<div class="dg-login__crown" aria-hidden="true">
							<span class="dg-login__crown-line"></span>
							<span class="material-symbols-outlined">wb_twilight</span>
							<span class="dg-login__crown-line"></span>
						</div>

						<div class="dg-login__arch">
							<div class="dg-login__arch-inner">
								<img src="<?php echo esc_url( $showcase_image ); ?>"
									alt="<?php esc_attr_e( 'Dragon Glow luxury elixir bottle', 'dragon-glow' ); ?>"
									class="dg-login__arch-img" loading="eager" />
								<div class="dg-login__arch-caption">
									<p><?php esc_html_e( 'Dragon Elixir N° 1', 'dragon-glow' ); ?></p>
									<span><?php esc_html_e( '24K Botanical Golden Serum', 'dragon-glow' ); ?></span>
								</div>
							</div>
							<span class="dg-login__medallion" aria-hidden="true">
								<span class="material-symbols-outlined">verified</span>
							</span>
						</div>
					</div>

					<div class="dg-login__ritual">
						<p class="dg-login__quote">
							&ldquo;<?php esc_html_e( 'Awaken the sovereign radiance dormant within each morning dawn.', 'dragon-glow' ); ?>&rdquo;
						</p>
						<div class="dg-login__steps">
							<div class="dg-login__step">
								<span class="dg-login__step-label"><?php esc_html_e( 'I. PURIFY', 'dragon-glow' ); ?></span>
								<span class="dg-login__step-sub"><?php esc_html_e( 'Nectar Emulsion', 'dragon-glow' ); ?></span>
							</div>
							<div class="dg-login__step">
								<span class="dg-login__step-label"><?php esc_html_e( 'II. INFUSE', 'dragon-glow' ); ?></span>
								<span class="dg-login__step-sub"><?php esc_html_e( 'Golden Drops', 'dragon-glow' ); ?></span>
							</div>
							<div class="dg-login__step">
								<span class="dg-login__step-label"><?php esc_html_e( 'III. SEAL', 'dragon-glow' ); ?></span>
								<span class="dg-login__step-sub"><?php esc_html_e( 'Silk Veil Balm', 'dragon-glow' ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- RIGHT: Sign in / Register -->
				<div class="dg-login__panel-col" data-sr>

					<header class="dg-login__head">
						<div class="dg-login__eyebrow-row">
							<span class="dg-login__eyebrow-line" aria-hidden="true"></span>
							<span class="dg-account-auth__eyebrow"><?php esc_html_e( 'Registry of Members', 'dragon-glow' ); ?></span>
							<span class="dg-login__eyebrow-line" aria-hidden="true"></span>
						</div>
						<h1 class="dg-login__title"><?php esc_html_e( 'Enter the Sanctuary', 'dragon-glow' ); ?></h1>
						<span class="dg-account-auth__title-rule" aria-hidden="true"></span>
						<p class="dg-login__sub">
							<?php esc_html_e( 'Present your apothecary credentials to receive curated formulations, private releases, and ancestral botanical privileges.', 'dragon-glow' ); ?>
						</p>
					</header>

					<?php wc_print_notices(); ?>

					<form method="post" class="dg-account-form" action="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" novalidate>

						<?php do_action( 'woocommerce_login_form_start' ); ?>

						<div class="dg-account-field">
							<div class="dg-account-field__row-label">
								<label for="dg-login-username"><?php esc_html_e( 'Client ID or Email', 'dragon-glow' ); ?></label>
								<span class="dg-account-field__hint"><?php esc_html_e( 'Dragon Codex', 'dragon-glow' ); ?></span>
							</div>
							<div class="dg-account-field__wrap">
								<span class="material-symbols-outlined dg-account-field__icon" aria-hidden="true">fingerprint</span>
								<input
									type="text"
									class="dg-account-field__input dg-account-field__input--icon"
									name="username"
									id="dg-login-username"
									autocomplete="username"
									placeholder="<?php esc_attr_e( 'patron@maison-dragonglow.com', 'dragon-glow' ); ?>"
									value="<?php echo isset( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
										?>" />
							</div>
						</div>

						<div class="dg-account-field">
							<div class="dg-account-field__row-label">
								<label for="dg-login-password"><?php esc_html_e( 'Security Passphrase', 'dragon-glow' ); ?></label>
								<a href="<?php echo esc_url( $lost_pwd_url ); ?>" class="dg-account-form__link">
									<?php esc_html_e( 'Lost credentials?', 'dragon-glow' ); ?>
								</a>
							</div>
							<div class="dg-account-field__wrap">
								<span class="material-symbols-outlined dg-account-field__icon" aria-hidden="true">key</span>
								<input
									type="password"
									class="dg-account-field__input dg-account-field__input--icon"
									name="password"
									id="dg-login-password"
									autocomplete="current-password"
									placeholder="<?php esc_attr_e( 'Enter your confidential cipher', 'dragon-glow' ); ?>" />
								<button
									type="button"
									class="dg-account-field__toggle"
									aria-label="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
									data-label-hide="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
									data-label-show="<?php esc_attr_e( 'Hide password', 'dragon-glow' ); ?>"
									data-dg-toggle-password="#dg-login-password">
									<span class="material-symbols-outlined">visibility</span>
								</button>
							</div>
						</div>

						<div class="dg-account-field__row">
							<label class="dg-account-checkbox">
								<input type="checkbox" name="rememberme" value="forever" />
								<span><?php esc_html_e( 'Preserve session token on this terminal', 'dragon-glow' ); ?></span>
							</label>
						</div>

						<?php do_action( 'woocommerce_login_form' ); ?>

						<input type="hidden" name="woocommerce-login-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-login' ) ); ?>" />
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

						<button type="submit" name="login" value="<?php esc_attr_e( 'Sign in', 'dragon-glow' ); ?>" class="dg-login__submit">
							<span class="dg-login__submit-frame" aria-hidden="true"></span>
							<span class="material-symbols-outlined">workspace_premium</span>
							<span><?php esc_html_e( 'Enter the Sanctuary', 'dragon-glow' ); ?></span>
							<span class="material-symbols-outlined">arrow_right_alt</span>
						</button>

					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>

			<!-- Alternative Access (social sign-in buttons) -->
			<div class="dg-login__alt-access">
				<div class="dg-login__alt-access-header">
					<span class="dg-login__alt-access-line" aria-hidden="true"></span>
					<div class="dg-login__alt-access-text">
						<span class="dg-login__divider-star">&#10022;</span>
						<span><?php esc_html_e( 'Alternative Access', 'dragon-glow' ); ?></span>
						<span class="dg-login__divider-star">&#10022;</span>
					</div>
					<span class="dg-login__alt-access-line" aria-hidden="true"></span>
				</div>

					<?php
					$google_enabled = dg_social_login_google_enabled();
					$apple_enabled  = dg_social_login_apple_enabled();
					?>
					<div class="dg-login__social-buttons">
						<?php if ( $google_enabled ) : ?>
							<a href="<?php echo esc_url( dg_social_login_start_url( 'google' ) ); ?>" class="dg-login__social-btn">
						<?php else : ?>
							<button type="button" class="dg-login__social-btn" disabled
								title="<?php esc_attr_e( 'Google sign-in is coming soon.', 'dragon-glow' ); ?>">
						<?php endif; ?>
							<svg class="dg-login__social-icon" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
								<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
								<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
								<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
							</svg>
							<span><?php esc_html_e( 'Google', 'dragon-glow' ); ?></span>
						<?php echo $google_enabled ? '</a>' : '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static closing tag string, no user input. ?>

						<?php if ( $apple_enabled ) : ?>
							<a href="<?php echo esc_url( dg_social_login_start_url( 'apple' ) ); ?>" class="dg-login__social-btn">
						<?php else : ?>
							<button type="button" class="dg-login__social-btn" disabled
								title="<?php esc_attr_e( 'Apple sign-in is coming soon.', 'dragon-glow' ); ?>">
						<?php endif; ?>
							<svg class="dg-login__social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
								<path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
							</svg>
							<span><?php esc_html_e( 'Apple ID', 'dragon-glow' ); ?></span>
						<?php echo $apple_enabled ? '</a>' : '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static closing tag string, no user input. ?>
					</div>

					<p class="dg-login__register-prompt">
						<?php esc_html_e( 'Not yet inscribed in our ledger?', 'dragon-glow' ); ?>
						<?php
						// Preserve redirect_to when switching from login → register (same
						// "intended URL" pattern — if user came from /shop/ to login, and
						// clicks "Request Atelier Initiation", they should land back on /shop/
						// after registering, not on the dashboard).
						$register_url = dg_account_endpoint_url( 'register' );
						if ( '' !== $redirect_to && $redirect_to !== dg_account_endpoint_url( '' ) ) {
							$register_url = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $register_url );
						}
						?>
						<a href="<?php echo esc_url( $register_url ); ?>" class="dg-login__register-link">
							<?php esc_html_e( 'Request Atelier Initiation', 'dragon-glow' ); ?>
						</a>
					</p>
				</div>

				<?php if ( $register_enabled ) : ?>
						<div class="dg-login__divider">
							<span class="dg-login__divider-star">&#10022;</span>
							<span><?php esc_html_e( 'Not Yet Inscribed?', 'dragon-glow' ); ?></span>
							<span class="dg-login__divider-star">&#10022;</span>
						</div>

						<section class="dg-login__register" id="dg-account-register">
							<p class="dg-login__text">
								<?php esc_html_e( 'Track orders, save your favourites, and unlock exclusive offers from Dragon Glow.', 'dragon-glow' ); ?>
							</p>

							<form method="post" class="dg-account-form" action="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" novalidate>

								<?php do_action( 'woocommerce_register_form_start' ); ?>

								<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
									<div class="dg-account-field">
										<label for="dg-reg-username"><?php esc_html_e( 'Username', 'dragon-glow' ); ?></label>
										<input
											type="text"
											class="dg-account-field__input"
											name="username"
											id="dg-reg-username"
											autocomplete="username"
											value="<?php echo isset( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
												?>" />
									</div>
								<?php endif; ?>

								<div class="dg-account-field">
									<label for="dg-reg-email"><?php esc_html_e( 'Client ID or Primary Email', 'dragon-glow' ); ?></label>
									<input
										type="email"
										class="dg-account-field__input"
										name="email"
										id="dg-reg-email"
										autocomplete="email"
										placeholder="<?php esc_attr_e( 'patron@maison-dragonglow.com', 'dragon-glow' ); ?>"
										value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
											?>" />
								</div>

								<div class="dg-account-field">
									<label for="dg-reg-password"><?php esc_html_e( 'Choose Security Cipher (Password)', 'dragon-glow' ); ?></label>
									<div class="dg-account-field__wrap">
										<input
											type="password"
											class="dg-account-field__input"
											name="password"
											id="dg-reg-password"
											autocomplete="new-password"
											placeholder="<?php esc_attr_e( 'Minimum 8 golden characters', 'dragon-glow' ); ?>" />
										<button
											type="button"
											class="dg-account-field__toggle"
											aria-label="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
											data-label-hide="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
											data-label-show="<?php esc_attr_e( 'Hide password', 'dragon-glow' ); ?>"
											data-dg-toggle-password="#dg-reg-password">
											<span class="material-symbols-outlined">visibility</span>
										</button>
									</div>
								</div>

								<?php do_action( 'woocommerce_register_form' ); ?>

								<input type="hidden" name="woocommerce-register-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-register' ) ); ?>" />
								<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

								<button type="submit" name="register" value="<?php esc_attr_e( 'Create account', 'dragon-glow' ); ?>" class="dg-login__submit">
									<span class="dg-login__submit-frame" aria-hidden="true"></span>
									<span class="material-symbols-outlined">verified_user</span>
									<span><?php esc_html_e( 'Inscribe in Atelier Ledger', 'dragon-glow' ); ?></span>
								</button>

								<?php do_action( 'woocommerce_register_form_end' ); ?>
							</form>
						</section>
					<?php endif; ?>

			<div class="dg-login__footnote">
				<span class="dg-login__footnote-item">
					<span class="material-symbols-outlined" aria-hidden="true">eco</span>
					<span><?php esc_html_e( 'Pure Botanical Distillation', 'dragon-glow' ); ?></span>
				</span>
				<span class="dg-login__dot" aria-hidden="true">&bull;</span>
				<span class="dg-login__footnote-item">
					<span class="material-symbols-outlined" aria-hidden="true">verified</span>
					<span><?php esc_html_e( 'Certified Cruelty-Free', 'dragon-glow' ); ?></span>
				</span>
			</div>

				</div>

			</div>
		</div>
	</main>

	<!-- Classical Heritage Footer -->
	<footer class="dg-account-auth__heritage-footer">
		<div class="dg-account-auth__heritage-locations">
			<span><?php esc_html_e( 'Heritage Atelier San Francisco', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot" aria-hidden="true">&bull;</span>
			<span><?php esc_html_e( 'Fifth Avenue New York', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot" aria-hidden="true">&bull;</span>
			<span><?php esc_html_e( 'Parisian Archive', 'dragon-glow' ); ?></span>
		</div>
		<div class="dg-account-auth__heritage-copyright">
			<?php
			/* translators: %d: Current year */
			printf( esc_html__( '© %d Dragon Glow Cosmetics Inc. All rights reserved.', 'dragon-glow' ), (int) gmdate( 'Y' ) );
			?>
		</div>
	</footer>
	<?php
}
