<?php
/**
 * Dragon Glow — Sustainability: Sourcing (Traceable. Always.)
 * Image left (6/12) + text right (5/12, start-8). Region chips below body.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data     = dg_sustainability_data();
$sourcing = $data['sourcing'];
?>
<section class="dg-sus-sourcing" aria-label="<?php echo esc_attr( $sourcing['headline'] ); ?>">
	<div class="dg-sus-sourcing-inner">

		<!-- Image: left -->
		<div class="dg-sus-sourcing-image" data-sr>
			<img
				src="<?php echo esc_url( $sourcing['image_url'] ); ?>"
				alt="<?php echo esc_attr( $sourcing['image_alt'] ); ?>"
				loading="lazy"
				decoding="async"
			>
		</div>

		<!-- Text: right -->
		<div class="dg-sus-sourcing-content" data-sr>
			<h2 class="dg-sus-sourcing-headline"><?php echo esc_html( $sourcing['headline'] ); ?></h2>
			<p class="dg-sus-sourcing-body">
				<?php
				$body_lines = explode( "\n", $sourcing['body'] );
				foreach ( $body_lines as $line ) :
					echo esc_html( $line ) . '<br>';
				endforeach;
				?>
			</p>

			<div class="dg-sus-sourcing-regions">
				<?php foreach ( $sourcing['regions'] as $region ) : ?>
				<span class="dg-sus-sourcing-region"><?php echo esc_html( $region ); ?></span>
				<?php endforeach; ?>
			</div>

			<p class="dg-sus-sourcing-footnote"><?php echo esc_html( $sourcing['footnote'] ); ?></p>
		</div>
	</div>
</section>