<?php
/**
 * Dragon Glow — Our Ingredients: Signature
 * Dragon Fruit Enzyme — text left, image right. Mobile: image first.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data      = dg_our_ingredients_data();
$signature = $data['signature'];
?>
<section class="dg-oi-signature" aria-label="<?php echo esc_attr( $signature['title'] ); ?>">
	<div class="dg-oi-signature-inner">

		<!-- Text: left -->
		<div class="dg-oi-signature-text" data-sr>
			<div class="dg-oi-signature-header">
				<p class="dg-oi-signature-badge"><?php echo esc_html( $signature['badge'] ); ?></p>
				<h3 class="dg-oi-signature-title"><?php echo esc_html( $signature['title'] ); ?></h3>
				<p class="dg-oi-signature-subtitle"><?php echo esc_html( $signature['subtitle'] ); ?></p>
			</div>
			<p class="dg-oi-signature-body">
				<?php
				$body_lines = explode( "\n", $signature['body'] );
				foreach ( $body_lines as $line ) :
					echo esc_html( $line ) . '<br>';
				endforeach;
				?>
			</p>
		</div>

		<!-- Image: right -->
		<div class="dg-oi-signature-image" data-sr>
			<img
				src="<?php echo esc_url( $signature['image_url'] ); ?>"
				alt="<?php echo esc_attr( $signature['image_alt'] ); ?>"
				loading="lazy"
				decoding="async"
			>
		</div>
	</div>
</section>
