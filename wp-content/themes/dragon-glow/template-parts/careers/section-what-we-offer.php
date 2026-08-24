<?php
/**
 * Template part — Careers / Section 5: What we offer
 *
 * Grid 1col mobile / 2col sm / 3col md, mỗi ô có border outline-variant
 * hover đổi sang border-tertiary (gold).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$benefits = dg_careers_benefits_data();
?>
<section class="dg-careers-offer" data-sr>
	<h2 class="dg-careers-section-title dg-careers-section-title--center dg-careers-section-title--emphasis"><?php esc_html_e( 'What we offer', 'dragon-glow' ); ?></h2>
	<?php if ( ! empty( $benefits ) ) : ?>
		<div class="dg-careers-offer-grid">
			<?php foreach ( $benefits as $benefit ) : ?>
				<div class="dg-careers-offer-tile">
					<p class="dg-careers-offer-text"><?php echo esc_html( $benefit ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>