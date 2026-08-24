<?php
/**
 * Dragon Glow — Sustainability: Accountability
 * Image left (6/12) + text right (5/12, start-8).
 * Mirrors stitch: "Audited. Every year."
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data           = dg_sustainability_data();
$accountability = $data['accountability'];
?>
<section class="dg-sus-accountability" aria-label="<?php echo esc_attr( $accountability['headline'] ); ?>">
	<div class="dg-sus-accountability-inner">

		<!-- Image: left -->
		<div class="dg-sus-accountability-image" data-sr>
			<img
				src="<?php echo esc_url( $accountability['image_url'] ); ?>"
				alt="<?php echo esc_attr( $accountability['image_alt'] ); ?>"
				loading="lazy"
				decoding="async"
			>
		</div>

		<!-- Text: right -->
		<div class="dg-sus-accountability-content" data-sr>
			<h2 class="dg-sus-accountability-headline"><?php echo esc_html( $accountability['headline'] ); ?></h2>
			<p class="dg-sus-accountability-body">
				<?php
				$body_lines = explode( "\n", $accountability['body'] );
				foreach ( $body_lines as $line ) :
					echo esc_html( $line ) . '<br>';
				endforeach;
				?>
			</p>
		</div>
	</div>
</section>