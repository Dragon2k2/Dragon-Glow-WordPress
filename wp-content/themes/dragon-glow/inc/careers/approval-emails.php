<?php
/**
 * Dragon Glow — Careers: Approval email body builders
 *
 * Each builder returns the HTML body for the candidate-facing email. The HTML
 * itself lives in template-parts/emails/ so designers can edit it without
 * touching PHP logic.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build interview-invitation HTML email for the candidate (Cách A — approve).
 *
 * @param array  $payload  Submission payload.
 * @param string $date     YYYY-MM-DD.
 * @param string $time     HH:MM.
 * @param string $location Location or video link.
 * @param string $duration Duration in minutes.
 * @param string $message  Optional HR note.
 * @return string HTML body.
 */
function dg_careers_email_for_candidate_approve( array $payload, string $date, string $time, string $location, string $duration, string $message ): string {
	$role    = $payload['role'] ?? '';
	$name    = $payload['name'] ?? '';
	$hr_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$role_intro_map = (array) apply_filters(
		'dg_careers_role_approve_intro',
		array(
			'Formulation Chemist'         => __( 'For the lab portion of the interview, please bring any sample formulations or stability data you would like to walk us through.', 'dragon-glow' ),
			'Retail Partnerships Manager' => __( 'The first interview is a portfolio walkthrough — bring case studies from your strongest brand partnerships.', 'dragon-glow' ),
		)
	);
	$role_intro = $role_intro_map[ $role ] ?? __( 'The interview will run for about 45 minutes and cover your background, the role, and next steps.', 'dragon-glow' );

	$dt         = new DateTimeImmutable( $date . 'T' . $time . ':00', wp_timezone() );
	$date_human = $dt->format( 'l, F j, Y' );
	$time_human = $dt->format( 'g:i A T' );

	$body = dg_render_email_template(
		'emails/approval-accept',
		array(
			'name'        => $name,
			'role'        => $role,
			'role_intro'  => $role_intro,
			'date_human'  => $date_human,
			'time_human'  => $time_human,
			'duration'    => $duration,
			'location'    => $location,
			'message'     => $message,
			'hr_name'     => $hr_name,
		)
	);

	/**
	 * Lọc HTML body của email approve trước khi gửi.
	 *
	 * @param string $body     HTML body đã render.
	 * @param array  $payload  Submission payload gốc.
	 * @param string $date     YYYY-MM-DD.
	 * @param string $time     HH:MM.
	 * @param string $location Location / video link.
	 * @param string $duration Duration in minutes.
	 * @param string $message  Optional HR note.
	 */
	return (string) apply_filters( 'dg_careers_email_for_candidate_approve', $body, $payload, $date, $time, $location, $duration, $message );
}

/**
 * Build rejection-email HTML for the candidate.
 *
 * @param array  $payload Submission payload.
 * @param string $reason  Optional rejection reason.
 * @return string HTML body.
 */
function dg_careers_email_for_candidate_reject( array $payload, string $reason ): string {
	$role    = $payload['role'] ?? '';
	$name    = $payload['name'] ?? '';
	$hr_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$role_intro_map = (array) apply_filters(
		'dg_careers_role_reject_intro',
		array()
	);
	$role_intro = $role_intro_map[ $role ] ?? __( 'After careful consideration, we have decided not to move forward with your application at this time.', 'dragon-glow' );

	$body = dg_render_email_template(
		'emails/approval-reject',
		array(
			'name'       => $name,
			'role'       => $role,
			'role_intro' => $role_intro,
			'reason'     => $reason,
			'hr_name'    => $hr_name,
		)
	);

	/**
	 * Lọc HTML body của email reject trước khi gửi.
	 *
	 * @param string $body    HTML body đã render.
	 * @param array  $payload Submission payload gốc.
	 * @param string $reason  Rejection reason.
	 */
	return (string) apply_filters( 'dg_careers_email_for_candidate_reject', $body, $payload, $reason );
}

/**
 * Render an email template with the given context.
 *
 * Locates the template at `template-parts/{slug}.php`, exposes `$email`
 * (the context array) to the template scope, captures the output, and
 * returns it as a string.
 *
 * @param string $slug     Template slug relative to `template-parts/`.
 * @param array  $context  Variables to expose to the template.
 * @return string Captured output, empty string if template not found.
 */
function dg_render_email_template( string $slug, array $context ): string {
	$template_path = locate_template( 'template-parts/' . $slug . '.php' );
	if ( ! $template_path ) {
		return '';
	}

	$email = $context;

	ob_start();
	include $template_path;
	return (string) ob_get_clean();
}
