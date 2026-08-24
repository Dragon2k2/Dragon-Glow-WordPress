<?php
/**
 * Dragon Glow — Shipping & Returns: Hero
 * Mirrors the stitch prototype: full-width macro-texture background,
 * cream overlay, centered title + subtitle.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_shipping_returns_data()['hero'];
?>
<section class="dg-sr-hero" aria-label="<?php echo esc_attr( $hero['title'] ); ?>">
	<img
		class="dg-sr-hero-bg"
		src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/shipping-returns/shipping-returns.webp' ); ?>"
		alt=""
		aria-hidden="true"
		loading="eager"
		fetchpriority="high"
		decoding="async"
		width="1024"
		height="680"
	>
	<div class="dg-sr-hero-overlay" aria-hidden="true"></div>
	<div class="dg-sr-hero-content">
		<h1 class="dg-sr-hero-title" data-sr><?php echo esc_html( $hero['title'] ); ?></h1>
		<p class="dg-sr-hero-sub" data-sr><?php echo esc_html( $hero['subtitle'] ); ?></p>
	</div>
</section>