<?php
/**
 * Dragon Glow — Social Login: Google (Authorization Code flow)
 *
 * Server-side redirect flow (no client-side Google Identity Services JS,
 * no extra CDN script, no CORS/postMessage handling) — matches the theme's
 * "vanilla, no build step" constraint:
 *
 *   1. User clicks the Google button → GET /my-account/?dg_social_login=google
 *      → dg_social_login_route_start() redirects to Google's consent screen.
 *   2. Google redirects back to DG_SOCIAL_LOGIN redirect URI with ?code=...&state=...
 *      → dg_social_login_route_callback() exchanges the code for tokens,
 *        verifies the ID token, maps/creates the WP user, logs them in.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

const DG_GOOGLE_AUTH_ENDPOINT  = 'https://accounts.google.com/o/oauth2/v2/auth';
const DG_GOOGLE_TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';

/**
 * Redirect the browser to Google's OAuth consent screen.
 *
 * @param string $redirect_to Where to send the user after a successful sign-in
 *                             (the page they were on before clicking "Google").
 *                             Already validated by dg_account_safe_redirect_target().
 * @return void (redirects then exits).
 */
function dg_google_login_start( string $redirect_to = '' ): void {
	if ( ! dg_social_login_google_enabled() ) {
		dg_social_login_fail( __( 'Google sign-in is not available right now. Please use your email and password.', 'dragon-glow' ) );
	}

	$state = wp_generate_password( 32, false );
	// Short-lived transient (10 min) keyed by state — CSRF protection + lets
	// the callback confirm this exact browser initiated the request. Also
	// carries redirect_to through the round trip since Google's consent
	// screen doesn't preserve our query string.
	set_transient(
		'dg_social_state_' . $state,
		array(
			'provider'    => 'google',
			'redirect_to' => $redirect_to,
		),
		10 * MINUTE_IN_SECONDS
	);

	$url = add_query_arg(
		array(
			'client_id'     => rawurlencode( DG_GOOGLE_CLIENT_ID ),
			'redirect_uri'  => rawurlencode( dg_social_login_redirect_uri() ),
			'response_type' => 'code',
			'scope'         => rawurlencode( 'openid email profile' ),
			'state'         => rawurlencode( $state ),
			'prompt'        => 'select_account',
		),
		DG_GOOGLE_AUTH_ENDPOINT
	);

	wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect -- fixed Google endpoint, not user input.
	exit;
}

/**
 * Handle Google's redirect back with an authorization code.
 *
 * @param string $code  Authorization code from Google.
 * @param string $state State token to verify against the one we issued.
 * @return void (redirects then exits).
 */
function dg_google_login_callback( string $code, string $state ): void {
	if ( ! dg_social_login_google_enabled() ) {
		dg_social_login_fail( __( 'Google sign-in is not available right now.', 'dragon-glow' ) );
	}

	$payload = get_transient( 'dg_social_state_' . $state );
	delete_transient( 'dg_social_state_' . $state );
	if ( ! is_array( $payload ) || 'google' !== ( $payload['provider'] ?? '' ) ) {
		dg_social_login_fail( __( 'Your Google sign-in request expired or was invalid. Please try again.', 'dragon-glow' ) );
	}
	$redirect_to = (string) ( $payload['redirect_to'] ?? '' );

	$response = wp_remote_post(
		DG_GOOGLE_TOKEN_ENDPOINT,
		array(
			'timeout' => 15,
			'body'    => array(
				'code'          => $code,
				'client_id'     => DG_GOOGLE_CLIENT_ID,
				'client_secret' => DG_GOOGLE_CLIENT_SECRET,
				'redirect_uri'  => dg_social_login_redirect_uri(),
				'grant_type'    => 'authorization_code',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		dg_social_login_fail( __( 'Could not reach Google. Please try again.', 'dragon-glow' ) );
	}

	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) || empty( $body['id_token'] ) ) {
		dg_social_login_fail( __( 'Google did not confirm your identity. Please try again.', 'dragon-glow' ) );
	}

	$claims = dg_google_verify_id_token( (string) $body['id_token'] );
	if ( is_wp_error( $claims ) ) {
		dg_social_login_fail( $claims->get_error_message() );
	}

	if ( empty( $claims['email_verified'] ) || 'true' !== (string) $claims['email_verified'] ) {
		dg_social_login_fail( __( 'Your Google email is not verified. Please verify it with Google first.', 'dragon-glow' ) );
	}

	$user_id = dg_social_login_find_or_create_user(
		'google',
		(string) $claims['sub'],
		(string) $claims['email'],
		isset( $claims['given_name'] ) ? (string) $claims['given_name'] : '',
		isset( $claims['family_name'] ) ? (string) $claims['family_name'] : ''
	);

	if ( is_wp_error( $user_id ) ) {
		dg_social_login_fail( $user_id->get_error_message() );
	}

	dg_social_login_authenticate_and_redirect( $user_id, $redirect_to );
}

/**
 * Verify a Google-issued ID token (JWT) via Google's `tokeninfo` endpoint.
 *
 * Using Google's own verification endpoint — instead of locally validating
 * the RS256 signature against Google's rotating JWKs — avoids pulling a JWT
 * library into a build-step-free theme. The round trip (~100-200ms) only
 * happens once per sign-in, at a point where the user is already waiting
 * on a redirect, so the added latency is imperceptible.
 *
 * @param string $id_token Raw JWT from the token endpoint response.
 * @return array<string, mixed>|WP_Error Decoded claims, or WP_Error on failure.
 */
function dg_google_verify_id_token( string $id_token ) {
	$response = wp_remote_get(
		add_query_arg( 'id_token', rawurlencode( $id_token ), 'https://oauth2.googleapis.com/tokeninfo' ),
		array( 'timeout' => 15 )
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'dg_google_tokeninfo_unreachable', __( 'Could not verify your Google identity. Please try again.', 'dragon-glow' ) );
	}

	$claims = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $claims ) || empty( $claims['sub'] ) || empty( $claims['email'] ) ) {
		return new WP_Error( 'dg_google_tokeninfo_invalid', __( 'Google returned an invalid identity response.', 'dragon-glow' ) );
	}

	// aud must match our Client ID — otherwise the token could have been
	// issued for a different application (token substitution attack).
	if ( empty( $claims['aud'] ) || DG_GOOGLE_CLIENT_ID !== (string) $claims['aud'] ) {
		return new WP_Error( 'dg_google_tokeninfo_aud_mismatch', __( 'This Google identity token was not issued for this site.', 'dragon-glow' ) );
	}

	return $claims;
}
