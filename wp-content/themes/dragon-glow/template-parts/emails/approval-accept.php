<?php
/**
 * Email template — Careers Approval (Accept / Interview Invitation)
 *
 * Rendered via dg_render_email_template(). Context variables exposed via
 * the `$email` array:
 *
 *   - name        (string) Candidate name.
 *   - role        (string) Role applied for.
 *   - role_intro  (string) Role-specific intro paragraph.
 *   - date_human  (string) Human-readable interview date (e.g. "Monday, July 14, 2026").
 *   - time_human  (string) Human-readable interview time (e.g. "2:30 PM UTC").
 *   - duration    (string) Duration in minutes (e.g. "45").
 *   - location    (string) Address or video link.
 *   - message     (string) Optional HR note.
 *   - hr_name     (string) Brand name to sign off with.
 *
 * The output of this template becomes the email HTML body. Use inline styles
 * (no external CSS) so the email renders consistently across clients.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<p style="font-family:inherit;"><?php
	printf(
		/* translators: %s: candidate name */
		esc_html__( 'Hi %s,', 'dragon-glow' ),
		esc_html( $email['name'] ?? '' )
	);
?></p>
<p style="font-family:inherit;"><?php
	printf(
		/* translators: %s: role name wrapped in <strong> */
		esc_html__( 'We would love to invite you to interview for the %s role at Dragon Glow.', 'dragon-glow' ),
		'<strong>' . esc_html( $email['role'] ?? '' ) . '</strong>'
	);
?></p>
<p style="font-family:inherit;"><?php echo esc_html( $email['role_intro'] ?? '' ); ?></p>

<table cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-family:inherit;margin:18px 0;">
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'Date', 'dragon-glow' ); ?></td>
		<td><strong><?php echo esc_html( $email['date_human'] ?? '' ); ?></strong></td>
	</tr>
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'Time', 'dragon-glow' ); ?></td>
		<td><strong><?php echo esc_html( $email['time_human'] ?? '' ); ?></strong></td>
	</tr>
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'Duration', 'dragon-glow' ); ?></td>
		<td><?php echo esc_html( $email['duration'] ?? '' ); ?> <?php esc_html_e( 'minutes', 'dragon-glow' ); ?></td>
	</tr>
	<tr>
		<td style="color:#888;vertical-align:top;"><?php esc_html_e( 'Location', 'dragon-glow' ); ?></td>
		<td><?php echo esc_html( $email['location'] ?? '' ); ?></td>
	</tr>
</table>

<?php if ( ! empty( $email['message'] ) ) : ?>
	<p style="font-family:inherit;">
		<strong><?php esc_html_e( 'A note from us', 'dragon-glow' ); ?></strong><br>
		<?php echo nl2br( esc_html( $email['message'] ) ); ?>
	</p>
<?php endif; ?>

<p style="font-family:inherit;">
	<?php esc_html_e( 'A calendar invite is attached. If you need to reschedule, simply reply to this email.', 'dragon-glow' ); ?>
</p>

<p style="font-family:inherit;"><?php
	printf(
		/* translators: %s: brand name */
		esc_html__( 'Warmly, %s', 'dragon-glow' ),
		esc_html( $email['hr_name'] ?? '' )
	);
?></p>
