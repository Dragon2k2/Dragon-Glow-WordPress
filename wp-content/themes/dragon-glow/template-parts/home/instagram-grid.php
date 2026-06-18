<?php
/**
 * Dragon Glow — Instagram Grid Section
 * Community gallery with 4 images
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Instagram images from original HTML
$instagram_images = array(
    array(
        'src'   => get_theme_file_uri( 'assets/images/home/instagram-01.webp' ),
        'alt'   => 'Flat lay of Dragon Glow products',
    ),
    array(
        'src'   => get_theme_file_uri( 'assets/images/home/instagram-02.webp' ),
        'alt'   => 'Hands applying glowing serum',
    ),
    array(
        'src'   => get_theme_file_uri( 'assets/images/home/instagram-03.webp' ),
        'alt'   => 'Minimalist vanity arrangement',
    ),
    array(
        'src'   => get_theme_file_uri( 'assets/images/home/instagram-04.webp' ),
        'alt'   => 'Water droplets on skincare product',
    ),
);
?>
<section class="py-section-gap max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
    <div class="text-center mb-16 reveal">
        <h2 class="font-headline text-headline-lg text-primary mb-4"><?php esc_html_e( 'Captured in the Light', 'dragon-glow' ); ?></h2>
        <p class="text-on-surface-variant"><?php esc_html_e( 'Tag @DragonGlow to be featured in our community gallery.', 'dragon-glow' ); ?></p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ( $instagram_images as $index => $img ) : ?>
        <div class="aspect-square relative group overflow-hidden rounded-2xl cursor-pointer reveal" style="transition-delay: <?php echo esc_attr( $index * 100 ); ?>ms;">
            <img class="w-full h-full object-cover transition-transform group-hover:scale-110"
                 src="<?php echo esc_url( $img['src'] ); ?>"
                 alt="<?php echo esc_attr( $img['alt'] ); ?>"
                 loading="lazy" />
            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-4xl">favorite</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
