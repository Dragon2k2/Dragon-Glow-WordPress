<?php
/**
 * Template part — Careers / Section 4: Life here ("How we work.")
 *
 * 12-col: text 6/12 (mobile order-2) + ảnh 6/12 (mobile order-1, mobile-top).
 * Bg secondary-container (blush).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$image = dg_careers_life_image_data();
$bullets = dg_careers_how_we_work_data();
?>
<section class="dg-careers-life" data-sr>
	<div class="dg-careers-life-text">
		<h2 class="dg-careers-section-title"><?php esc_html_e( 'How We Work', 'dragon-glow' ); ?></h2>
		<?php if ( ! empty( $bullets ) ) : ?>
			<ul class="dg-careers-life-list">
				<?php foreach ( $bullets as $bullet ) : ?>
					<li class="dg-careers-life-bullet">
						<span class="dg-careers-life-rule" aria-hidden="true"></span>
						<span class="dg-careers-life-bullet-text"><?php echo esc_html( $bullet ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<div class="dg-careers-life-media">
		<img
			class="dg-careers-life-img"
			src="<?php echo esc_url( $image['image_url'] ); ?>"
			alt="<?php echo esc_attr( $image['image_alt'] ); ?>"
			loading="lazy"
		/>
	</div>
</section>