<?php
/**
 * Dragon Glow — Page Hero Partial
 * Reusable hero block for interior pages.
 *
 * @package Dragon_Glow
 *
 * Usage:
 *   set_query_var( 'dg_hero_title',    __( 'Your Title',    'dragon-glow' ) );
 *   set_query_var( 'dg_hero_subtitle', __( 'Your subtitle.', 'dragon-glow' ) );
 *   get_template_part( 'template-parts/global/page-hero' );
 */

defined( 'ABSPATH' ) || exit;

$hero_title    = (string) get_query_var( 'dg_hero_title', '' );
$hero_subtitle = (string) get_query_var( 'dg_hero_subtitle', '' );
?>
<section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mb-16 md:mb-24 text-center page-hero-section">
    <?php if ( $hero_title ) : ?>
        <h1 class="font-display-lg text-display-lg mb-6 text-glow">
            <?php echo esc_html( $hero_title ); ?>
        </h1>
    <?php endif; ?>
    <?php if ( $hero_subtitle ) : ?>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto leading-relaxed">
            <?php echo esc_html( $hero_subtitle ); ?>
        </p>
    <?php endif; ?>
</section>
