<?php
/**
 * Dragon Glow — Featured Categories
 * 6 category cards with hover effects
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Define categories with images from original HTML
$categories = array(
    array(
        'name'        => __( 'Cleansers', 'dragon-glow' ),
        'tag'         => __( 'Gentle', 'dragon-glow' ),
        'image'       => get_theme_file_uri( 'assets/images/home/category-cleansers.jpg' ),
        'link'        => dg_is_woocommerce_active() ? get_term_link( 'cleansers', 'product_cat' ) : '#',
    ),
    array(
        'name'        => __( 'Serums', 'dragon-glow' ),
        'tag'         => __( 'Potent', 'dragon-glow' ),
        'image'       => get_theme_file_uri( 'assets/images/home/category-serums.webp' ),
        'link'        => dg_is_woocommerce_active() ? get_term_link( 'serums', 'product_cat' ) : '#',
    ),
    array(
        'name'        => __( 'Moisturizers', 'dragon-glow' ),
        'tag'         => __( 'Hydrate', 'dragon-glow' ),
        'image'       => get_theme_file_uri( 'assets/images/home/category-moisturizers.webp' ),
        'link'        => dg_is_woocommerce_active() ? get_term_link( 'moisturizers', 'product_cat' ) : '#',
    ),
    array(
        'name'        => __( 'Blush', 'dragon-glow' ),
        'tag'         => __( 'Glow', 'dragon-glow' ),
        'image'       => get_theme_file_uri( 'assets/images/home/category-blush.webp' ),
        'link'        => dg_is_woocommerce_active() ? get_term_link( 'blush', 'product_cat' ) : '#',
    ),
    array(
        'name'        => __( 'Sun Care', 'dragon-glow' ),
        'tag'         => __( 'Protect', 'dragon-glow' ),
        'image'       => get_theme_file_uri( 'assets/images/home/category-sun-care.webp' ),
        'link'        => dg_is_woocommerce_active() ? get_term_link( 'sun-care', 'product_cat' ) : '#',
    ),
    array(
        'name'        => __( 'Lip Color', 'dragon-glow' ),
        'tag'         => __( 'Nourish', 'dragon-glow' ),
        'image'       => get_theme_file_uri( 'assets/images/home/category-lip-color.webp' ),
        'link'        => dg_is_woocommerce_active() ? get_term_link( 'lip-color', 'product_cat' ) : '#',
    ),
);
?>
<section class="py-section-gap max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
    <div class="text-center mb-16 reveal">
        <h2 class="font-headline text-headline-lg text-primary mb-4"><?php esc_html_e( 'Curated Rituals', 'dragon-glow' ); ?></h2>
        <p class="text-on-surface-variant max-w-xl mx-auto"><?php esc_html_e( "Explore our collection of targeted treatments designed to bring out your skin's natural luminescence.", 'dragon-glow' ); ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ( $categories as $index => $cat ) : ?>
        <div class="group relative aspect-[4/5] overflow-hidden rounded-3xl cursor-pointer reveal" style="transition-delay: <?php echo esc_attr( ( $index % 3 ) * 100 ); ?>ms;">
            <a href="<?php echo esc_url( $cat['link'] ); ?>">
                <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                     src="<?php echo esc_url( $cat['image'] ); ?>"
                     alt="<?php echo esc_attr( $cat['name'] ); ?>"
                     loading="lazy" />
                <div class="absolute bottom-0 left-0 p-8 w-full translate-y-4 group-hover:translate-y-0 transition-transform">
                    <span class="bg-white/80 backdrop-blur text-primary px-4 py-1 rounded-full text-xs font-bold mb-3 inline-block"><?php echo esc_html( $cat['tag'] ); ?></span>
                    <h3 class="font-headline text-headline-md text-white drop-shadow-md"><?php echo esc_html( $cat['name'] ); ?></h3>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>
