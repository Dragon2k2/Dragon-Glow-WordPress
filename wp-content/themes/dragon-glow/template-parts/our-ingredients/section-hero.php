<?php
/**
 * Dragon Glow — Our Ingredients: Hero
 * Mirrors stitch prototype: image left + text right, editorial split layout.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data = dg_our_ingredients_data();
$hero = $data['hero'];
?>
<section class="dg-oi-hero" aria-label="<?php echo esc_attr( $hero['badge'] ); ?>">
	<div class="dg-oi-hero-inner">

		<!-- Image: left col-span-5, full height -->
		<div class="dg-oi-hero-image" data-sr>
			<img
				src="<?php echo esc_url( $hero['image_url'] ); ?>"
				alt="<?php echo esc_attr( $hero['image_alt'] ); ?>"
				loading="eager"
				fetchpriority="high"
				decoding="async"
			>
		</div>

		<!-- Text: right col-span-6, offset -->
		<div class="dg-oi-hero-content" data-sr>
			<p class="dg-oi-hero-badge"><?php echo esc_html( $hero['badge'] ); ?></p>
			<h1 class="dg-oi-hero-headline"><?php echo esc_html( $hero['headline'] ); ?></h1>

			<?php if ( $hero['accent_line'] ) : ?>
			<div class="dg-oi-hero-accent" aria-hidden="true"></div>
			<?php endif; ?>

			<p class="dg-oi-hero-body">
				<?php
				$body_lines = explode( "\n", $hero['body'] );
				foreach ( $body_lines as $line ) :
					echo esc_html( $line ) . '<br>';
				endforeach;
				?>
			</p>

			<a href="<?php echo esc_url( $hero['cta_url'] ); ?>"
			   class="dg-oi-btn">
				<?php echo esc_html( $hero['cta_text'] ); ?>
			</a>
		</div>
	</div>

	<!-- Pullquote — full width below split -->
	<div class="dg-oi-pullquote" data-sr>
		<h2 class="dg-oi-pullquote-text">
			<?php echo esc_html( $data['pullquote']['text'] ); ?>
		</h2>
	</div>
</section>
