<?php
/**
 * Dragon Glow — Brand Rituals Section
 * "A Daily Invocation" — AM / PM ritual cards
 * Matches Stitch design: shop-page1 / shop-page2
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$section_title = get_theme_mod( 'dg_shop_rituals_title', __( 'A Daily Invocation', 'dragon-glow' ) );
$section_text  = get_theme_mod(
	'dg_shop_rituals_text',
	__( 'Beauty is not a task, it is a ritual of self-reverence. Discover our curated sequences for dawn and dusk.', 'dragon-glow' )
);

$am_eyebrow   = get_theme_mod( 'dg_shop_rituals_am_eyebrow', __( 'AM Ritual', 'dragon-glow' ) );
$am_title     = get_theme_mod( 'dg_shop_rituals_am_title', __( 'The Dawn Awakening', 'dragon-glow' ) );
$am_image     = get_theme_mod( 'dg_shop_rituals_am_image', get_template_directory_uri() . '/assets/images/shop/rituals-am.webp' );
$am_url       = get_theme_mod( 'dg_shop_rituals_am_url', home_url( '/shop/?ritual=am' ) );

$pm_eyebrow   = get_theme_mod( 'dg_shop_rituals_pm_eyebrow', __( 'PM Ritual', 'dragon-glow' ) );
$pm_title     = get_theme_mod( 'dg_shop_rituals_pm_title', __( 'The Evening Rebirth', 'dragon-glow' ) );
$pm_image     = get_theme_mod( 'dg_shop_rituals_pm_image', get_template_directory_uri() . '/assets/images/shop/rituals-pm.webp' );
$pm_url       = get_theme_mod( 'dg_shop_rituals_pm_url', home_url( '/shop/?ritual=pm' ) );
?>
<section class="py-section-gap bg-surface" id="dg-shop-rituals">
	<div class="text-center mb-20 px-margin-mobile reveal-on-scroll active">
		<h2 class="font-headline-lg text-headline-lg text-on-surface mb-4">
			<?php echo esc_html( $section_title ); ?>
		</h2>
		<p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">
			<?php echo esc_html( $section_text ); ?>
		</p>
	</div>
	<div class="px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto flex flex-col md:flex-row gap-gutter">

		<a class="flex-1 group cursor-pointer product-card-hover reveal-on-scroll active" href="<?php echo esc_url( $am_url ); ?>">
			<div class="relative h-[600px] overflow-hidden mb-8">
				<?php if ( $am_image ) : ?>
					<img alt="<?php echo esc_attr( $am_title ); ?>"
						 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 ease-out"
						 src="<?php echo esc_url( $am_image ); ?>" />
				<?php endif; ?>
				<div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors duration-700"></div>
				<div class="absolute bottom-10 left-10 text-white transform group-hover:-translate-y-2 transition-transform duration-500">
					<span class="font-label-sm text-label-sm uppercase tracking-[0.3em] mb-2 block">
						<?php echo esc_html( $am_eyebrow ); ?>
					</span>
					<h3 class="font-headline-md text-headline-md">
						<?php echo esc_html( $am_title ); ?>
					</h3>
				</div>
			</div>
		</a>

		<a class="flex-1 group cursor-pointer product-card-hover reveal-on-scroll active"
		   style="transition-delay: 200ms;"
		   href="<?php echo esc_url( $pm_url ); ?>">
			<div class="relative h-[600px] overflow-hidden mb-8">
				<?php if ( $pm_image ) : ?>
					<img alt="<?php echo esc_attr( $pm_title ); ?>"
						 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 ease-out"
						 src="<?php echo esc_url( $pm_image ); ?>" />
				<?php endif; ?>
				<div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors duration-700"></div>
				<div class="absolute bottom-10 left-10 text-white transform group-hover:-translate-y-2 transition-transform duration-500">
					<span class="font-label-sm text-label-sm uppercase tracking-[0.3em] mb-2 block">
						<?php echo esc_html( $pm_eyebrow ); ?>
					</span>
					<h3 class="font-headline-md text-headline-md">
						<?php echo esc_html( $pm_title ); ?>
					</h3>
				</div>
			</div>
		</a>

	</div>
</section>
