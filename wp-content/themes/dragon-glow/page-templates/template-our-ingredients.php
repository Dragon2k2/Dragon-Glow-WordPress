<?php
/**
 * Template Name: Our Ingredients — Dragon Glow
 * Description: Our Ingredients — editorial luxury minimalism layout mirroring
 * stitch_dragon_glow_ingredients_page. Geist font, cream/charcoal palette,
 * 5 ingredient tiles, full-bleed image, scroll reveal animations.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// FOUC guard — reveal hidden elements after JS bootstraps.
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

require_once locate_template( 'template-parts/our-ingredients/data-our-ingredients.php' );
?>
<main id="main-content" class="dg-oi" data-page="our-ingredients">

	<?php get_template_part( 'template-parts/our-ingredients/section-hero' ); ?>

	<?php get_template_part( 'template-parts/our-ingredients/section-signature' ); ?>

	<?php get_template_part( 'template-parts/our-ingredients/section-ingredients' ); ?>

	<?php get_template_part( 'template-parts/our-ingredients/section-traceable' ); ?>

	<?php get_template_part( 'template-parts/our-ingredients/section-what-we-leave-out' ); ?>

	<?php get_template_part( 'template-parts/our-ingredients/section-outro' ); ?>

</main>
<?php get_footer();
