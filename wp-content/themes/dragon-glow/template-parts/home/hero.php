<?php
/**
 * Dragon Glow — Hero Section
 * Full-viewport hero: hero image + overlay + headline + 2 CTAs + blob decorations
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$hero_image  = get_theme_mod( 'dg_hero_image', get_template_directory_uri() . '/assets/images/home/hero-image.webp' );
$hero_title  = get_theme_mod( 'dg_hero_title', __( 'Glow with the Power of Nature', 'dragon-glow' ) );
$hero_sub    = get_theme_mod( 'dg_hero_subtitle', __( 'Premium skincare rituals meticulously crafted with ethereal botanicals for a luminous, transcendent complexion. Elevate your daily routine into a sacred moment of self-love.', 'dragon-glow' ) );
$cta1_text   = get_theme_mod( 'dg_cta1_text', __( 'Shop Now', 'dragon-glow' ) );
$cta1_url    = get_theme_mod( 'dg_cta1_url', dg_is_woocommerce_active() ? get_permalink( wc_get_page_id( 'shop' ) ) : '#' );
$cta2_text   = get_theme_mod( 'dg_cta2_text', __( 'Discover the Ritual', 'dragon-glow' ) );
$cta2_url    = get_theme_mod( 'dg_cta2_url', get_permalink( get_page_by_path( 'our-story' ) ) ?: home_url( '/our-story/' ) );
?>
<header class="relative h-[90vh] flex items-center overflow-hidden">
    <!-- Background Image with Parallax -->
    <div class="absolute inset-0 z-0">
        <?php if ( $hero_image ) : ?>
            <img src="<?php echo esc_url( $hero_image ); ?>"
                 alt="<?php esc_attr_e( 'Dragon Glow Hero', 'dragon-glow' ); ?>"
                 class="w-full h-full object-cover object-[80%_30%]"
                 style="transform: translateY(var(--scroll-y, 0));"
                 loading="eager" />
        <?php else : ?>
            <div class="w-full h-full bg-gradient-to-br from-primary-container via-background to-secondary-container"></div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-r from-surface/80 via-surface/20 to-transparent"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop w-full">
        <div class="max-w-2xl reveal">
            <h1 class="font-display text-display-lg md:text-display-lg text-primary mb-6 leading-tight">
                <?php echo esc_html( $hero_title ); ?>
            </h1>
            <p class="font-body text-body-lg text-on-surface-variant mb-10 leading-relaxed">
                <?php echo esc_html( $hero_sub ); ?>
            </p>
            <div class="flex gap-4 items-center">
                <a href="<?php echo esc_url( $cta1_url ); ?>"
                   class="bg-gradient-to-br from-primary-container to-secondary-container text-on-primary-container px-10 py-5 rounded-full font-bold shadow-lg hover:shadow-primary-container/40 transition-all duration-300 border-b-2 border-tertiary-container cta-glow active:scale-95">
                    <?php echo esc_html( $cta1_text ); ?>
                </a>
                <a href="<?php echo esc_url( $cta2_url ); ?>"
                   class="border border-tertiary text-tertiary px-10 py-5 rounded-full font-bold hover:bg-tertiary hover:text-white transition-all">
                    <?php echo esc_html( $cta2_text ); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Decorative Blobs -->
    <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-primary-container/30 rounded-full blur-[100px] animate-pulse pointer-events-none"></div>
    <div class="absolute top-40 -left-20 w-80 h-80 bg-secondary-container/20 rounded-full blur-[80px] pointer-events-none"></div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-on-surface-variant">
        <span class="text-label-sm font-label-sm uppercase tracking-widest"><?php esc_html_e( 'Scroll', 'dragon-glow' ); ?></span>
        <span class="material-symbols-outlined animate-bounce">expand_more</span>
    </div>
</header>
