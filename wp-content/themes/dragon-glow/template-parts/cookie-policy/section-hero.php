<?php
/**
 * Template part — Cookie Policy / Hero
 *
 * Header block: title, last updated + effective date, intro, manage cookies
 * placeholder button. Data lấy từ dg_cookie_policy_hero_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_cookie_policy_hero_data();
?>
<header class="dg-cookie-header" data-sr>
	<h1 class="dg-cookie-title"><?php echo esc_html( $hero['title'] ); ?></h1>
	<div class="dg-cookie-meta">
		<span><?php
			printf(
				/* translators: %s: last updated date */
				esc_html__( 'Last updated: %s', 'dragon-glow' ),
				esc_html( $hero['last_updated'] )
			);
		?></span>
		<span class="dg-cookie-meta-sep">&bull;</span>
		<span><?php
			printf(
				/* translators: %s: effective date */
				esc_html__( 'Effective date: %s', 'dragon-glow' ),
				esc_html( $hero['effective_date'] )
			);
		?></span>
	</div>
	<p class="dg-cookie-intro"><?php echo esc_html( $hero['intro'] ); ?></p>
	<!--
		WIP: Manage Cookies button is intentionally a no-op placeholder for now.
		The consent banner / modal / persistence flow will be implemented in a
		separate change. See .dg-cookie-management section below for the in-page
		Manage Cookies trigger that will reuse the same component.
	-->
	<button class="dg-cookie-manage-btn" type="button" disabled aria-disabled="true">
		<?php esc_html_e( 'Manage Cookies', 'dragon-glow' ); ?>
	</button>
</header>
