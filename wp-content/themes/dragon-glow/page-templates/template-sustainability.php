<?php
/**
 * Template Name: Sustainability — Dragon Glow
 * Description: Sustainability — editorial luxury minimalism layout mirroring
 * stitch_dragon_glow_sustainability_page. Geist font, cream/charcoal palette,
 * 10 sections from hero through closing CTA, scroll reveal animations.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// FOUC guard — reveal hidden elements after JS bootstraps.
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

require_once locate_template( 'template-parts/sustainability/data-sustainability.php' );
?>
<main id="main-content" class="dg-sus" data-page="sustainability">

	<?php get_template_part( 'template-parts/sustainability/section-hero' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-intro' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-commitments' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-sourcing' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-packaging' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-refill' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-carbon' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-accountability' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-numbers' ); ?>

	<?php get_template_part( 'template-parts/sustainability/section-closing' ); ?>

</main>
<?php get_footer();