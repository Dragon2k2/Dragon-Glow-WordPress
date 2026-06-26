<?php
/**
 * Dragon Glow — Shipping & Returns: CTA Tile
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="dg-tile dg-tile--cta" data-sr data-magnetic>
	<div class="dg-cta-content">
		<span class="material-symbols-outlined dg-cta-icon" aria-hidden="true">
			support_agent
		</span>
		<h3 class="dg-cta-title font-display">
			<?php esc_html_e( 'Still have a question?', 'dragon-glow' ); ?>
		</h3>
		<p class="dg-cta-sub">
			<?php esc_html_e( 'Our concierge is here to help with anything about your order.', 'dragon-glow' ); ?>
		</p>
		<a class="dg-cta-btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
			<span><?php esc_html_e( 'Contact concierge', 'dragon-glow' ); ?></span>
			<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
		</a>
	</div>
	<span class="dg-cta-glow" aria-hidden="true"></span>
</article>
