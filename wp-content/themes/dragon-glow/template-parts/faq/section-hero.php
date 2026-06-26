<?php
/**
 * Dragon Glow — FAQ: Hero
 * Editorial header riêng cho FAQ (Geist) — không dùng global page-hero
 * để toàn quyền kiểm soát typography của hướng "Numbered Editorial".
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$intro = dg_faq_data()['intro'];
?>
<header class="dg-faq-hero" data-sr-group>
	<p class="dg-faq-eyebrow" data-sr><?php echo esc_html( $intro['eyebrow'] ); ?></p>
	<h1 class="dg-faq-title" data-sr><?php echo esc_html( $intro['title'] ); ?></h1>
	<p class="dg-faq-lede" data-sr><?php echo esc_html( $intro['subtitle'] ); ?></p>
</header>
