<?php
/**
 * Template part — Accessibility Statement / Hero
 *
 * Hero ngắn: eyebrow + H1 + intro (không kèm "Last updated").
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_accessibility_hero_data();
?>
<header class="dg-accessibility-header" data-sr>
	<p class="dg-accessibility-eyebrow"><?php echo esc_html( $hero['eyebrow'] ); ?></p>
	<h1 class="dg-accessibility-title"><?php echo esc_html( $hero['title'] ); ?></h1>
	<p class="dg-accessibility-intro"><?php echo esc_html( $hero['intro'] ); ?></p>
</header>