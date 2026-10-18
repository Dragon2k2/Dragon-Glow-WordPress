<?php
/**
 * Dragon Glow — My Account: Sign-in Validation
 *
 * Error handling for the signed-out sign-in form (`login.php`). Mirrors the
 * register flow (`register-validation.php`): every problem of a submit is
 * collected, tagged with the form field it belongs to, and rendered as an
 * error summary plus inline messages. The same texts are localized to
 * `assets/js/login.js` so client-side and server-side messages never drift.
 *
 * Cases covered:
 *   - empty Client ID / email          (validated before WC calls wp_signon)
 *   - empty password                   (validated before WC calls wp_signon)
 *   - unknown Client ID / email        (tagged to the username field)
 *   - correct account, wrong password  (tagged to the password field; the
 *                                       message confirms the account exists
 *                                       so the visitor only retypes the password)
 *   - expired page / invalid nonce     (WC ignores the POST silently)
 *   - any other WC / plugin / social-login error notice (shown as form error)
 *
 * Product decision: the messages for "unknown account" and "wrong password"
 * differ on purpose, which reveals whether an account exists. Pair this with
 * login throttling (lockout / rate-limit plugin or WAF) on the server.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map of sign-in form fields to the DOM id of their input.
 *
 * Field keys are shared by PHP (error tagging), the template (aria wiring)
 * and JS (`data-dg-error-for` / `data-dg-error-link` attributes). The
 * `credentials` key is server-only: bad credentials flag both controls and
 * its summary link targets the first one.
 *
 * @return array<string,string>
 */
function dg_login_fields(): array {
	return array(
		'username'    => 'dg-login-username',
		'password'    => 'dg-login-password',
		'credentials' => 'dg-login-username',
	);
}

/**
 * Plain-text validation messages (single source of truth for PHP and JS).
 *
 * @return array<string,string>
 */
function dg_login_messages(): array {
	return array(
		'summary_title'       => __( 'We could not sign you in.', 'dragon-glow' ),
		'username_required'   => __( 'Please enter your Client ID or email address.', 'dragon-glow' ),
		'password_required'   => __( 'Please enter your password.', 'dragon-glow' ),
		'unknown_account'     => __( 'We could not find an account with this Client ID or email address. Please check it and try again.', 'dragon-glow' ),
		'incorrect_password'  => __( 'We found your account, but the password is incorrect. Please re-enter your password.', 'dragon-glow' ),
		'invalid_credentials' => __( 'The Client ID, email address or password you entered is incorrect. Please check your details and try again.', 'dragon-glow' ),
		'session_expired'     => __( 'Your session has expired. Please submit the form again.', 'dragon-glow' ),
	);
}

/**
 * Map of the WP_Error codes raised by wp_signon() to the form field they
 * belong to and the message key (see dg_login_messages()) to show.
 *
 * @return array<string,array{field:string,message:string}>
 */
function dg_login_failure_map(): array {
	return array(
		'invalid_username'      => array(
			'field'   => 'username',
			'message' => 'unknown_account',
		),
		'invalid_email'         => array(
			'field'   => 'username',
			'message' => 'unknown_account',
		),
		'incorrect_password'    => array(
			'field'   => 'password',
			'message' => 'incorrect_password',
		),
		'authentication_failed' => array(
			'field'   => 'credentials',
			'message' => 'invalid_credentials',
		),
	);
}

/**
 * Request-scoped store for the errors found while processing the sign-in POST.
 *
 * @param array<int,array{code:string,field:string,message:string}>|null $errors Pass an array to replace the stored errors.
 * @return array<int,array{code:string,field:string,message:string}>
 */
function dg_login_error_store( ?array $errors = null ): array {
	static $store = array();

	if ( null !== $errors ) {
		$store = $errors;
	}

	return $store;
}

/**
 * Validate the sign-in submission and report every problem.
 *
 * Hooked to `woocommerce_process_login_errors`, which WC fires from
 * `process_login()` after the nonce check and before `wp_signon()`. WC turns
 * the returned WP_Error into an error notice and skips the sign-in attempt.
 *
 * @param WP_Error $errors   Validation errors collected so far.
 * @param string   $username Username / email as submitted (trimmed by WC).
 * @param string   $password Password as submitted.
 * @return WP_Error
 */
