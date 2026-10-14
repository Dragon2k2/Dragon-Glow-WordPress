<?php
/**
 * Dragon Glow — My Account: Registration Validation
 *
 * Server-side validation for the `/my-account/register/` form. Runs inside
 * `WC_Form_Handler::process_registration()` (after WC verified the nonce and
 * before the customer is created) and collects EVERY problem in one pass, so
 * the visitor sees all of them at once instead of fixing one error per
 * submit. Each problem is tagged with the form field it belongs to; the
 * register page (register.php) uses the tags to render an error summary plus
 * inline messages, and localizes the same texts to account-register.js so
 * client-side and server-side messages never drift apart.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maximum length accepted for the first / last name fields.
 *
 * @return int
 */
function dg_register_name_max_length(): int {
	return 50;
}

/**
 * Map of validated form fields to the DOM id of their input.
 *
 * Field keys are shared by PHP (error tagging), the template (aria wiring)
 * and JS (`data-dg-field` attributes).
 *
 * @return array<string,string>
 */
function dg_register_fields(): array {
	return array(
		'first_name' => 'dg-reg-first-name',
		'last_name'  => 'dg-reg-last-name',
		'email'      => 'dg-reg-email',
		'password'   => 'dg-reg-password',
		'terms'      => 'dg-reg-terms',
	);
}

/**
 * Password character-type rules (OWASP/NIST aligned, see register-handler.php).
 *
 * @return array<string,array{pattern:string,label:string}>
 */
function dg_register_password_rules(): array {
	return array(
		'lowercase' => array(
			'pattern' => '/[a-z]/',
			'label'   => __( 'lowercase letter (a-z)', 'dragon-glow' ),
		),
		'uppercase' => array(
			'pattern' => '/[A-Z]/',
			'label'   => __( 'uppercase letter (A-Z)', 'dragon-glow' ),
		),
		'digit'     => array(
			'pattern' => '/[0-9]/',
			'label'   => __( 'number (0-9)', 'dragon-glow' ),
		),
		'special'   => array(
			'pattern' => '/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/',
			'label'   => __( 'special character (!@#$%...)', 'dragon-glow' ),
		),
	);
}

/**
 * Plain-text validation messages (single source of truth for PHP and JS).
 *
 * @return array<string,string>
 */
function dg_register_messages(): array {
	$name_max = dg_register_name_max_length();

	return array(
		'summary_title'       => __( 'We could not create your account yet.', 'dragon-glow' ),
		'first_name_required' => __( 'Please enter your first name.', 'dragon-glow' ),
		'last_name_required'  => __( 'Please enter your last name.', 'dragon-glow' ),
		/* translators: %d: maximum number of characters */
		'name_too_long'       => sprintf( __( 'Please use %d characters or fewer.', 'dragon-glow' ), $name_max ),
		'email_required'      => __( 'Please enter your email address.', 'dragon-glow' ),
		'email_invalid'       => __( 'Please enter a valid email address.', 'dragon-glow' ),
		'email_exists'        => __( 'An account is already registered with this email address.', 'dragon-glow' ),
		'password_required'   => __( 'Please enter a password.', 'dragon-glow' ),
		'password_too_short'  => __( 'Password must be at least 12 characters long.', 'dragon-glow' ),
		/* translators: %s: comma-separated list of missing character types */
		'password_missing'    => __( 'Password must contain: %s.', 'dragon-glow' ),
		'terms_required'      => __( 'Please accept the Terms of Service and Privacy Policy to continue.', 'dragon-glow' ),
		'session_expired'     => __( 'Your session has expired. Please submit the form again.', 'dragon-glow' ),
	);
}

/**
 * Collect the problems found in a password.
 *
 * @param string $password Raw (unsanitized) password.
 * @return array<int,array{code:string,message:string}>
 */
function dg_register_password_problems( string $password ): array {
	$messages = dg_register_messages();

	if ( mb_strlen( $password ) < 12 ) {
		return array(
			array(
				'code'    => 'password_too_short',
				'message' => $messages['password_too_short'],
			),
		);
	}

	$missing = array();
	foreach ( dg_register_password_rules() as $rule ) {
		if ( ! preg_match( $rule['pattern'], $password ) ) {
			$missing[] = $rule['label'];
		}
	}

	if ( empty( $missing ) ) {
		return array();
	}

	return array(
		array(
			'code'    => 'password_missing_types',
			'message' => sprintf( $messages['password_missing'], implode( ', ', $missing ) ),
		),
	);
}

/**
 * Request-scoped store for the errors found by the validator.
 *
 * @param array<int,array{code:string,field:string,message:string}>|null $errors Pass an array to replace the stored errors.
 * @return array<int,array{code:string,field:string,message:string}>
 */
function dg_register_error_store( ?array $errors = null ): array {
	static $store = array();

	if ( null !== $errors ) {
		$store = $errors;
	}

	return $store;
}

/**
 * Validate the full registration submission and report every problem.
 *
 * Hooked to `woocommerce_process_registration_errors`, which WC fires from
 * `process_registration()` after the nonce check. WC turns each message of
 * the returned WP_Error into an error notice and aborts account creation.
 *
 * The name and terms checks only apply to the Dragon Glow register page,
 * which posts the `dg_register_context` marker; the compact sign-up form on
 * the sign-in page has no such fields.
 *
 * @param WP_Error $errors   Validation errors collected so far.
 * @param string   $username Username (unused, WC generates it).
 * @param string   $password Password as seen by WC (unused, read from POST).
 * @param string   $email    Email address from the form.
 * @return WP_Error
 */
