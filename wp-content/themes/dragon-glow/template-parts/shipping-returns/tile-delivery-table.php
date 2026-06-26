<?php
/**
 * Dragon Glow — Shipping & Returns: Delivery Options Tile
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$delivery    = dg_shipping_returns_data()['delivery'];
$delivery_note = dg_shipping_returns_data()['delivery_note'];
?>
<article class="dg-tile dg-tile--table" data-sr aria-labelledby="dg-delivery-h">
	<h3 id="dg-delivery-h" class="dg-tile-title font-display">
		<?php esc_html_e( 'Choose your pace', 'dragon-glow' ); ?>
	</h3>

	<ul class="dg-delivery-list" aria-label="<?php esc_attr_e( 'Delivery methods', 'dragon-glow' ); ?>">
		<?php foreach ( $delivery as $row ) : ?>
		<li class="dg-delivery-row<?php echo $row['highlight'] ? ' is-highlight' : ''; ?>">
			<span class="dg-d-method"><?php echo esc_html( $row['method'] ); ?></span>
			<span class="dg-d-time"><?php echo esc_html( $row['time'] ); ?></span>
			<span class="dg-d-cost<?php echo $row['highlight'] ? ' is-free' : ''; ?>">
				<?php echo esc_html( $row['cost'] ); ?>
			</span>
		</li>
		<?php endforeach; ?>
	</ul>

	<p class="dg-delivery-note">
		<span class="material-symbols-outlined" aria-hidden="true">schedule</span>
		<?php echo esc_html( $delivery_note ); ?>
	</p>
</article>
