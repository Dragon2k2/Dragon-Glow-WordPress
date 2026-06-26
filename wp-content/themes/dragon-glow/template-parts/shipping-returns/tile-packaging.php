<?php
/**
 * Dragon Glow — Shipping & Returns: Packaging Pillars Tile
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$packaging = dg_shipping_returns_data()['packaging'];
?>
<div class="dg-tile dg-tile--packaging" data-sr id="packaging" aria-label="<?php esc_attr_e( 'Packaging commitment', 'dragon-glow' ); ?>">
	<span class="dg-tile-eyebrow"><?php esc_html_e( '04 — Packaging', 'dragon-glow' ); ?></span>
	<h3 class="dg-tile-title font-display">
		<?php esc_html_e( 'Packaged with purpose', 'dragon-glow' ); ?>
	</h3>

	<div class="dg-packaging-grid">
		<?php foreach ( $packaging as $idx => $p ) : ?>
		<div class="dg-pillar-item dg-pillar-item--<?php echo 0 === $idx ? 'light' : 'dark'; ?>" data-sr>
			<span class="material-symbols-outlined dg-pillar-icon" aria-hidden="true">
				<?php echo esc_html( $p['icon'] ); ?>
			</span>
			<div class="dg-pillar-content">
				<h4 class="dg-pillar-title"><?php echo esc_html( $p['title'] ); ?></h4>
				<p class="dg-pillar-body"><?php echo esc_html( $p['body'] ); ?></p>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
