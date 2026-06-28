<?php
/**
 * Dragon Glow — FAQ: Hero
 * Editorial header — Geist, centered, restrained.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$intro = dg_faq_data()['intro'];
?>
<header class="dg-faq-hero" data-sr-group>
	<h1 class="dg-faq-title" data-sr><?php echo esc_html( $intro['title'] ); ?></h1>
	<p class="dg-faq-lede" data-sr><?php echo esc_html( $intro['subtitle'] ); ?></p>
</header>