function dg_login_validate_submission( WP_Error $errors, string $username, string $password ): WP_Error {
	// Only the Dragon Glow sign-in form posts this marker; the checkout login
	// form (and any other WC login form) keeps WooCommerce's own messages.
	if ( ! isset( $_POST['dg_login_context'] ) || 'page' !== $_POST['dg_login_context'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified by WC core before this filter runs; compared to a literal.
		return $errors;
	}

	$messages = dg_login_messages();
	$found    = array();

	if ( '' === trim( $username ) ) {
		$found[] = array(
			'code'    => 'username_required',
			'field'   => 'username',
			'message' => $messages['username_required'],
		);
		$errors->add( 'username_required', $messages['username_required'] );
	}

	if ( '' === $password ) {
		$found[] = array(
			'code'    => 'password_required',
			'field'   => 'password',
			'message' => $messages['password_required'],
		);
		$errors->add( 'password_required', $messages['password_required'] );
	}

	dg_login_error_store( $found );

	return $errors;
}
add_filter( 'woocommerce_process_login_errors', 'dg_login_validate_submission', 10, 3 );

/**
 * Remember a failed credential check.
 *
 * `wp_login_failed` fires from wp_authenticate() with the WP_Error of the
 * failed attempt. Only the codes in dg_login_failure_map() are stored, each
 * tagged to its own field (unknown account -> username, wrong password ->
 * password), so the visitor knows exactly what to fix. Every other failure
 * (lockout plugins, blocked users, ...) keeps its own message and is picked
 * up from the WC notice by dg_login_get_errors().
 *
 * @param string        $username Username or email that was submitted (unused).
 * @param WP_Error|null $error    Error of the failed attempt (WP 5.4+).
 * @return void
 */
function dg_login_capture_failure( string $username, $error = null ): void {
	$map = dg_login_failure_map();
	if ( ! $error instanceof WP_Error || ! isset( $map[ $error->get_error_code() ] ) ) {
		return;
	}

	if ( ! empty( dg_login_error_store() ) ) {
		return;
	}

	$entry = $map[ $error->get_error_code() ];

	dg_login_error_store(
		array(
			array(
				'code'    => $error->get_error_code(),
				'field'   => $entry['field'],
				'message' => dg_login_messages()[ $entry['message'] ],
			),
		)
	);
}
add_action( 'wp_login_failed', 'dg_login_capture_failure', 10, 2 );

/**
 * Get every error to render on the sign-in form.
 *
 * Returns the field-tagged errors from the validator. When none were tagged
 * (expired nonce, lockout / anti-spam plugins, social-login failures), the
 * remaining WooCommerce error notices are converted to untagged ("form")
 * errors instead. Error notices are removed from WC's queue so they are not
 * printed a second time by `wc_print_notices()`; success/info notices (for
 * example "password reset") are left alone.
 *
 * @return array<int,array{code:string,field:string,message:string}>
 */
function dg_login_get_errors(): array {
	$errors = dg_login_error_store();

	// The inline "create account" form on the same page posts `register`;
	// its error notices belong to WC's own printing, not to this summary.
	if ( isset( $_POST['register'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only branch switch.
		return $errors;
	}

	// WC silently ignores a login POST whose nonce is invalid (expired
	// page, full-page cache), so no notice exists for it. Tell the visitor.
	if ( empty( $errors ) && isset( $_POST['login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- this IS the nonce check.
		$nonce = isset( $_POST['woocommerce-login-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-login-nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! wp_verify_nonce( $nonce, 'woocommerce-login' ) ) {
			$errors[] = array(
				'code'    => 'session_expired',
				'field'   => 'form',
				'message' => dg_login_messages()['session_expired'],
			);
		}
	}

	if ( ! function_exists( 'wc_get_notices' ) || ! function_exists( 'wc_set_notices' ) ) {
		return $errors;
	}

	$notices = wc_get_notices();
	if ( empty( $notices['error'] ) ) {
		return $errors;
	}

	if ( empty( $errors ) ) {
		foreach ( $notices['error'] as $notice ) {
			$message = is_array( $notice ) ? (string) ( $notice['notice'] ?? '' ) : (string) $notice;
			$message = wp_specialchars_decode( wp_strip_all_tags( $message ), ENT_QUOTES );
			$message = trim( (string) preg_replace( '/^\s*Error:\s*/i', '', $message ) );
			if ( '' !== $message ) {
				$errors[] = array(
					'code'    => 'notice',
					'field'   => 'form',
					'message' => $message,
				);
			}
		}
	}

	unset( $notices['error'] );
	wc_set_notices( $notices );

	return $errors;
}

/**
 * Group errors by the form field they belong to.
 *
 * @param array<int,array{code:string,field:string,message:string}> $errors Errors from dg_login_get_errors().
 * @return array<string,array<int,string>> Field key => messages.
 */
function dg_login_group_errors( array $errors ): array {
	$grouped = array();

	foreach ( $errors as $error ) {
		$grouped[ $error['field'] ][] = $error['message'];
	}

	return $grouped;
}

/**
 * Get the ARIA attributes for a sign-in control.
 *
 * Bad credentials mark both controls invalid. Output is already escaped.
 *
 * @param string                          $field   Field key ('username' or 'password').
 * @param array<string,array<int,string>> $grouped Output of dg_login_group_errors().
 * @return string
 */
function dg_login_field_attrs( string $field, array $grouped ): string {
	$fields = dg_login_fields();
	if ( ! isset( $fields[ $field ] ) ) {
		return '';
	}

	$attrs = ' aria-describedby="' . esc_attr( $fields[ $field ] . '-error' ) . '"';
	if ( ! empty( $grouped[ $field ] ) || ! empty( $grouped['credentials'] ) ) {
		$attrs .= ' aria-invalid="true"';
	}

	return $attrs;
}

/**
 * Print the inline error container of a sign-in field.
 *
 * Always rendered (hidden when empty) so login.js can fill it for
 * client-side validation without creating markup.
 *
 * @param string                          $field   Field key ('username' or 'password').
 * @param array<string,array<int,string>> $grouped Output of dg_login_group_errors().
 * @return void
 */
function dg_login_render_field_error( string $field, array $grouped ): void {
	$fields = dg_login_fields();
	if ( ! isset( $fields[ $field ] ) ) {
		return;
	}

	$messages = $grouped[ $field ] ?? array();
	?>
	<p class="dg-login__error" id="<?php echo esc_attr( $fields[ $field ] . '-error' ); ?>" data-dg-error-for="<?php echo esc_attr( $field ); ?>"<?php echo empty( $messages ) ? ' hidden' : ''; ?>>
		<span class="material-symbols-outlined" aria-hidden="true">error</span>
		<span class="dg-login__error-text"><?php echo esc_html( implode( ' ', $messages ) ); ?></span>
	</p>
	<?php
}
