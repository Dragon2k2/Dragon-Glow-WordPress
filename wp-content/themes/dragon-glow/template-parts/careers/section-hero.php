<?php
/**
 * Template part — Careers / Hero
 *
 * Layout 12-col: ảnh 6/12 (mobile full height 50vh, desktop full height của section)
 * + text 5/12 bắt đầu từ col 8 (offset 3). Min-height 80vh. Reveal khi scroll.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_careers_hero_data();
?>
<section class="dg-careers-hero" data-sr>
	<div class="dg-careers-hero-media">
		<img
			class="dg-careers-hero-img"
			src="<?php echo esc_url( $hero['image_url'] ); ?>"
			alt="<?php echo esc_attr( $hero['image_alt'] ); ?>"
			loading="eager"
		/>
	</div>
	<div class="dg-careers-hero-text">
		<p class="dg-careers-hero-eyebrow"><?php echo esc_html( $hero['eyebrow'] ); ?></p>
		<h1 class="dg-careers-hero-title dg-careers-hero-title--emphasis"><?php echo esc_html( $hero['title'] ); ?></h1>
		<p class="dg-careers-hero-intro"><?php echo esc_html( $hero['intro'] ); ?></p>
		<a class="dg-careers-btn dg-careers-btn--secondary" href="#open-roles"><?php echo esc_html( $hero['cta_label'] ); ?></a>
	</div>
</section>