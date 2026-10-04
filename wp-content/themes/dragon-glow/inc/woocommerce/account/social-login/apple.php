<?php
/**
 * Dragon Glow — Social Login: Sign in with Apple
 *
 * Server-side flow, same shape as google.php:
 *
 *   1. User clicks the Apple button → GET /my-account/?dg_social_login=apple
 *      → dg_apple_login_start() redirects to Apple's authorize endpoint.
 *   2. Apple POSTs back to the redirect URI (`response_mode=form_post` is
 *      REQUIRED by Apple whenever the `email`/`name` scope is requested)
 *      → dg_apple_login_callback() exchanges the code for tokens, verifies
 *        the ID token against Apple's published JWKs, maps/creates the WP
 *        user, logs them in.
 *
 * Unlike Google, Apple has no simple "tokeninfo" verification endpoint, so
 * the ID token's RS256 signature is verified locally against Apple's JWKs
 * (fetched + cached) using only `openssl` (bundled with PHP) — no Composer
 * JWT library needed, keeping the theme build-step-free.
 *
 * The "client secret" Apple expects is itself a JWT that we sign with the
 * ES256 private key downloaded from the Apple Developer portal.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

const DG_APPLE_AUTH_ENDPOINT  = 'https://appleid.apple.com/auth/authorize';
const DG_APPLE_TOKEN_ENDPOINT = 'https://appleid.apple.com/auth/token';
const DG_APPLE_KEYS_ENDPOINT  = 'https://appleid.apple.com/auth/keys';

/**
 * Redirect the browser to Apple's Sign in with Apple consent screen.
 *
 * @param string $redirect_to Where to send the user after a successful sign-in
 *                             (the page they were on before clicking "Apple").
 *                             Already validated by dg_account_safe_redirect_target().
 * @return void (redirects then exits).
 */
function dg_apple_login_start( string $redirect_to = '' ): void {
	if ( ! dg_social_login_apple_enabled() ) {
		dg_social_login_fail( __( 'Apple sign-in is not available right now. Please use your email and password.', 'dragon-glow' ) );
	}

	$state = wp_generate_password( 32, false );
	// Carries redirect_to through the round trip since Apple's consent
	// screen doesn't preserve our query string.
	set_transient(
		'dg_social_state_' . $state,
		array(
			'provider'    => 'apple',
			'redirect_to' => $redirect_to,
		),
		10 * MINUTE_IN_SECONDS
	);

	$url = add_query_arg(
		array(
			'client_id'     => rawurlencode( DG_APPLE_SERVICES_ID ),
			'redirect_uri'  => rawurlencode( dg_social_login_redirect_uri() ),
			'response_type' => 'code',
			'response_mode' => 'form_post', // Required by Apple when requesting the "email" scope.
			'scope'         => rawurlencode( 'name email' ),
			'state'         => rawurlencode( $state ),
		),
		DG_APPLE_AUTH_ENDPOINT
	);

	wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect -- fixed Apple endpoint, not user input.
	exit;
}

/**
 * Handle Apple's `form_post` callback with an authorization code.
 *
 * @param string $code  Authorization code from Apple.
 * @param string $state State token to verify against the one we issued.
 * @return void (redirects then exits).
 */
