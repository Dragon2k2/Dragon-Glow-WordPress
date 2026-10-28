<?php
/**
 * Dragon Glow — Hero Section
 * 2-column layout: left copy (badge + gradient headline + SVG underline + description + CTAs + stats)
 *                  right product frame (image + floating badges + info card)
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Hero customizer data with defaults
$hero_image  = get_theme_mod( 'dg_hero_image', get_template_directory_uri() . '/assets/images/home/hero-image.webp' );
$badge_text  = get_theme_mod( 'dg_hero_badge_text', __( '2026 Cellular Drop 04', 'dragon-glow' ) );
$badge_sub   = get_theme_mod( 'dg_hero_badge_sub', __( 'Ultra-Bounce Skin', 'dragon-glow' ) );
$hero_title  = get_theme_mod( 'dg_hero_title', __( 'Get Your Dragon Glaze', 'dragon-glow' ) );
$hero_title_highlight = get_theme_mod( 'dg_hero_title_highlight', __( 'Dragon Glaze', 'dragon-glow' ) );
$hero_sub    = get_theme_mod( 'dg_hero_subtitle', __( 'An ancient Dragon Superfruit ritual, perfected by clinical skincare. Fermented Hylocereus Bio-C and micro-encapsulated hyaluronic spheres melt into skin for mirror-clear, glass-like luminosity — weightless to wear, endless in glow.', 'dragon-glow' ) );

$cta1_text   = get_theme_mod( 'dg_cta1_text', __( 'Shop The Glaze Ritual', 'dragon-glow' ) );
$cta1_url    = get_theme_mod( 'dg_cta1_url', dg_is_woocommerce_active() ? get_permalink( wc_get_page_id( 'shop' ) ) : '#' );
$cta1_icon   = get_theme_mod( 'dg_cta1_icon', 'bolt' );

$cta2_text   = get_theme_mod( 'dg_cta2_text', __( 'Discover Our Story', 'dragon-glow' ) );
$cta2_url    = get_theme_mod( 'dg_cta2_url', get_permalink( get_page_by_path( 'our-story' ) ) ?: home_url( '/our-story/' ) );
$cta2_icon   = get_theme_mod( 'dg_cta2_icon', 'auto_stories' );

// Stats data
$stat1_value = get_theme_mod( 'dg_hero_stat1_value', '+98.4%' );
$stat1_label = get_theme_mod( 'dg_hero_stat1_label', __( 'Instant Surface Glass Reflection', 'dragon-glow' ) );

$stat2_value = get_theme_mod( 'dg_hero_stat2_value', '72 Hrs' );
$stat2_label = get_theme_mod( 'dg_hero_stat2_label', __( 'Continuous Lipid Hydration', 'dragon-glow' ) );

// Product card data
$product_title   = get_theme_mod( 'dg_hero_product_title', __( 'Dragon Glaze Elixir', 'dragon-glow' ) );
$product_desc    = get_theme_mod( 'dg_hero_product_desc', __( 'Bio-Fermented Superfruit Complex', 'dragon-glow' ) );
$product_price   = get_theme_mod( 'dg_hero_product_price', '$52.00' );
$product_restock = get_theme_mod( 'dg_hero_product_restock', __( 'Restocked 4h ago', 'dragon-glow' ) );

// Floating sticker data
$sticker1_text = get_theme_mod( 'dg_hero_sticker1_text', __( '100% Juicy Bounce', 'dragon-glow' ) );
$sticker2_text = get_theme_mod( 'dg_hero_sticker2_text', __( '280M+ Reels Views', 'dragon-glow' ) );
?>

<section class="dg-home-hero max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-10 pb-16 lg:pb-24">
        <!-- Left Column: Copy & CTAs -->
        <div class="lg:col-span-6 flex flex-col items-start gap-6 z-10">
            
            <!-- Badge Pill -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-tertiary-container/30 backdrop-blur-md shadow-sm">
                <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-secondary animate-ping"></span>
                <span class="font-label-sm text-label-sm uppercase tracking-widest text-on-tertiary-container font-semibold">
                    <?php echo esc_html( $badge_text ); ?>
                </span>
                <span class="text-tertiary">•</span>
                <span class="font-label-sm text-label-sm text-on-tertiary-container font-medium">
                    <?php echo esc_html( $badge_sub ); ?>
                </span>
            </div>

            <!-- Gradient Headline with SVG Underline -->
            <div class="space-y-2">
                <h1 class="font-display-xl text-display-xl lg:text-[72px] lg:leading-[80px] text-on-surface tracking-tight">
                    <?php 
                    // Parse title and wrap highlight in gradient span
                    $title_parts = str_replace( $hero_title_highlight, '[HIGHLIGHT]' . $hero_title_highlight . '[/HIGHLIGHT]', $hero_title );
                    $title_parts = explode( '[HIGHLIGHT]', $title_parts );
                    
                    foreach ( $title_parts as $i => $part ) {
                        if ( strpos( $part, '[/HIGHLIGHT]' ) !== false ) {
                            $text = str_replace( '[/HIGHLIGHT]', '', $part );
                            echo '<span class="dg-home-hero__gradient-text-wrapper">';
                            echo '<span class="relative inline-block text-transparent bg-clip-text dg-home-hero__gradient-text">';
                            echo esc_html( $text );
                            echo '<svg class="dg-home-hero__gradient-underline absolute -bottom-2 left-0 w-full h-3 opacity-70" fill="none" preserveAspectRatio="none" viewBox="0 0 200 12">';
                            echo '<path d="M2 9.5C65 2.5 135 2 198 9.5" stroke="currentColor" stroke-linecap="round" stroke-width="4"></path>';
                            echo '</svg></span>';
                            echo '<span class="dg-hero-emoji-accent">✨</span>';
                            echo '</span>';
                        } else {
                            echo esc_html( $part );
                        }
                    }
                    ?>
                    <?php echo esc_html( __( 'On.', 'dragon-glow' ) ); ?>
                </h1>
            </div>

            <!-- Description -->
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl leading-relaxed">
                <?php echo esc_html( $hero_sub ); ?>
            </p>

            <!-- CTAs -->
            <div class="flex flex-wrap items-center gap-4 pt-2 w-full sm:w-auto">
                <a href="<?php echo esc_url( $cta1_url ); ?>" 
                   class="dg-home-hero__cta-primary w-full sm:w-auto px-6 py-3 rounded-full text-on-secondary font-label-sm text-label-sm uppercase tracking-wider shadow-lg hover:scale-105 active:scale-95 transition-all text-center inline-flex items-center justify-center gap-2.5">
                    <span><?php echo esc_html( $cta1_text ); ?></span>
                    <span class="material-symbols-outlined text-[18px] flex-shrink-0"><?php echo esc_attr( $cta1_icon ); ?></span>
                </a>
                <a href="<?php echo esc_url( $cta2_url ); ?>" 
                   class="w-full sm:w-auto px-5 py-3 rounded-full bg-surface-container-lowest/80 backdrop-blur-md text-on-surface font-label-sm text-label-sm uppercase tracking-wider hover:bg-surface-container-low shadow-sm hover:scale-105 active:scale-95 transition-all text-center inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-[18px] flex-shrink-0"><?php echo esc_attr( $cta2_icon ); ?></span>
                    <span><?php echo esc_html( $cta2_text ); ?></span>
                </a>
            </div>

            <!-- Proof Stats Row -->
            <div class="pt-6 w-full flex items-center gap-6 sm:gap-10">
                <div class="flex flex-col">
                    <span class="font-headline-sm text-headline-sm text-on-surface tracking-tight">
                        <?php echo esc_html( $stat1_value ); ?>
                    </span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">
                        <?php echo esc_html( $stat1_label ); ?>
                    </span>
                </div>
                <div class="w-px h-10 bg-outline-variant/50"></div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-headline-sm text-on-surface tracking-tight">
                        <?php echo esc_html( $stat2_value ); ?>
                    </span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">
                        <?php echo esc_html( $stat2_label ); ?>
                    </span>
                </div>
                <div class="w-px h-10 bg-outline-variant/50"></div>
                <div class="flex flex-col">
                    <div class="flex items-center text-primary-container">
                        <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                        <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                        <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                        <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                        <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                    </div>
                    <span class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">
                        <?php echo esc_html( __( '4.9/5 (18.2K+ Glaze Addicts)', 'dragon-glow' ) ); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Column: Product Frame with Floating Elements -->
        <div class="lg:col-span-6 relative flex justify-center items-center">
            <!-- Main Hero Product Frame -->
            <div class="dg-home-hero__product-frame relative w-full max-w-[520px] aspect-[4/5] rounded-[2.5rem] p-3 bg-gradient-to-tr from-surface-container-lowest via-surface-container-low to-surface-container-lowest shadow-2xl overflow-hidden group">
                
                <?php if ( $hero_image ) : ?>
                    <img src="<?php echo esc_url( $hero_image ); ?>" 
                         alt="<?php echo esc_attr( $product_title ); ?>"
                         data-hero-img
                         class="w-full h-full object-cover rounded-[2rem]" />
                <?php else : ?>
                    <div class="w-full h-full bg-gradient-to-br from-primary-container via-background to-secondary-container rounded-[2rem]"></div>
                <?php endif; ?>

                <!-- Iridescent Rim Glow -->
                <div class="absolute inset-0 rounded-[2.5rem] bg-gradient-to-t from-surface-dim/40 via-transparent to-transparent pointer-events-none"></div>

                <!-- Floating Sticker 1: 100% Juicy Tag (Top Right) -->
                <div class="dg-home-hero__sticker dg-home-hero__sticker--1 absolute top-6 right-6 px-4 py-2 rounded-full flex items-center gap-2 cursor-pointer transform rotate-6">
                    <span class="w-2.5 h-2.5 rounded-full animate-pulse"></span>
                    <span class="font-label-sm text-label-sm uppercase font-bold tracking-widest">
                        <?php echo esc_html( $sticker1_text ); ?>
                    </span>
                </div>

                <!-- Floating Sticker 2: TikTok Viral Badge (Top Left) -->
                <div class="dg-home-hero__sticker dg-home-hero__sticker--2 absolute top-8 left-6 px-4 py-2 rounded-2xl flex items-center gap-2 transform -rotate-6">
                    <span class="material-symbols-outlined text-[18px]">movie</span>
                    <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">
                        <?php echo esc_html( $sticker2_text ); ?>
                    </span>
                </div>

                <!-- Floating Card: Product Info (Bottom) -->
                <div class="dg-home-hero__info-card absolute bottom-6 left-6 right-6 rounded-2xl backdrop-blur-xl shadow-xl flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="dg-badge-vibrant rounded-xl p-0.5 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">water_drop</span>
                        </div>
                        <div style="margin-left: var(--info-card-gap);">
                            <h4 class="font-semibold">
                                <?php echo esc_html( $product_title ); ?>
                            </h4>
                            <p>
                                <?php echo esc_html( $product_desc ); ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="dg-info-card-price font-label-lg text-label-lg font-bold">
                            <?php echo esc_html( $product_price ); ?>
                        </span>
                        <span class="dg-info-card-restock font-label-sm text-[10px] uppercase">
                            <?php echo esc_html( $product_restock ); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Decorative Floating Holographic Orb -->
            <div class="dg-glaze-orb dg-home-hero__orb absolute w-24 h-24 rounded-full p-1 shadow-2xl flex items-center justify-center text-center transform rotate-12 animate-bounce cursor-pointer">
                <span class="dg-glaze-orb-text font-label-sm text-label-sm uppercase font-bold leading-tight">
                    <?php echo esc_html( __( 'Glaze', 'dragon-glow' ) ); ?><br><?php echo esc_html( __( 'Certified', 'dragon-glow' ) ); ?>
                </span>
            </div>
            </div>
        </div>
</section>
