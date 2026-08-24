<?php
/**
 * Dragon Glow — Careers: Approval decision processor
 *
 * Handles POST submission from the HR approval form:
 *   - validates inputs
 *   - builds email body via dg_careers_email_for_candidate_*
 *   - sends via Brevo (preferred) or wp_mail() (fallback)
 *   - finalises the single-use token
 *   - returns an `{ ok, message }` array used by the template for feedback
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Process HR decision on POST: validate, send email, return result.
 *
 * @param string              $action  'approve'|'reject'.
 * @param array<string,mixed> $payload Submission payload (must contain _token).
 * @param array<string,mixed> $post    $_POST data, slashed.
 * @return array{ok: bool, message: string}
 */
function dg_approval_process_decision( string $action, array $payload, array $post ): array {
	$from_email = (string) apply_filters( 'dg_careers_from_email', 'anhthuymaiphuong698@gmail.com' );
	$from_name  = (string) apply_filters( 'dg_careers_from_name', 'Dragon Glow Careers' );

	if ( 'approve' === $action ) {
		$date     = sanitize_text_field( $post['dg_interview_date'] ?? '' );
		$time     = sanitize_text_field( $post['dg_interview_time'] ?? '' );
		$location = sanitize_text_field( $post['dg_interview_location'] ?? '' );
		$duration = sanitize_text_field( $post['dg_interview_duration'] ?? '45' );
		$message  = sanitize_textarea_field( $post['dg_interview_message'] ?? '' );

		if ( empty( $date ) || empty( $time ) || empty( $location ) ) {
			return array(
				'ok'      => false,
				'message' => __( 'Please enter the date, time and location (or video link) for the interview.', 'dragon-glow' ),
			);
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return array(
				'ok'      => false,
				'message' => __( 'Date format must be YYYY-MM-DD.', 'dragon-glow' ),
			);
		}
		if ( ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			return array(
				'ok'      => false,
				'message' => __( 'Time format must be HH:MM (24h).', 'dragon-glow' ),
			);
		}

		$body = dg_careers_email_for_candidate_approve( $payload, $date, $time, $location, $duration, $message );

		$sent = dg_send_brevo_email( array(
			'to_email'       => $payload['email'],
			'to_name'        => $payload['name'] ?? '',
			'subject'        => sprintf( '[Dragon Glow] Interview invitation — %s', $payload['role'] ),
			'html_body'      => $body,
			'from_email'     => $from_email,
			'from_name'      => $from_name,
			'reply_to_email' => $from_email,
			'reply_to_name'  => $from_name,
		) );

		if ( ! $sent ) {
			// Fallback wp_mail() (thường fail trên shared hosting — chỉ để giữ đường lui).
			$sent = wp_mail(
				$payload['email'],
				sprintf( '[Dragon Glow] Interview invitation — %s', $payload['role'] ),
				$body,
				array(
					'Content-Type: text/html; charset=UTF-8',
					sprintf( 'From: %s <%s>', $from_name, $from_email ),
				)
			);
		}

		if ( $sent ) {
			dg_approval_finalise_token( $payload['_token'] );
			do_action( 'dg_careers_decision_approved', $payload, $date, $time, $location, $duration, $message );
			return array(
				'ok'      => true,
				'message' => __( 'Interview invitation sent to the candidate.', 'dragon-glow' ),
			);
		}
		return array(
			'ok'      => false,
			'message' => __( 'We could not send the email automatically. Please reply directly to the candidate.', 'dragon-glow' ),
		);
	}

	// Reject path.
	$reason = sanitize_textarea_field( $post['dg_reject_reason'] ?? '' );
	$body   = dg_careers_email_for_candidate_reject( $payload, $reason );

	$sent = dg_send_brevo_email( array(
		'to_email'       => $payload['email'],
		'to_name'        => $payload['name'] ?? '',
		'subject'        => sprintf( '[Dragon Glow] Application update — %s', $payload['role'] ),
		'html_body'      => $body,
		'from_email'     => $from_email,
		'from_name'      => $from_name,
		'reply_to_email' => $from_email,
		'reply_to_name'  => $from_name,
	) );

	if ( ! $sent ) {
		$sent = wp_mail(
			$payload['email'],
			sprintf( '[Dragon Glow] Application update — %s', $payload['role'] ),
			$body,
			array(
				'Content-Type: text/html; charset=UTF-8',
				sprintf( 'From: %s <%s>', $from_name, $from_email ),
			)
		);
	}

	if ( $sent ) {
		dg_approval_finalise_token( $payload['_token'] );
		do_action( 'dg_careers_decision_rejected', $payload, $reason );
		return array(
			'ok'      => true,
			'message' => __( 'Rejection email sent to the candidate.', 'dragon-glow' ),
		);
	}
	return array(
		'ok'      => false,
		'message' => __( 'We could not send the email automatically. Please reply directly to the candidate.', 'dragon-glow' ),
	);
}
