<?php
/**
 * Dragon Glow — Careers: Approval route + token helpers
 *
 * Hooks template_redirect to route any request with ?dg_action=approve|reject
 * to the dedicated approval template. Also exposes token consume / finalise
 * helpers used by the decision flow.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Route any request with ?dg_action=approve|reject to a dedicated template.
 *
 * Uses template_redirect so the URL stays the same as the email link (HR doesn't
 * need to know it's a special page). The same URL gets the form on GET and the
 * result on POST.
 *
 * @return void
 */
function dg_approval_route(): void {
	if ( empty( $_GET['dg_action'] ) || empty( $_GET['token'] ) ) {
		return;
	}

	$action = sanitize_key( wp_unslash( $_GET['dg_action'] ) );
	if ( ! in_array( $action, array( 'approve', 'reject' ), true ) ) {
		return;
	}

	if ( is_admin() ) {
		return;
	}

	$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );

	status_header( 200 );
	nocache_headers();

	$template_path = locate_template( 'template-parts/careers/approval-page.php' );
	if ( ! $template_path ) {
		wp_die(
			esc_html__( 'Approval template is missing. Please contact the site administrator.', 'dragon-glow' ),
			esc_html__( 'Approval', 'dragon-glow' ),
			array( 'response' => 500 )
		);
	}

	// Variables exposed to the template.
	$dg_action    = $action;
	$dg_token     = $token;
	$dg_payload   = dg_approval_consume_token( $action, $token );
	$dg_post_data = wp_unslash( $_POST );
	$dg_processed = null;

	if ( ! empty( $_POST['dg_approval_submit'] ) && $dg_payload ) {
		check_admin_referer( 'dg_approval_decision_' . $action, 'dg_approval_nonce' );
		// Stash token so process can delete the transient on success.
		$dg_payload['_token'] = $token;
		$dg_processed         = dg_approval_process_decision( $action, $dg_payload, $dg_post_data );
	}

	get_header();
	include $template_path;
	get_footer();
	exit;
}
add_action( 'template_redirect', 'dg_approval_route' );

/**
 * Validate token → return payload or null.
 *
 * @param string $action 'approve'|'reject'.
 * @param string $token  Token from URL.
 * @return array<string,mixed>|null
 */
function dg_approval_consume_token( string $action, string $token ): ?array {
	if ( '' === $token || strlen( $token ) < 16 ) {
		return null;
	}
	$key     = 'dg_app_' . $action . '_' . $token;
	$payload = get_transient( $key );
	return is_array( $payload ) ? $payload : null;
}

/**
 * Delete both transient forms so the link becomes single-use.
 *
 * @param string $token Token from URL.
 */
function dg_approval_finalise_token( string $token ): void {
	delete_transient( 'dg_app_approve_' . $token );
	delete_transient( 'dg_app_reject_' . $token );
}
