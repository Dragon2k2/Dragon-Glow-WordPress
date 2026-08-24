<?php
/**
 * Dragon Glow — Brevo / Sendinblue direct API integration.
 *
 * Sends transactional email via the Brevo API directly, bypassing the
 * WP Mail SMTP plugin (fixes email not sending from the AJAX front-end
 * context). Requires a Brevo API key:
 *   Brevo Dashboard → SMTP → Configuration → API Key
 *
 * API key resolution order:
 *   1. `dg_brevo_api_key` filter.
 *   2. `dg_brevo_api_key` WordPress option.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Brevo API key from WordPress options (set by WP Mail SMTP plugin) or filter.
 * Falls back to a hardcoded key via `dg_brevo_api_key` filter or `wp_options` 'dg_brevo_api_key'.
 *
 * @return string|null
 */
function dg_brevo_get_api_key(): ?string {
	// 1. Filter (allows setting via functions.php, mu-plugins, or wp-config helper).
	$filtered = apply_filters( 'dg_brevo_api_key', '' );
	if ( is_string( $filtered ) && '' !== $filtered ) {
		return $filtered;
	}

	// 2. WordPress option (private to this theme).
	$opt = get_option( 'dg_brevo_api_key' );
	if ( is_string( $opt ) && '' !== $opt ) {
		return $opt;
	}

	return null;
}

/**
 * Upload a file to Brevo and return the attachment ID.
 *
 * @param string $file_path Absolute path to file on disk.
 * @param string $api_key   Brevo API key.
 *
 * @return string|null Attachment ID or null on failure.
 */
/**
 * Build Brevo attachment array from a CV file (inline base64, no separate upload).
 *
 * @param string $file_path Absolute path to the file on disk.
 * @return array|null Brevo attachment item `['content' => base64, 'name' => string]` or null on failure.
 */
function dg_build_brevo_attachment( string $file_path ): ?array {
	if ( ! file_exists( $file_path ) ) {
		error_log( "[Dragon Glow] Brevo attachment: file not found: {$file_path}" );
		return null;
	}
	if ( ! is_readable( $file_path ) ) {
		error_log( "[Dragon Glow] Brevo attachment: file not readable: {$file_path}" );
		return null;
	}

	$raw = file_get_contents( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	if ( false === $raw ) {
		error_log( "[Dragon Glow] Brevo attachment: file_get_contents failed for {$file_path}" );
		return null;
	}

	return array(
		'name'    => basename( $file_path ),
		'content' => base64_encode( $raw ),
	);
}

/**
 * Send an email via Brevo transactional API (SMTP relay).
 *
 * @param array  $args {
 *     @type string       $to_email        Recipient email.
 *     @type string       $to_name         Recipient name.
 *     @type string       $subject         Email subject.
 *     @type string       $html_body       Email HTML body.
 *     @type string       $from_email      Sender email (must be a verified domain in Brevo).
 *     @type string       $from_name       Sender name.
 *     @type string|null  $reply_to_email  Reply-to email (optional).
 *     @type string|null  $reply_to_name   Reply-to name (optional).
 *     @type string|null  $cv_path         Absolute server path to CV file (optional).
 * }
 *
 * @return bool True if email was queued/accepted by Brevo, false otherwise.
 */
function dg_send_brevo_email( array $args ): bool {
	$api_key = dg_brevo_get_api_key();
	if ( ! $api_key ) {
		error_log( '[Dragon Glow] Brevo API key not found. Set via `dg_brevo_api_key` filter or WP Mail SMTP settings.' );
		return false;
	}

	$to_email       = $args['to_email']       ?? '';
	$to_name        = $args['to_name']        ?? '';
	$subject        = $args['subject']        ?? '';
	$html_body      = $args['html_body']      ?? '';
	$from_email     = $args['from_email']     ?? 'anhthuymaiphuong698@gmail.com';
	$from_name      = $args['from_name']      ?? 'Dragon Glow';
	$reply_to_email = $args['reply_to_email'] ?? null;
	$reply_to_name  = $args['reply_to_name']  ?? null;
	$cv_path        = $args['cv_path']        ?? null;

	if ( ! is_email( $to_email ) || ! is_email( $from_email ) || ! $subject ) {
		error_log( '[Dragon Glow] dg_send_brevo_email: invalid params.' );
		return false;
	}

	$payload = array(
		'sender'      => array(
			'name'  => $from_name,
			'email' => $from_email,
		),
		'to'          => array(
			array(
				'name'  => $to_name ?: $to_email,
				'email' => $to_email,
			),
		),
		'subject'     => $subject,
		'htmlContent' => $html_body,
	);

	if ( $reply_to_email && is_email( $reply_to_email ) ) {
		$payload['replyTo'] = array(
			'name'  => $reply_to_name ?: $reply_to_email,
			'email' => $reply_to_email,
		);
	}

	// Attach CV if provided.
	if ( $cv_path && is_string( $cv_path ) ) {
		$attachment = dg_build_brevo_attachment( $cv_path );
		if ( $attachment ) {
			$payload['attachment'] = array( $attachment );
		}
	}

	$response = wp_remote_post(
		'https://api.brevo.com/v3/smtp/email',
		array(
			'headers' => array(
				'api-key'      => $api_key,
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $payload ),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( '[Dragon Glow] Brevo API failed: ' . $response->get_error_message() );
		return false;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		$body = wp_remote_retrieve_body( $response );
		error_log( "[Dragon Glow] Brevo API error {$code}: {$body}" );
		return false;
	}

	return true;
}