function dg_register_validate_submission( WP_Error $errors, string $username, string $password, string $email ): WP_Error {
	if ( ! isset( $_POST['register'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by WC core before this filter runs.
		return $errors;
	}

	$messages  = dg_register_messages();
	$full_form = isset( $_POST['dg_register_context'] ) && 'page' === $_POST['dg_register_context']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- compared to a literal.
	$found     = array();

	$add = static function ( string $code, string $field, string $message ) use ( &$found, $errors ): void {
		$found[] = array(
			'code'    => $code,
			'field'   => $field,
			'message' => $message,
		);
		$errors->add( $code, $message );
	};

	// Names (register page only).
	if ( $full_form ) {
		$name_fields = array(
			'first_name' => 'billing_first_name',
			'last_name'  => 'billing_last_name',
		);
		foreach ( $name_fields as $field => $post_key ) {
			$value = isset( $_POST[ $post_key ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( '' === $value ) {
				$add( $field . '_required', $field, $messages[ $field . '_required' ] );
			} elseif ( mb_strlen( $value ) > dg_register_name_max_length() ) {
				$add( $field . '_too_long', $field, $messages['name_too_long'] );
			}
		}
	}

	// Email. Note: revealing that an address is registered is WooCommerce's
	// own default behavior; keep it so legitimate members are pointed to sign-in.
	if ( '' === trim( $email ) ) {
		$add( 'email_required', 'email', $messages['email_required'] );
	} elseif ( ! is_email( $email ) ) {
		$add( 'email_invalid', 'email', $messages['email_invalid'] );
	} elseif ( email_exists( $email ) ) {
		$add( 'email_exists', 'email', $messages['email_exists'] );
	}

	// Password — read from POST because WC blanks it when it auto-generates passwords.
	$raw_password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- raw on purpose to preserve special characters.
	if ( '' === $raw_password ) {
		$add( 'password_required', 'password', $messages['password_required'] );
	} else {
		foreach ( dg_register_password_problems( (string) $raw_password ) as $problem ) {
			$add( $problem['code'], 'password', $problem['message'] );
		}
	}

	// Terms of Service / Privacy Policy consent (register page only).
	if ( $full_form && empty( $_POST['terms'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$add( 'terms_required', 'terms', $messages['terms_required'] );
	}

	dg_register_error_store( $found );

	return $errors;
}
add_filter( 'woocommerce_process_registration_errors', 'dg_register_validate_submission', 10, 4 );

/**
 * Get every error to render on the register page.
 *
 * Returns the field-tagged errors from the validator. When the validator did
 * not run or found nothing (CSRF/nonce failure, anti-spam plugins, errors
 * raised inside `wc_create_new_customer()`), the remaining WooCommerce error
 * notices are converted to untagged ("form") errors instead. The error
 * notices are removed from WC's queue so they are not printed a second time
 * by `wc_print_notices()`; success/info notices are left alone.
 *
 * @return array<int,array{code:string,field:string,message:string}>
 */
function dg_register_get_errors(): array {
	$errors = dg_register_error_store();

	// WC silently ignores a register POST whose nonce is invalid (expired
	// page, full-page cache), so no notice exists for it. Tell the visitor.
	if ( empty( $errors ) && isset( $_POST['register'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- this IS the nonce check.
		$nonce = isset( $_POST['woocommerce-register-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! wp_verify_nonce( $nonce, 'woocommerce-register' ) ) {
			$errors[] = array(
				'code'    => 'session_expired',
				'field'   => 'form',
				'message' => dg_register_messages()['session_expired'],
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
 * @param array<int,array{code:string,field:string,message:string}> $errors Errors from dg_register_get_errors().
 * @return array<string,array<int,string>> Field key => messages.
 */
function dg_register_group_errors( array $errors ): array {
	$grouped = array();

	foreach ( $errors as $error ) {
		$grouped[ $error['field'] ][] = $error['message'];
	}

	return $grouped;
}

/**
 * Get the ARIA attributes for a form control.
 *
 * Output is already escaped.
 *
 * @param string                          $field  Field key from dg_register_fields().
 * @param array<string,array<int,string>> $grouped Output of dg_register_group_errors().
 * @return string
 */
function dg_register_field_attrs( string $field, array $grouped ): string {
	$fields = dg_register_fields();
	if ( ! isset( $fields[ $field ] ) ) {
		return '';
	}

	$attrs = ' aria-describedby="' . esc_attr( $fields[ $field ] . '-error' ) . '"';
	if ( ! empty( $grouped[ $field ] ) ) {
		$attrs .= ' aria-invalid="true"';
	}

	return $attrs;
}

/**
 * Print the inline error container of a form field.
 *
 * Always rendered (hidden when empty) so account-register.js can fill it for
 * client-side validation without creating markup.
 *
 * @param string                          $field   Field key from dg_register_fields().
 * @param array<string,array<int,string>> $grouped Output of dg_register_group_errors().
 * @return void
 */
function dg_register_render_field_error( string $field, array $grouped ): void {
	$fields = dg_register_fields();
	if ( ! isset( $fields[ $field ] ) ) {
		return;
	}

	$messages = $grouped[ $field ] ?? array();
	?>
	<p class="dg-register-form__error" id="<?php echo esc_attr( $fields[ $field ] . '-error' ); ?>" data-dg-error-for="<?php echo esc_attr( $field ); ?>"<?php echo empty( $messages ) ? ' hidden' : ''; ?>>
		<span class="material-symbols-outlined" aria-hidden="true">error</span>
		<span class="dg-register-form__error-text"><?php echo esc_html( implode( ' ', $messages ) ); ?></span>
	</p>
	<?php
}
