<?php
/**
 * Dragon Glow — Shop Hero Banner
 * Immersive narrative hero for the shop page
 * Matches Stitch design: shop-page1 / shop-page2
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// ACF / Customizer support (optional). Falls back to defaults.
$hero_image     = get_theme_mod( 'dg_shop_hero_image', get_template_directory_uri() . '/assets/images/shop/hero-default.jpg' );
$hero_eyebrow   = get_theme_mod( 'dg_shop_hero_eyebrow', __( 'Editorial Edition', 'dragon-glow' ) );
$hero_title_a   = get_theme_mod( 'dg_shop_hero_title_a', __( 'The Art of', 'dragon-glow' ) );
$hero_title_b   = get_theme_mod( 'dg_shop_hero_title_b', __( 'Luminous', 'dragon-glow' ) );
$hero_title_c   = get_theme_mod( 'dg_shop_hero_title_c', __( 'Being', 'dragon-glow' ) );
$hero_text      = get_theme_mod(
	'dg_shop_hero_text',
	__( 'Experience the definitive collection of clinical radiance. Where ancient dragon fruit enzymes meet contemporary molecular science for an unparalleled glow.', 'dragon-glow' )
);
$hero_btn1_text = get_theme_mod( 'dg_shop_hero_btn1_text', __( 'Shop The Series', 'dragon-glow' ) );
$hero_btn1_url  = get_theme_mod( 'dg_shop_hero_btn1_url', '#products' );
$hero_btn2_text = get_theme_mod( 'dg_shop_hero_btn2_text', __( 'Explore Philosophy', 'dragon-glow' ) );
$hero_btn2_url  = get_theme_mod( 'dg_shop_hero_btn2_url', home_url( '/our-story/' ) );
?>
<header class="relative w-full h-[90vh] overflow-hidden flex items-center" id="dg-shop-hero">
	<div class="absolute inset-0 z-0">
		<?php if ( $hero_image ) : ?>
			<img alt="<?php esc_attr_e( 'Dragon Glow — Editorial Collection', 'dragon-glow' ); ?>"
				 class="w-full h-full object-cover object-center scale-105 transition-transform duration-1000 ease-out"
				 id="dg-shop-hero-img"
				 src="<?php echo esc_url( $hero_image ); ?>" />
		<?php endif; ?>
		<div class="absolute inset-0 bg-gradient-to-r from-background/40 to-transparent"></div>
	</div>
	<div class="relative z-10 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto w-full">
		<div class="max-w-2xl reveal-on-scroll active">
			<span class="font-label-sm text-label-sm text-primary tracking-[0.2em] mb-4 block">
				<?php echo esc_html( $hero_eyebrow ); ?>
			</span>
			<h1 class="font-display-lg text-display-lg text-primary-container mb-8 leading-tight">
				<?php echo esc_html( $hero_title_a ); ?><br/>
				<i class="italic font-normal"><?php echo esc_html( $hero_title_b ); ?></i> <?php echo esc_html( $hero_title_c ); ?>
			</h1>
			<p class="font-body-lg text-body-lg text-on-surface mb-10 max-w-lg">
				<?php echo esc_html( $hero_text ); ?>
			</p>
			<div class="flex flex-wrap gap-6">
				<a class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest hover:brightness-110"
				   href="<?php echo esc_url( $hero_btn1_url ); ?>">
					<?php echo esc_html( $hero_btn1_text ); ?>
				</a>
				<a class="btn-luxury border border-primary text-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary/5"
				   href="<?php echo esc_url( $hero_btn2_url ); ?>">
					<?php echo esc_html( $hero_btn2_text ); ?>
				</a>
			</div>
		</div>
	</div>
</header>
