<?php
/**
 * Dragon Glow — Sustainability: Hero
 * Mirrors stitch: image left (5/12) + text right (6/12, start-7), editorial split.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data = dg_sustainability_data();
$hero = $data['hero'];
?>
<section class="dg-sus-hero" aria-label="<?php echo esc_attr( $hero['badge'] ); ?>">
	<div class="dg-sus-hero-inner">

		<!-- Image: left col -->
		<div class="dg-sus-hero-image" data-sr>
			<img
				src="<?php echo esc_url( $hero['image_url'] ); ?>"
				alt="<?php echo esc_attr( $hero['image_alt'] ); ?>"
				loading="eager"
				fetchpriority="high"
				decoding="async"
			>
		</div>

		<!-- Text: right col -->
		<div class="dg-sus-hero-content" data-sr>
			<p class="dg-sus-hero-badge"><?php echo esc_html( $hero['badge'] ); ?></p>
			<h1 class="dg-sus-hero-headline"><?php echo esc_html( $hero['headline'] ); ?></h1>
			<p class="dg-sus-hero-body">
				<?php
				$body_lines = explode( "\n", $hero['body'] );
				foreach ( $body_lines as $line ) :
					echo esc_html( $line ) . '<br>';
				endforeach;
				?>
			</p>
			<a href="<?php echo esc_url( $hero['cta_url'] ); ?>" class="dg-sus-btn">
				<?php echo esc_html( $hero['cta_text'] ); ?>
			</a>
		</div>
	</div>
</section>