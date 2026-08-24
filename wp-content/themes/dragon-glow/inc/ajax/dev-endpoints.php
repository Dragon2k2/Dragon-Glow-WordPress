<?php
/**
 * Dragon Glow — Brevo one-time setup / diagnostic endpoints.
 *
 * ONE-TIME setup endpoints (remove after the key is set) — secured by manage_options.
 *   POST action=dg_set_brevo_key,          body: key=API_KEY
 *   GET  action=dg_show_brevo_key_prefix   (returns first 14 chars only)
 *   GET  action=dg_test_brevo_send         (sends test email to current user)
 *   GET  action=dg_test_brevo_ping         (verifies key + send + reports server IP)
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_dg_show_brevo_key_prefix', function(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ) );
	}
	$key = dg_brevo_get_api_key();
	wp_send_json_success( array(
		'has_key'    => (bool) $key,
		'key_prefix' => $key ? substr( $key, 0, 14 ) . '...' : null,
		'key_length' => $key ? strlen( $key ) : 0,
		'option_set' => (bool) get_option( 'dg_brevo_api_key' ),
	) );
} );

add_action( 'wp_ajax_dg_set_brevo_key', function(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ) );
	}
	$key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );
	if ( '' === $key ) {
		wp_send_json_error( array( 'message' => 'Empty key' ) );
	}
	update_option( 'dg_brevo_api_key', $key, false );
	wp_send_json_success( array(
		'message'    => 'Key saved.',
		'key_prefix' => substr( $key, 0, 14 ) . '...',
		'key_length' => strlen( $key ),
	) );
} );

add_action( 'wp_ajax_dg_test_brevo_send', function(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ) );
	}

	$key = dg_brevo_get_api_key();
	if ( ! $key ) {
		wp_send_json_success( array(
			'sent'     => false,
			'reason'   => 'no_key',
			'response' => 'API key not found',
		) );
	}

	$user    = wp_get_current_user();
	$payload = array(
		'sender'  => array(
			'name'  => 'Dragon Glow',
			'email' => apply_filters( 'dg_careers_from_email', 'anhthuymaiphuong698@gmail.com' ),
		),
		'to'      => array(
			array(
				'name'  => $user->display_name,
				'email' => $user->user_email,
			),
		),
		'subject' => '[Dragon Glow] Brevo test',
		'htmlContent' => '<p>If you see this, Brevo is wired up.</p><p>Sent: ' . current_time( 'mysql' ) . '</p>',
	);

	$response = wp_remote_post(
		'https://api.brevo.com/v3/smtp/email',
		array(
			'headers' => array(
				'api-key'      => $key,
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $payload ),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_success( array(
			'sent'     => false,
			'reason'   => 'wp_error',
			'response' => $response->get_error_message(),
		) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	wp_send_json_success( array(
		'sent'     => $code >= 200 && $code < 300,
		'reason'   => $code >= 200 && $code < 300 ? 'ok' : 'brevo_error',
		'code'     => $code,
		'response' => $body,
	) );
} );

add_action( 'wp_ajax_dg_test_brevo_ping', function(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ) );
	}

	$key = dg_brevo_get_api_key();
	if ( ! $key ) {
		wp_send_json_success( array( 'ok' => false, 'reason' => 'no_key' ) );
	}

	// Test 1: GET /v3/account (just verify key works)
	$resp_account = wp_remote_get( 'https://api.brevo.com/v3/account', array(
		'headers' => array( 'api-key' => $key ),
		'timeout' => 15,
	) );

	$account_code = is_wp_error( $resp_account ) ? 'wp_error' : wp_remote_retrieve_response_code( $resp_account );
	$account_body = is_wp_error( $resp_account ) ? $resp_account->get_error_message() : wp_remote_retrieve_body( $resp_account );

	// Test 2: POST /v3/smtp/email (full send)
	$user = wp_get_current_user();
	$payload = array(
		'sender'  => array( 'name' => 'Dragon Glow', 'email' => 'anhthuymaiphuong698@gmail.com' ),
		'to'      => array( array( 'name' => $user->display_name, 'email' => $user->user_email ) ),
		'subject' => '[Dragon Glow] Brevo ping',
		'htmlContent' => '<p>ping</p>',
	);
	$resp_send = wp_remote_post( 'https://api.brevo.com/v3/smtp/email', array(
		'headers' => array( 'api-key' => $key, 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode( $payload ),
		'timeout' => 15,
	) );

	$send_code = is_wp_error( $resp_send ) ? 'wp_error' : wp_remote_retrieve_response_code( $resp_send );
	$send_body = is_wp_error( $resp_send ) ? $resp_send->get_error_message() : wp_remote_retrieve_body( $resp_send );

	// Server info
	$server_ip = wp_remote_get( 'https://api.ipify.org', array( 'timeout' => 10 ) );
	$server_ip_str = is_wp_error( $server_ip ) ? 'unknown' : wp_remote_retrieve_body( $server_ip );

	wp_send_json_success( array(
		'server_ip'    => $server_ip_str,
		'account_test' => array( 'code' => $account_code, 'body' => substr( $account_body, 0, 400 ) ),
		'send_test'    => array( 'code' => $send_code, 'body' => substr( $send_body, 0, 400 ) ),
	) );
} );
