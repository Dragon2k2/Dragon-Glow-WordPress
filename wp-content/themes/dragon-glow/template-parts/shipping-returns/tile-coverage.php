<?php
/**
 * Dragon Glow — Shipping & Returns: Coverage Tile
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$coverage = dg_shipping_returns_data()['coverage'];
?>
<article class="dg-tile dg-tile--coverage" data-sr id="coverage" aria-labelledby="dg-coverage-h">
	<span class="dg-tile-eyebrow"><?php esc_html_e( '03 — Coverage', 'dragon-glow' ); ?></span>
	<h3 id="dg-coverage-h" class="dg-tile-title font-display">
		<?php esc_html_e( 'Where we ship', 'dragon-glow' ); ?>
	</h3>

	<div class="dg-coverage-grid">
		<?php foreach ( $coverage as $idx => $c ) : ?>
		<div class="dg-coverage-item" data-sr>
			<div class="dg-coverage-icon-row">
				<span class="material-symbols-outlined dg-coverage-icon" aria-hidden="true">
					<?php echo esc_html( $c['icon'] ); ?>
				</span>
				<span class="dg-coverage-rule" aria-hidden="true"></span>
			</div>
			<h4 class="dg-coverage-item-title"><?php echo esc_html( $c['title'] ); ?></h4>
			<p class="dg-coverage-item-body"><?php echo esc_html( $c['body'] ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
</article>