function dg_apple_login_callback( string $code, string $state ): void {
	if ( ! dg_social_login_apple_enabled() ) {
		dg_social_login_fail( __( 'Apple sign-in is not available right now.', 'dragon-glow' ) );
	}

	$payload = get_transient( 'dg_social_state_' . $state );
	delete_transient( 'dg_social_state_' . $state );
	if ( ! is_array( $payload ) || 'apple' !== ( $payload['provider'] ?? '' ) ) {
		dg_social_login_fail( __( 'Your Apple sign-in request expired or was invalid. Please try again.', 'dragon-glow' ) );
	}
	$redirect_to = (string) ( $payload['redirect_to'] ?? '' );

	$client_secret = dg_apple_generate_client_secret();
	if ( is_wp_error( $client_secret ) ) {
		dg_social_login_fail( $client_secret->get_error_message() );
	}

	$response = wp_remote_post(
		DG_APPLE_TOKEN_ENDPOINT,
		array(
			'timeout' => 15,
			'body'    => array(
				'code'          => $code,
				'client_id'     => DG_APPLE_SERVICES_ID,
				'client_secret' => $client_secret,
				'redirect_uri'  => dg_social_login_redirect_uri(),
				'grant_type'    => 'authorization_code',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		dg_social_login_fail( __( 'Could not reach Apple. Please try again.', 'dragon-glow' ) );
	}

	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) || empty( $body['id_token'] ) ) {
		dg_social_login_fail( __( 'Apple did not confirm your identity. Please try again.', 'dragon-glow' ) );
	}

	$claims = dg_apple_verify_id_token( (string) $body['id_token'] );
	if ( is_wp_error( $claims ) ) {
		dg_social_login_fail( $claims->get_error_message() );
	}

	if ( empty( $claims['email'] ) ) {
		dg_social_login_fail( __( 'Apple did not share an email address for this account.', 'dragon-glow' ) );
	}

	// Apple only sends `name` in the POST body on the very first consent —
	// never inside the ID token. Capture it here if present.
	$first_name = '';
	$last_name  = '';
	if ( isset( $_POST['user'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Apple's own form_post, no WP nonce exists for a 3rd-party redirect.
		$user_blob = json_decode( sanitize_text_field( wp_unslash( $_POST['user'] ) ), true );
		if ( is_array( $user_blob ) && ! empty( $user_blob['name'] ) ) {
			$first_name = isset( $user_blob['name']['firstName'] ) ? sanitize_text_field( $user_blob['name']['firstName'] ) : '';
			$last_name  = isset( $user_blob['name']['lastName'] ) ? sanitize_text_field( $user_blob['name']['lastName'] ) : '';
		}
	}

	$user_id = dg_social_login_find_or_create_user(
		'apple',
		(string) $claims['sub'],
		(string) $claims['email'],
		$first_name,
		$last_name
	);

	if ( is_wp_error( $user_id ) ) {
		dg_social_login_fail( $user_id->get_error_message() );
	}

	dg_social_login_authenticate_and_redirect( $user_id, $redirect_to );
}

/**
 * Build the ES256-signed JWT Apple requires as the OAuth `client_secret`.
 *
 * Apple does not issue a static client secret — it must be a short-lived
 * JWT (max 6 months validity) signed with the private key downloaded once
 * from the Apple Developer portal (Keys → Sign in with Apple).
 *
 * @return string|WP_Error Signed JWT, or WP_Error if the private key is unusable.
 */
function dg_apple_generate_client_secret() {
	$header  = dg_jwt_base64url_encode( wp_json_encode( array( 'alg' => 'ES256', 'kid' => DG_APPLE_KEY_ID ) ) );
	$now     = time();
	$payload = dg_jwt_base64url_encode(
		wp_json_encode(
			array(
				'iss' => DG_APPLE_TEAM_ID,
				'iat' => $now,
				'exp' => $now + ( 5 * MINUTE_IN_SECONDS ), // Short-lived — regenerated on every sign-in.
				'aud' => 'https://appleid.apple.com',
				'sub' => DG_APPLE_SERVICES_ID,
			)
		)
	);

	$unsigned = $header . '.' . $payload;

	$private_key = openssl_pkey_get_private( DG_APPLE_PRIVATE_KEY );
	if ( false === $private_key ) {
		return new WP_Error( 'dg_apple_invalid_private_key', __( 'Apple sign-in is misconfigured (invalid private key). Please contact support.', 'dragon-glow' ) );
	}

	$signature = '';
	$signed    = openssl_sign( $unsigned, $signature, $private_key, OPENSSL_ALGO_SHA256 );
	if ( ! $signed ) {
		return new WP_Error( 'dg_apple_sign_failed', __( 'Apple sign-in is misconfigured (could not sign request). Please contact support.', 'dragon-glow' ) );
	}

	// ES256 (JWS) requires the raw (r,s) concatenation, not the DER
	// signature openssl_sign() produces by default — convert it.
	$jws_signature = dg_jwt_der_to_jws_signature( $signature );
	if ( '' === $jws_signature ) {
		return new WP_Error( 'dg_apple_signature_convert_failed', __( 'Apple sign-in is misconfigured (signature error). Please contact support.', 'dragon-glow' ) );
	}

	return $unsigned . '.' . dg_jwt_base64url_encode( $jws_signature );
}

/**
 * Verify an Apple-issued ID token (JWT) against Apple's published JWKs.
 *
 * Apple rotates its signing keys periodically but exposes them all at
 * `DG_APPLE_KEYS_ENDPOINT`. We cache the key set for 24h (transient) to
 * avoid a network round trip on every sign-in while still picking up
 * rotations within a day.
 *
 * @param string $id_token Raw JWT from the token endpoint response.
 * @return array<string, mixed>|WP_Error Decoded claims, or WP_Error on failure.
 */
function dg_apple_verify_id_token( string $id_token ) {
	$segments = explode( '.', $id_token );
	if ( 3 !== count( $segments ) ) {
		return new WP_Error( 'dg_apple_token_malformed', __( 'Apple returned a malformed identity token.', 'dragon-glow' ) );
	}
	list( $header_b64, $payload_b64, $signature_b64 ) = $segments;

	$header = json_decode( dg_jwt_base64url_decode( $header_b64 ), true );
	$claims = json_decode( dg_jwt_base64url_decode( $payload_b64 ), true );
	if ( ! is_array( $header ) || ! is_array( $claims ) || empty( $header['kid'] ) ) {
		return new WP_Error( 'dg_apple_token_malformed', __( 'Apple returned a malformed identity token.', 'dragon-glow' ) );
	}

	$public_key = dg_apple_get_jwk_public_key( (string) $header['kid'] );
	if ( is_wp_error( $public_key ) ) {
		return $public_key;
	}

	$signature = dg_jwt_base64url_decode( $signature_b64 );
	$der_sig   = dg_jwt_jws_to_der_signature( $signature );
	$verified  = openssl_verify( $header_b64 . '.' . $payload_b64, $der_sig, $public_key, OPENSSL_ALGO_SHA256 );

	if ( 1 !== $verified ) {
		return new WP_Error( 'dg_apple_signature_invalid', __( 'Your Apple identity token could not be verified.', 'dragon-glow' ) );
	}

	if ( empty( $claims['sub'] ) ) {
		return new WP_Error( 'dg_apple_token_invalid', __( 'Apple returned an invalid identity response.', 'dragon-glow' ) );
	}

	if ( empty( $claims['aud'] ) || DG_APPLE_SERVICES_ID !== (string) $claims['aud'] ) {
		return new WP_Error( 'dg_apple_aud_mismatch', __( 'This Apple identity token was not issued for this site.', 'dragon-glow' ) );
	}

	if ( empty( $claims['iss'] ) || 'https://appleid.apple.com' !== (string) $claims['iss'] ) {
		return new WP_Error( 'dg_apple_iss_mismatch', __( 'This Apple identity token has an unexpected issuer.', 'dragon-glow' ) );
	}

	if ( empty( $claims['exp'] ) || time() > (int) $claims['exp'] ) {
		return new WP_Error( 'dg_apple_token_expired', __( 'Your Apple identity token has expired. Please try again.', 'dragon-glow' ) );
	}

	return $claims;
}

/**
 * Fetch (and cache) Apple's JWKs, then build an OpenSSL public key resource
 * for the given `kid`.
 *
 * @param string $kid Key ID from the JWT header.
 * @return resource|OpenSSLAsymmetricKey|WP_Error PEM public key resource, or WP_Error.
 */
function dg_apple_get_jwk_public_key( string $kid ) {
	$jwks = get_transient( 'dg_apple_jwks' );
	if ( false === $jwks ) {
		$response = wp_remote_get( DG_APPLE_KEYS_ENDPOINT, array( 'timeout' => 15 ) );
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'dg_apple_jwks_unreachable', __( 'Could not verify your Apple identity. Please try again.', 'dragon-glow' ) );
		}
		$jwks = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $jwks ) || empty( $jwks['keys'] ) ) {
			return new WP_Error( 'dg_apple_jwks_invalid', __( 'Apple returned an invalid key set.', 'dragon-glow' ) );
		}
		set_transient( 'dg_apple_jwks', $jwks, DAY_IN_SECONDS );
	}

	foreach ( $jwks['keys'] as $jwk ) {
		if ( isset( $jwk['kid'] ) && $kid === $jwk['kid'] ) {
			return dg_jwk_rsa_to_pem( (string) $jwk['n'], (string) $jwk['e'] );
		}
	}

	return new WP_Error( 'dg_apple_jwk_not_found', __( 'Apple used a signing key this site does not recognize. Please try again.', 'dragon-glow' ) );
}
