<?php
/**
 * Dragon Glow — Trust Badges Bar
 * 4 badges showcasing brand values in horizontal layout
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$badges = array(
    array(
        'icon'  => 'local_shipping',
        'title' => __( 'Free Shipping', 'dragon-glow' ),
        'desc'  => __( 'On orders over $75', 'dragon-glow' ),
    ),
    array(
        'icon'  => 'eco',
        'title' => __( 'Cruelty-Free', 'dragon-glow' ),
        'desc'  => __( '100% Vegan formulas', 'dragon-glow' ),
    ),
    array(
        'icon'  => 'medical_services',
        'title' => __( 'Expert Tested', 'dragon-glow' ),
        'desc'  => __( 'Dermatologist approved', 'dragon-glow' ),
    ),
    array(
        'icon'  => 'history',
        'title' => __( '30-Day Returns', 'dragon-glow' ),
        'desc'  => __( 'Hassle-free guarantee', 'dragon-glow' ),
    ),
);
?>
<section class="bg-surface-container-low py-12 border-y border-outline-variant/10">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-2 md:grid-cols-4 gap-8">
        <?php foreach ( $badges as $index => $badge ) : ?>
        <div class="flex items-center gap-4 reveal" style="transition-delay: <?php echo esc_attr( $index * 100 ); ?>ms;">
            <span class="material-symbols-outlined text-primary text-3xl"><?php echo esc_html( $badge['icon'] ); ?></span>
            <div>
                <p class="font-bold text-on-surface"><?php echo esc_html( $badge['title'] ); ?></p>
                <p class="text-sm text-on-surface-variant"><?php echo esc_html( $badge['desc'] ); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
