<?php
/**
 * Dragon Glow — front-page.php
 * Front page template - tu dong duoc WordPress su dung lam trang chu.
 * Noi dung khớp với code HTML gốc từ stitch_dragon_glow_e_commerce_store
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="overflow-x-hidden" id="main-content">
    <?php get_template_part( 'template-parts/home/hero' ); ?>
    <?php get_template_part( 'template-parts/home/trust-badges' ); ?>
    <?php get_template_part( 'template-parts/home/featured-categories' ); ?>
    <?php get_template_part( 'template-parts/home/best-sellers' ); ?>
    <?php get_template_part( 'template-parts/home/brand-story' ); ?>
    <?php get_template_part( 'template-parts/home/testimonials' ); ?>
    <?php get_template_part( 'template-parts/home/instagram-grid' ); ?>
</main>

<?php get_footer(); ?>
