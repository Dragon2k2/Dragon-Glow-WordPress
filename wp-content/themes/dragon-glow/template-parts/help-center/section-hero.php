<?php
/**
 * Dragon Glow — Help Center: Hero
 * Editorial header — Geist, centered, restrained.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_hc_data()['hero'];
?>
<section class="dg-hc-hero" aria-label="<?php echo esc_attr( $hero['title'] ); ?>" data-sr-group>
	<h1 class="dg-hc-hero-title" data-sr><?php echo esc_html( $hero['title'] ); ?></h1>
	<p class="dg-hc-hero-sub" data-sr><?php echo esc_html( $hero['subtitle'] ); ?></p>
</section>