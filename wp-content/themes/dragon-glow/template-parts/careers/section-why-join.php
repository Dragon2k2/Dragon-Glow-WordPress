<?php
/**
 * Template part — Careers / Section 3: Why join us ("What we hold to.")
 *
 * Grid 3-col mobile=1. Icon (img) + title + body, mỗi tile.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$tiles = dg_careers_why_join_data();
?>
<section class="dg-careers-why" data-sr>
	<h2 class="dg-careers-section-title dg-careers-section-title--center dg-careers-section-title--emphasis"><?php esc_html_e( 'What We Hold To', 'dragon-glow' ); ?></h2>
	<?php if ( ! empty( $tiles ) ) : ?>
		<div class="dg-careers-why-grid">
			<?php foreach ( $tiles as $tile ) : ?>
				<div class="dg-careers-why-tile">
					<img
						class="dg-careers-why-icon"
						src="<?php echo esc_url( $tile['icon_url'] ); ?>"
						alt="<?php echo esc_attr( $tile['icon_alt'] ); ?>"
						loading="lazy"
					/>
					<h3 class="dg-careers-why-title"><?php echo esc_html( $tile['title'] ); ?></h3>
					<p class="dg-careers-why-body"><?php echo esc_html( $tile['body'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>