<?php
/**
 * Template part — Privacy Policy / Hero
 *
 * Header block: title, last updated + effective date, intro. Data lấy từ
 * dg_privacy_policy_hero_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_privacy_policy_hero_data();
?>
<header class="dg-privacy-header" data-sr>
	<h1 class="dg-privacy-title"><?php echo esc_html( $hero['title'] ); ?></h1>
	<div class="dg-privacy-meta">
		<span><?php
			printf(
				/* translators: %s: last updated date */
				esc_html__( 'Last updated: %s', 'dragon-glow' ),
				esc_html( $hero['last_updated'] )
			);
		?></span>
		<span class="dg-privacy-meta-sep">•</span>
		<span><?php
			printf(
				/* translators: %s: effective date */
				esc_html__( 'Effective date: %s', 'dragon-glow' ),
				esc_html( $hero['effective_date'] )
			);
		?></span>
	</div>
	<p class="dg-privacy-intro"><?php echo esc_html( $hero['intro'] ); ?></p>
</header>
