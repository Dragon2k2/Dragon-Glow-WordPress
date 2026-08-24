<?php
/**
 * Dragon Glow — Careers: Approval ICS calendar builder
 *
 * Builds a minimal VCALENDAR string for the interview event so the email
 * client can add it to the candidate's calendar.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build minimal VCALENDAR string for ICS calendar invite.
 *
 * Caller is expected to attach this when sending the email.
 *
 * @param array  $payload  Submission payload.
 * @param string $date     YYYY-MM-DD.
 * @param string $time     HH:MM.
 * @param string $location Location or video link.
 * @param string $duration Duration in minutes.
 * @return string ICS file content.
 */
function dg_careers_build_ics( array $payload, string $date, string $time, string $location, string $duration ): string {
	$dt          = new DateTimeImmutable( $date . 'T' . $time . ':00', new DateTimeZone( 'UTC' ) );
	$end         = $dt->modify( '+' . max( 15, (int) $duration ) . ' minutes' );
	$uid         = wp_generate_password( 16, false ) . '@' . wp_parse_url( home_url(), PHP_URL_HOST );
	$stamp       = gmdate( 'Ymd\THis\Z' );
	$dtstart     = $dt->format( 'Ymd\THis\Z' );
	$dtend       = $end->format( 'Ymd\THis\Z' );
	$summary     = sprintf( 'Dragon Glow interview — %s', $payload['role'] ?? '' );
	$description = 'Interview with Dragon Glow for the ' . ( $payload['role'] ?? '' ) . ' role.';
	$location_esc = str_replace( array( "\r", "\n", ',', ';' ), array( ' ', ' ', '\,', '\;' ), $location );

	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//Dragon Glow//Careers//EN',
		'CALSCALE:GREGORIAN',
		'METHOD:REQUEST',
		'BEGIN:VEVENT',
		'UID:' . $uid,
		'DTSTAMP:' . $stamp,
		'DTSTART:' . $dtstart,
		'DTEND:' . $dtend,
		'SUMMARY:' . $summary,
		'DESCRIPTION:' . $description,
		'LOCATION:' . $location_esc,
		'END:VEVENT',
		'END:VCALENDAR',
	);
	return implode( "\r\n", $lines ) . "\r\n";
}
