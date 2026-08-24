<?php
/**
 * Template part — Terms of Service / Hero
 *
 * Header block: title, last updated + effective date, intro. Data lấy từ
 * dg_terms_of_service_hero_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_terms_of_service_hero_data();
?>
<header class="dg-terms-header" data-sr>
	<h1 class="dg-terms-title"><?php echo esc_html( $hero['title'] ); ?></h1>
	<div class="dg-terms-meta">
		<span><?php
			printf(
				/* translators: %s: last updated date */
				esc_html__( 'Last updated: %s', 'dragon-glow' ),
				esc_html( $hero['last_updated'] )
			);
		?></span>
		<span class="dg-terms-meta-sep">•</span>
		<span><?php
			printf(
				/* translators: %s: effective date */
				esc_html__( 'Effective date: %s', 'dragon-glow' ),
				esc_html( $hero['effective_date'] )
			);
		?></span>
	</div>
	<p class="dg-terms-intro"><?php echo esc_html( $hero['intro'] ); ?></p>
</header>
