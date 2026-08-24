<?php
/**
 * Template Name: Homepage — Dragon Glow
 * Description: Full homepage với hero, categories, best sellers, brand story, testimonials.
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
    <?php get_template_part( 'template-parts/global/newsletter-bar' ); ?>
</main>

<?php get_footer(); ?>
