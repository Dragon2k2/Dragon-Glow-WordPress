<?php
/**
 * Dragon Glow — Our Ingredients: Traceable
 * Full-bleed image with overlay, centered text + country labels.
 * Mirrors stitch: "Traceable. Always."
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data       = dg_our_ingredients_data();
$traceable  = $data['traceable'];
$countries  = $traceable['countries'];
?>
<section id="traceable" class="dg-oi-traceable" aria-label="Traceability">
	<div class="dg-oi-traceable-bg">
		<img
			src="<?php echo esc_url( $traceable['image_url'] ); ?>"
			alt="<?php echo esc_attr( $traceable['image_alt'] ); ?>"
			loading="lazy"
			decoding="async"
		>
		<div class="dg-oi-traceable-overlay" aria-hidden="true"></div>
	</div>

	<div class="dg-oi-traceable-content" data-sr>
		<h2 class="dg-oi-traceable-headline"><?php echo esc_html( $traceable['headline'] ); ?></h2>

		<p class="dg-oi-traceable-body">
			<?php
			$body_lines = explode( "\n", $traceable['body'] );
			foreach ( $body_lines as $line ) :
				echo esc_html( $line ) . '<br>';
			endforeach;
			?>
		</p>

		<div class="dg-oi-traceable-countries">
			<?php foreach ( $countries as $country ) : ?>
			<span class="dg-oi-traceable-country"><?php echo esc_html( $country ); ?></span>
			<?php endforeach; ?>
		</div>

		<p class="dg-oi-traceable-footnote"><?php echo esc_html( $traceable['footnote'] ); ?></p>
	</div>
</section>
