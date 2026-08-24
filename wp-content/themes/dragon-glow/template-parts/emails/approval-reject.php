<?php
/**
 * Email template — Careers Approval (Reject / Application Update)
 *
 * Rendered via dg_render_email_template(). Context variables exposed via
 * the `$email` array:
 *
 *   - name       (string) Candidate name.
 *   - role       (string) Role applied for.
 *   - role_intro (string) Role-specific intro paragraph.
 *   - reason     (string) Optional rejection reason.
 *   - hr_name    (string) Brand name to sign off with.
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
		esc_html__( 'Thank you again for applying for the %s role.', 'dragon-glow' ),
		'<strong>' . esc_html( $email['role'] ?? '' ) . '</strong>'
	);
?>
<?php echo esc_html( $email['role_intro'] ?? '' ); ?></p>

<?php if ( ! empty( $email['reason'] ) ) : ?>
	<p style="font-family:inherit;"><?php echo nl2br( esc_html( $email['reason'] ) ); ?></p>
<?php endif; ?>

<p style="font-family:inherit;">
	<?php esc_html_e( 'We deeply appreciate the time you put into your application, and we will keep your profile on file should a more suitable role open in the future.', 'dragon-glow' ); ?>
</p>

<p style="font-family:inherit;"><?php
	printf(
		/* translators: %s: brand name */
		esc_html__( 'Wishing you the best, %s', 'dragon-glow' ),
		esc_html( $email['hr_name'] ?? '' )
	);
?></p>
