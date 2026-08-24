<?php
/**
 * Dragon Glow — Sustainability: Refill
 * Text left (5/12) + image right (6/12, start-7).
 * Mirrors stitch: "Send it back. Begin again."
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data   = dg_sustainability_data();
$refill = $data['refill'];
?>
<section class="dg-sus-refill" aria-label="<?php echo esc_attr( $refill['headline'] ); ?>">
	<div class="dg-sus-refill-inner">

		<!-- Text: left -->
		<div class="dg-sus-refill-content" data-sr>
			<h2 class="dg-sus-refill-headline"><?php echo esc_html( $refill['headline'] ); ?></h2>
			<p class="dg-sus-refill-body">
				<?php
				$body_lines = explode( "\n", $refill['body'] );
				foreach ( $body_lines as $line ) :
					echo esc_html( $line ) . '<br>';
				endforeach;
				?>
			</p>
		</div>

		<!-- Image: right -->
		<div class="dg-sus-refill-image" data-sr>
			<img
				src="<?php echo esc_url( $refill['image_url'] ); ?>"
				alt="<?php echo esc_attr( $refill['image_alt'] ); ?>"
				loading="lazy"
				decoding="async"
			>
		</div>
	</div>
</section>