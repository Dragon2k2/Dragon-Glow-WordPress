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
<section class="dg-trust-badges">
    <div class="dg-trust-badges__inner">
        <?php foreach ( $badges as $index => $badge ) : ?>
        <div class="dg-trust-badges__item flex items-center gap-4 reveal" style="transition-delay: <?php echo esc_attr( $index * 100 ); ?>ms;">
            <span class="dg-trust-badges__icon material-symbols-outlined text-primary text-3xl"><?php echo esc_html( $badge['icon'] ); ?></span>
            <div class="dg-trust-badges__text">
                <p class="dg-trust-badges__title font-bold text-on-surface"><?php echo esc_html( $badge['title'] ); ?></p>
                <p class="dg-trust-badges__desc text-sm text-on-surface-variant"><?php echo esc_html( $badge['desc'] ); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
