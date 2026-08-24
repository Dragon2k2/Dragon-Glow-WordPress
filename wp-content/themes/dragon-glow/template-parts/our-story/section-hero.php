<?php
/**
 * Template part — Our Story / Hero
 *
 * Full-bleed image + dark overlay + center text (eyebrow + headline + body
 * + anchor CTA). Data từ dg_our_story_hero_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero = dg_our_story_hero_data();
?>
<section class="relative h-screen w-full flex items-center justify-center overflow-hidden">
	<div class="absolute inset-0">
		<img alt="<?php echo esc_attr( $hero['image_alt'] ); ?>"
			 class="w-full h-full object-cover"
			 src="<?php echo esc_url( $hero['image_url'] ); ?>" />
		<div class="absolute inset-0 bg-black/20"></div>
	</div>
	<div class="relative z-10 text-center px-margin-mobile reveal-on-scroll">
		<p class="text-primary-container dg-story-eyebrow-hero mb-6">
			<?php echo esc_html( $hero['eyebrow'] ); ?>
		</p>
		<h1 class="font-serif text-white text-4xl md:text-6xl lg:text-7xl mb-8 max-w-4xl mx-auto leading-tight font-semibold tracking-tight">
			<?php echo esc_html( $hero['headline'] ); ?>
		</h1>
		<p class="text-white/90 max-w-2xl mx-auto mb-10 text-lg leading-relaxed">
			<?php echo esc_html( $hero['body'] ); ?>
		</p>
		<a href="<?php echo esc_url( $hero['cta_href'] ); ?>" class="inline-block bg-primary-container hover:bg-primary text-on-primary-container hover:text-white px-10 py-4 uppercase dg-story-eyebrow text-sm transition-all duration-500 leading-none">
			<?php echo esc_html( $hero['cta_label'] ); ?>
		</a>
	</div>
	<div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
	</div>
</section>
