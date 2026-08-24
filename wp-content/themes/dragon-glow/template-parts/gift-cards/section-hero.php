<?php
/**
 * Dragon Glow — Gift Cards: Hero
 * Editorial header — Geist, centered, restrained (theo reference).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_gift_cards_data()['hero'];
?>
<header class="dg-gift-hero" data-sr-group>
	<span class="dg-gift-hero-eyebrow" data-sr><?php echo esc_html( $hero['eyebrow'] ); ?></span>
	<h1 class="dg-gift-hero-title" data-sr><?php echo esc_html( $hero['title'] ); ?></h1>
	<p class="dg-gift-hero-lede" data-sr><?php echo esc_html( $hero['subtitle'] ); ?></p>
</header>
