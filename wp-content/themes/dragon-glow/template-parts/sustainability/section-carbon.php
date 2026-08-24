<?php
/**
 * Dragon Glow — Sustainability: Carbon
 * Centered text on tertiary-fixed tint background, with `air` icon.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data   = dg_sustainability_data();
$carbon = $data['carbon'];
?>
<section class="dg-sus-carbon" aria-label="<?php echo esc_attr( $carbon['headline'] ); ?>">
	<div class="dg-sus-carbon-overlay" aria-hidden="true"></div>
	<div class="dg-sus-carbon-inner" data-sr>
		<span class="material-symbols-outlined dg-sus-carbon-icon" aria-hidden="true"><?php echo esc_html( $carbon['icon'] ); ?></span>
		<h2 class="dg-sus-carbon-headline"><?php echo esc_html( $carbon['headline'] ); ?></h2>
		<p class="dg-sus-carbon-body">
			<?php
			$body_lines = explode( "\n", $carbon['body'] );
			foreach ( $body_lines as $line ) :
				echo esc_html( $line ) . '<br>';
			endforeach;
			?>
		</p>
	</div>
</section>