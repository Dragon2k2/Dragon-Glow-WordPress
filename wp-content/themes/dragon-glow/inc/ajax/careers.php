<?php
/**
 * Dragon Glow — AJAX: Careers Application (Cách A — email only, no CPT).
 *
 * Handles the Careers apply flow:
 *   1. Validate nonce + form fields (full_name, email required).
 *   2. Upload CV via wp_handle_upload — file path attached to the Brevo email.
 *   3. Generate approve/reject tokens — stored as 14-day transients.
 *   4. Send HR email via Brevo API (falls back to wp_mail()).
 *   5. Send candidate auto-reply via Brevo API (falls back to wp_mail()).
 *
 * Approval/rejection routing is handled in inc/approval-handler.php by token.
 * Brevo email sending lives in inc/ajax/brevo.php.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lazy-load the wp-admin files needed for wp_handle_upload(), only when the action fires.
 */
function dg_careers_apply_load_admin_files(): void {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

/**
 * Form schema (filterable qua dg_careers_apply_form_fields).
 * Trả về mảng field. Mỗi field có key/label/type (+required/placeholder).
 *
 * @return array<int, array<string, mixed>>
 */
function dg_careers_apply_form_fields(): array {
	$fields = array(
		array(
			'key'         => 'full_name',
			'label'       => __( 'Full name', 'dragon-glow' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => __( 'Your full name', 'dragon-glow' ),
		),
		array(
			'key'         => 'email',
			'label'       => __( 'Email', 'dragon-glow' ),
			'type'        => 'email',
			'required'    => true,
			'placeholder' => 'you@example.com',
		),
		array(
			'key'         => 'phone',
			'label'       => __( 'Phone (optional)', 'dragon-glow' ),
			'type'        => 'tel',
			'placeholder' => '+1 555 000 0000',
		),
		array(
			'key'         => 'linkedin',
			'label'       => __( 'LinkedIn or portfolio (optional)', 'dragon-glow' ),
			'type'        => 'url',
			'placeholder' => 'https://',
		),
		array(
			'key'         => 'cv',
			'label'       => __( 'CV / Resume (PDF, up to 8 MB)', 'dragon-glow' ),
			'type'        => 'file',
			'max_size_mb' => 8,
			'accept'      => '.pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		),
		array(
			'key'         => 'note',
			'label'       => __( 'A short note (optional)', 'dragon-glow' ),
			'type'        => 'textarea',
			'placeholder' => __( 'Tell us why you are interested in this role.', 'dragon-glow' ),
		),
	);
	return (array) apply_filters( 'dg_careers_apply_form_fields', $fields );
}

/**
 * AJAX: Submit a Careers application (Cách A — email only).
 *
 *  1. Validate nonce + form fields (full_name, email required).
 *  2. Upload CV via wp_handle_upload — file path used to attach to Brevo email.
 *  3. Sinh 2 token (approve / reject) — lưu transient 14 ngày.
 *  4. Send email HR via Brevo API (bypasses WP Mail SMTP plugin).
 *  5. Auto-reply for candidate via Brevo API.
 *
 * @return void
 */
function dg_ajax_submit_application(): void {
	dg_careers_apply_load_admin_files();

	check_ajax_referer( 'dg_nonce', 'nonce' );

	$role = sanitize_text_field( $_POST['role'] ?? '' );
	if ( empty( $role ) ) {
		wp_send_json_error( array( 'message' => __( 'Please choose a role before submitting.', 'dragon-glow' ) ) );
	}

	// Validate text fields. Form schema định nghĩa trong dg_careers_apply_form_fields()
	// (đã apply filter `dg_careers_apply_form_fields` bên trong function).
	$fields = dg_careers_apply_form_fields();
	$values = array();
	$errors = array();

	foreach ( $fields as $field ) {
		if ( 'file' === $field['type'] ) {
			continue;
		}
		$key      = $field['key'];
		$required = ! empty( $field['required'] );
		$raw      = wp_unslash( $_POST[ $key ] ?? '' );

		switch ( $field['type'] ) {
			case 'email':
				$value = sanitize_email( $raw );
				if ( $required && ( empty( $value ) || ! is_email( $value ) ) ) {
					$errors[] = sprintf( __( '%s: please enter a valid email address.', 'dragon-glow' ), $field['label'] );
				}
				break;
			case 'url':
				$value = $raw ? esc_url_raw( $raw ) : '';
				if ( $value && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
					$errors[] = sprintf( __( '%s: please enter a valid URL.', 'dragon-glow' ), $field['label'] );
					$value    = '';
				}
				break;
			case 'textarea':
				$value = sanitize_textarea_field( $raw );
				break;
			default:
				$value = sanitize_text_field( $raw );
				break;
		}

		if ( $required && '' === $value ) {
			$errors[] = sprintf( __( '%s is required.', 'dragon-glow' ), $field['label'] );
		}
		$values[ $key ] = $value;
	}

	if ( ! empty( $errors ) ) {
		wp_send_json_error( array( 'message' => implode( ' ', $errors ) ) );
	}

	// CV upload (optional).
	$cv_path = '';
	if ( ! empty( $_FILES['cv']['name'] ) ) {
		$cv_field = null;
		foreach ( $fields as $f ) {
			if ( 'file' === $f['type'] && 'cv' === $f['key'] ) {
				$cv_field = $f;
				break;
			}
		}
		$max_bytes = $cv_field && ! empty( $cv_field['max_size_mb'] )
			? (int) $cv_field['max_size_mb'] * 1024 * 1024
			: 8 * 1024 * 1024;

		if ( isset( $_FILES['cv']['size'] ) && (int) $_FILES['cv']['size'] > $max_bytes ) {
			wp_send_json_error( array( 'message' => __( 'Your CV is too large. Please upload a file smaller than the limit.', 'dragon-glow' ) ) );
		}

		$upload = wp_handle_upload( $_FILES['cv'], array( 'test_form' => false ) );
		if ( isset( $upload['error'] ) ) {
			wp_send_json_error( array( 'message' => $upload['error'] ) );
		}
		$cv_path = $upload['file'];
	}

	// Sinh token approve / reject; lưu transient 14 ngày.
	$approve_token = wp_generate_password( 32, false );
	$reject_token  = wp_generate_password( 32, false );
	$payload       = array(
		'role'      => $role,
		'name'      => $values['full_name'] ?? '',
		'email'     => $values['email'] ?? '',
		'phone'     => $values['phone'] ?? '',
		'linkedin'  => $values['linkedin'] ?? '',
		'note'      => $values['note'] ?? '',
		'cv_path'   => $cv_path,
		'submitted' => current_time( 'mysql' ),
		'ip'        => dg_careers_apply_get_ip(),
	);
	set_transient( 'dg_app_approve_' . $approve_token, $payload, 14 * DAY_IN_SECONDS );
	set_transient( 'dg_app_reject_'  . $reject_token,  $payload, 14 * DAY_IN_SECONDS );

	$approve_url = add_query_arg(
		array( 'dg_action' => 'approve', 'token' => $approve_token ),
		home_url( '/' )
	);
	$reject_url = add_query_arg(
		array( 'dg_action' => 'reject', 'token' => $reject_token ),
		home_url( '/' )
	);

	$hr_email   = (string) apply_filters( 'dg_careers_hr_email', 'careers@dragonglows.page.gd' );
	$from_email = (string) apply_filters( 'dg_careers_from_email', 'anhthuymaiphuong698@gmail.com' );
	$from_name  = (string) apply_filters( 'dg_careers_from_name', 'Dragon Glow Careers' );

	$hr_sent = false;
	if ( is_email( $hr_email ) ) {
		// Ưu tiên Brevo API, fallback wp_mail().
		$brevo_sent = dg_send_brevo_email( array(
			'to_email'       => $hr_email,
			'to_name'        => 'Dragon Glow HR',
			'subject'        => sprintf( '[Dragon Glow Careers] %s — %s', $values['full_name'] ?? '', $role ),
			'html_body'      => dg_careers_email_for_hr( $values, $role, $approve_url, $reject_url ),
			'from_email'    => $from_email,
			'from_name'     => $from_name,
			'reply_to_email' => $values['email'] ?? '',
			'reply_to_name'  => $values['full_name'] ?? '',
			'cv_path'       => $cv_path ?: null,
		) );

		if ( ! $brevo_sent ) {
			// Fallback: wp_mail() (sẽ fail nếu plugin SMTP không hook front-end, nhưng worth a shot).
			wp_mail(
				$hr_email,
				sprintf( '[Dragon Glow Careers] %s — %s', $values['full_name'] ?? '', $role ),
				dg_careers_email_for_hr( $values, $role, $approve_url, $reject_url ),
				array(
					'Content-Type: text/html; charset=UTF-8',
					sprintf( 'Reply-To: %s <%s>', $values['full_name'] ?? '', $values['email'] ?? '' ),
					sprintf( 'From: %s <%s>', $from_name, $from_email ),
				),
				$cv_path ? array( $cv_path ) : array()
			);
		}
		$hr_sent = true;
	}

	$candidate_sent = false;
	if ( ! empty( $values['email'] ) && is_email( $values['email'] ) ) {
		$brevo_sent_candidate = dg_send_brevo_email( array(
			'to_email'   => $values['email'],
			'to_name'    => $values['full_name'] ?? '',
			'subject'    => sprintf( '[Dragon Glow] We received your application for %s', $role ),
			'html_body'  => dg_careers_email_for_candidate_ack( $values, $role ),
			'from_email' => $from_email,
			'from_name'  => $from_name,
		) );

		if ( ! $brevo_sent_candidate ) {
			wp_mail(
				$values['email'],
				sprintf( '[Dragon Glow] We received your application for %s', $role ),
				dg_careers_email_for_candidate_ack( $values, $role ),
				array(
					'Content-Type: text/html; charset=UTF-8',
					sprintf( 'From: %s <%s>', $from_name, $from_email ),
				)
			);
		}
		$candidate_sent = true;
	}

	do_action( 'dg_careers_application_submitted', $values, $approve_token, $reject_token );

	$msg = __( 'Your application has been sent. We will be in touch shortly.', 'dragon-glow' );
	if ( ! $hr_sent && ! $candidate_sent ) {
		$msg = __( 'Your application was received, but email delivery may be delayed. We will be in touch shortly.', 'dragon-glow' );
	}

	wp_send_json_success( array( 'message' => $msg ) );
}

/**
 * Build HTML email body for HR — includes role intro + summary table + 2 action buttons.
 */
function dg_careers_email_for_hr( array $values, string $role, string $approve_url, string $reject_url ): string {
	$intro_map = (array) apply_filters(
		'dg_careers_role_intro',
		array(
			'Formulation Chemist'         => __( 'A chemist who blends ancient botanicals with modern actives — please review the formulation samples referenced in the CV.', 'dragon-glow' ),
			'Sustainability Lead'         => __( 'Sustainability is a core pillar for Dragon Glow. Please weigh candidates against our supply-chain commitments.', 'dragon-glow' ),
			'E-commerce Developer'        => __( 'Engineering role supporting our storefront. Review for Shopify/Liquid/PHP/React experience as appropriate.', 'dragon-glow' ),
			'Brand Copywriter'            => __( 'Voice and tone are signature for Dragon Glow. Review for editorial fluency with our luxury/clean copy style.', 'dragon-glow' ),
			'Customer Concierge'          => __( 'Front-line of the brand. Look for warmth, clarity, and customer-first instincts.', 'dragon-glow' ),
			'Retail Partnerships Manager' => __( 'Sales role. Look for established relationships with prestige retailers and clean negotiation instincts.', 'dragon-glow' ),
		)
	);

	$role_intro = $intro_map[ $role ] ?? __( 'Please review this application at your convenience.', 'dragon-glow' );

	ob_start();
	?>
<p style="margin:0 0 12px;font-family:inherit;"><?php echo esc_html( $role_intro ); ?></p>

<table cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:inherit;">
	<tr><td style="color:#888;"><?php esc_html_e( 'Role', 'dragon-glow' ); ?></td><td><strong><?php echo esc_html( $role ); ?></strong></td></tr>
	<tr><td style="color:#888;"><?php esc_html_e( 'Name', 'dragon-glow' ); ?></td><td><?php echo esc_html( $values['full_name'] ?? '' ); ?></td></tr>
	<tr><td style="color:#888;"><?php esc_html_e( 'Email', 'dragon-glow' ); ?></td><td><a href="mailto:<?php echo esc_attr( $values['email'] ?? '' ); ?>" style="color:#1c1b1b;"><?php echo esc_html( $values['email'] ?? '' ); ?></a></td></tr>
	<tr><td style="color:#888;"><?php esc_html_e( 'Phone', 'dragon-glow' ); ?></td><td><?php echo esc_html( $values['phone'] ?? '—' ); ?></td></tr>
	<tr><td style="color:#888;"><?php esc_html_e( 'LinkedIn', 'dragon-glow' ); ?></td><td><?php echo esc_html( $values['linkedin'] ?? '—' ); ?></td></tr>
	<tr><td style="color:#888;vertical-align:top;"><?php esc_html_e( 'Note', 'dragon-glow' ); ?></td><td><?php echo nl2br( esc_html( $values['note'] ?? '—' ) ); ?></td></tr>
</table>

<?php if ( ! empty( $values['cv'] ) ) : ?>
	<p style="margin:14px 0 0;color:#555;font-size:0.875rem;">
		<?php esc_html_e( 'CV: attached to this email.', 'dragon-glow' ); ?>
	</p>
<?php endif; ?>

<p style="margin:22px 0 10px;font-weight:600;font-family:inherit;"><?php esc_html_e( 'Your decision', 'dragon-glow' ); ?></p>

<p style="margin:0 0 16px;font-family:inherit;">
	<a href="<?php echo esc_url( $approve_url ); ?>"
		style="display:inline-block;padding:12px 22px;background:#1c1b1b;color:#fcf9f8;text-decoration:none;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;font-size:0.75rem;margin-right:8px;">
		<?php esc_html_e( 'Approve & schedule', 'dragon-glow' ); ?>
	</a>
	<a href="<?php echo esc_url( $reject_url ); ?>"
		style="display:inline-block;padding:12px 22px;background:transparent;color:#1c1b1b;border:1px solid #1c1b1b;text-decoration:none;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;font-size:0.75rem;">
		<?php esc_html_e( 'Reject', 'dragon-glow' ); ?>
	</a>
</p>

<p style="margin:16px 0 0;color:#888;font-size:0.875rem;font-family:inherit;">
	<?php
	printf(
		esc_html__( 'These links expire in %d days and can be used once.', 'dragon-glow' ),
		14
	);
	?>
</p>
	<?php
	$body = (string) ob_get_clean();
	return (string) apply_filters( 'dg_careers_email_for_hr', $body, $values, $role, $approve_url, $reject_url );
}

/**
 * Build HTML auto-reply for the candidate.
 */
function dg_careers_email_for_candidate_ack( array $values, string $role ): string {
	ob_start();
	?>
<p style="font-family:inherit;">
	<?php printf( esc_html__( 'Hi %s,', 'dragon-glow' ), esc_html( $values['full_name'] ?? '' ) ); ?>
</p>
<p style="font-family:inherit;">
	<?php
	printf(
		esc_html__( 'Thank you for applying for the %s role at Dragon Glow. We read every application personally and will follow up within five business days.', 'dragon-glow' ),
		'<strong>' . esc_html( $role ) . '</strong>'
	);
	?>
</p>
<p style="font-family:inherit;">
	<?php
	printf(
		esc_html__( 'Warmly, %s', 'dragon-glow' ),
		esc_html( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) )
	);
	?>
</p>
	<?php
	$body = (string) ob_get_clean();
	return (string) apply_filters( 'dg_careers_email_for_candidate_ack', $body, $values, $role );
}

/**
 * Get the requester's IP.
 */
function dg_careers_apply_get_ip(): string {
	$ip = $_SERVER['REMOTE_ADDR'] ?? '';
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$parts = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		$ip    = trim( $parts[0] );
	}
	return (string) apply_filters( 'dg_careers_apply_ip', $ip );
}

add_action( 'wp_ajax_dg_submit_application',        'dg_ajax_submit_application' );
add_action( 'wp_ajax_nopriv_dg_submit_application', 'dg_ajax_submit_application' );
