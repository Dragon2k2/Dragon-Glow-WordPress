<?php
/**
 * Dragon Glow — Our Ingredients: Ingredients Grid
 * 5 ingredient tiles in editorial asymmetric grid (3 cols with offsets).
 * Mirrors stitch: "What we work with."
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data        = dg_our_ingredients_data();
$ingredients = $data['ingredients'];
?>
<section class="dg-oi-ingredients" aria-label="Ingredients">
	<div class="dg-oi-ingredients-header" data-sr>
		<h2 class="dg-oi-ingredients-heading">What we work with</h2>
	</div>

	<div class="dg-oi-ingredients-grid">
		<?php foreach ( $ingredients as $index => $item ) : ?>
		<article
			class="dg-oi-tile <?php echo esc_attr( $item['offset_class'] ); ?>"
			data-sr
			data-sr-delay="<?php echo (int) ( $index * 80 ); ?>"
		>
			<div class="dg-oi-tile-image">
				<img
					src="<?php echo esc_url( $item['image_url'] ); ?>"
					alt="<?php echo esc_attr( $item['image_alt'] ); ?>"
					loading="lazy"
					decoding="async"
				>
			</div>
			<h3 class="dg-oi-tile-name"><?php echo esc_html( $item['name'] ); ?></h3>
			<div class="dg-oi-tile-source">
				<p class="dg-oi-tile-source-text"><?php echo esc_html( $item['source'] ); ?></p>
			</div>
			<p class="dg-oi-tile-desc"><?php echo esc_html( $item['description'] ); ?></p>
		</article>
		<?php endforeach; ?>
	</div>
</section>
