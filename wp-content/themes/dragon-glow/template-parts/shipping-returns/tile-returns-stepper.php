<?php
/**
 * Dragon Glow — Shipping & Returns: Returns Stepper Tile
 * Interactive 4-step timeline với vertical connector line.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$steps    = dg_shipping_returns_data()['returns_steps'];
$exceptions = dg_shipping_returns_data()['returns_exceptions'];
?>
<article class="dg-tile dg-tile--returns" data-sr id="returns" aria-labelledby="dg-returns-h">
	<span class="dg-tile-eyebrow"><?php esc_html_e( '02 — Returns', 'dragon-glow' ); ?></span>
	<h3 id="dg-returns-h" class="dg-tile-title font-display">
		<?php esc_html_e( 'Returns made effortless', 'dragon-glow' ); ?>
	</h3>

	<?php /* ── 4-step vertical timeline ─────────────────────────────── */ ?>
	<div class="dg-timeline" role="list" aria-label="<?php esc_attr_e( 'Return process steps', 'dragon-glow' ); ?>">
		<?php foreach ( $steps as $i => $step ) : ?>
		<div class="dg-timeline-item" role="listitem">
			<div class="dg-timeline-marker" aria-hidden="true">
				<span class="dg-timeline-number"><?php echo esc_html( $step['number'] ); ?></span>
				<?php if ( $i < count( $steps ) - 1 ) : ?>
				<span class="dg-timeline-connector" aria-hidden="true"></span>
				<?php endif; ?>
			</div>
			<div class="dg-timeline-content">
				<div class="dg-timeline-icon-row">
					<span class="material-symbols-outlined dg-timeline-icon" aria-hidden="true">
						<?php echo esc_html( $step['icon'] ); ?>
					</span>
				</div>
				<h4 class="dg-timeline-step-title"><?php echo esc_html( $step['title'] ); ?></h4>
				<p class="dg-timeline-step-body"><?php echo esc_html( $step['body'] ); ?></p>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<div class="dg-returns-exceptions">
		<span class="material-symbols-outlined dg-exc-icon" aria-hidden="true">info_outline</span>
		<div>
			<span class="dg-exc-label"><?php esc_html_e( 'Exceptions:', 'dragon-glow' ); ?></span>
			<span class="dg-exc-text"><?php echo esc_html( $exceptions ); ?></span>
		</div>
	</div>
</article>
