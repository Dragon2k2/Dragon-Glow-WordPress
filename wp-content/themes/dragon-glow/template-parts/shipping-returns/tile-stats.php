<?php
/**
 * Dragon Glow — Shipping & Returns: Stats Tiles
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$stats = dg_shipping_returns_data()['stats'];
?>
<?php foreach ( $stats as $stat ) : ?>
<article class="dg-tile dg-tile--stat dg-accent-<?php echo esc_attr( $stat['accent'] ); ?>" data-sr data-magnetic>
	<span class="dg-stat-icon material-symbols-outlined" aria-hidden="true">
		<?php echo esc_html( $stat['icon'] ); ?>
	</span>
	<p class="dg-stat-num font-display">
		<span class="dg-count" data-count-to="<?php echo esc_attr( $stat['to'] ); ?>">0</span><span class="dg-stat-suffix"><?php echo esc_html( $stat['suffix'] ); ?></span>
	</p>
	<p class="dg-stat-label"><?php echo esc_html( $stat['label'] ); ?></p>
</article>
<?php endforeach; ?>
