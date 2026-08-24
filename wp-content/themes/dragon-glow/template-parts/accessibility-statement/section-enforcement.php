<?php
/**
 * Template part — Accessibility Statement / Section 9: Feedback and contact
 *
 * Render box thông tin liên hệ lấy từ dg_accessibility_contact_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$contact = dg_accessibility_contact_data();
?>
<section class="dg-accessibility-section" id="section-9">
	<h2 class="dg-accessibility-section-title"><span class="dg-accessibility-section-num">09</span> Feedback and contact</h2>
	<p>If you meet a barrier, or need information in a different format, tell us. We treat accessibility reports as a priority.</p>
	<?php if ( ! empty( $contact ) ) : ?>
		<div class="dg-accessibility-contact-box">
			<ul>
				<?php foreach ( $contact as $row ) : ?>
					<li>
						<strong><?php echo esc_html( $row['label'] ); ?></strong>
						<?php if ( ! empty( $row['href'] ) ) : ?>
							<a href="<?php echo esc_url( $row['href'] ); ?>"><?php echo esc_html( $row['value'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $row['value'] ); ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="dg-accessibility-contact-note"><?php esc_html_e( 'We aim to respond within five business days.', 'dragon-glow' ); ?></p>
		</div>
	<?php endif; ?>
</section>