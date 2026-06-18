<?php
/**
 * Dragon Glow — Ingredient Philosophy Section
 * "Harnessing the Dragon Fruit" / "The Alchemy of Eternal Glow"
 * Matches Stitch design: shop-page1 / shop-page2
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$eyebrow   = get_theme_mod( 'dg_shop_philosophy_eyebrow', __( 'The Essence', 'dragon-glow' ) );
$title     = get_theme_mod( 'dg_shop_philosophy_title', __( 'Harnessing the Dragon Fruit', 'dragon-glow' ) );
$paragraph = get_theme_mod(
	'dg_shop_philosophy_text',
	__( 'Our signature Hylocereus enzyme complex is cold-pressed to preserve its vital nutrients. This "Luminous Bio-C" promotes cellular turnover without irritation, providing the foundation for a clinical-grade glow.', 'dragon-glow' )
);
$bullets   = array(
	get_theme_mod( 'dg_shop_philosophy_bullet1', __( 'Responsibly Sourced Micro-Farms', 'dragon-glow' ) ),
	get_theme_mod( 'dg_shop_philosophy_bullet2', __( 'Cold-Distilled Molecular Integrity', 'dragon-glow' ) ),
	get_theme_mod( 'dg_shop_philosophy_bullet3', __( 'Dermatologist-Approved Efficacy', 'dragon-glow' ) ),
);
$cta_text  = get_theme_mod( 'dg_shop_philosophy_cta', __( 'Our Ingredient Glossary', 'dragon-glow' ) );
$cta_url   = get_theme_mod( 'dg_shop_philosophy_cta_url', home_url( '/our-story/' ) );

$image_main = get_theme_mod( 'dg_shop_philosophy_image_main', get_template_directory_uri() . '/assets/images/shop/philosophy-main.jpg' );
$image_card = get_theme_mod( 'dg_shop_philosophy_image_card', get_template_directory_uri() . '/assets/images/shop/philosophy-card.webp' );
?>
<section class="bg-surface-container py-section-gap overflow-hidden" id="dg-shop-philosophy">
	<div class="px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter items-center">

			<div class="reveal-on-scroll active">
				<span class="font-label-sm text-label-sm text-primary tracking-[0.2em] mb-4 block">
					<?php echo esc_html( $eyebrow ); ?>
				</span>
				<h2 class="font-headline-lg text-headline-lg text-on-surface mb-8">
					<?php echo esc_html( $title ); ?>
				</h2>
				<p class="font-body-lg text-body-lg text-on-surface-variant mb-6 leading-relaxed">
					<?php echo esc_html( $paragraph ); ?>
				</p>
				<ul class="space-y-4 mb-10">
					<?php foreach ( $bullets as $bullet ) : ?>
						<?php if ( $bullet ) : ?>
							<li class="flex items-center gap-4 group cursor-default">
								<span class="w-1.5 h-1.5 bg-primary-container rounded-full group-hover:scale-150 transition-transform duration-300"></span>
								<span class="font-body-md text-body-md italic text-on-surface group-hover:text-primary transition-colors duration-300">
									<?php echo esc_html( $bullet ); ?>
								</span>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<a class="inline-block border-b-2 border-primary-container pb-1 font-label-sm text-label-sm uppercase tracking-widest hover:text-primary hover:border-primary transition-all duration-300 transform hover:translate-x-1"
				   href="<?php echo esc_url( $cta_url ); ?>">
					<?php echo esc_html( $cta_text ); ?>
				</a>
			</div>

			<div class="relative reveal-on-scroll active" style="transition-delay: 200ms;">
				<div class="aspect-square bg-surface overflow-hidden luxury-shadow rotate-2 group hover:rotate-0 transition-transform duration-1000 ease-out">
					<?php if ( $image_main ) : ?>
						<img alt="<?php esc_attr_e( 'Dragon Fruit — The Essence of Glow', 'dragon-glow' ); ?>"
							 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000"
							 src="<?php echo esc_url( $image_main ); ?>" />
					<?php endif; ?>
				</div>
				<?php if ( $image_card ) : ?>
					<div class="absolute -bottom-12 -left-12 hidden md:block w-64 aspect-[4/5] bg-surface-container-high p-4 luxury-shadow -rotate-6 hover:rotate-0 hover:z-20 transition-transform duration-700 ease-out">
						<img alt="<?php esc_attr_e( 'Clinical Laboratory — Dragon Glow', 'dragon-glow' ); ?>"
							 class="w-full h-full object-cover"
							 src="<?php echo esc_url( $image_card ); ?>" />
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
