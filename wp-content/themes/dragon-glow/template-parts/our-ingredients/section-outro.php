<?php
/**
 * Dragon Glow — Our Ingredients: Outro
 * Vertical hairline + headline + CTA button.
 * Mirrors stitch closing section.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data   = dg_our_ingredients_data();
$outro  = $data['outro'];
?>
<section class="dg-oi-outro" aria-label="Outro">
	<div class="dg-oi-outro-inner">

		<div class="dg-oi-outro-hairline" aria-hidden="true" data-sr></div>

		<h2 class="dg-oi-outro-headline" data-sr>
			<?php echo esc_html( $outro['headline'] ); ?>
		</h2>

		<a
			href="<?php echo esc_url( $outro['cta_url'] ); ?>"
			class="dg-oi-btn"
			data-sr
		>
			<?php echo esc_html( $outro['cta_text'] ); ?>
		</a>
	</div>
</section>
