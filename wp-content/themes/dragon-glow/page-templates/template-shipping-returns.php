<?php
/**
 * Template Name: Shipping & Returns — Dragon Glow
 * Description: Shipping & returns — 12-col split layout with macro-texture hero,
 * shipping philosophy, 3-phase journey timeline, sticky returns card, and FAQ accordion.
 * Mirrors stitch_dragon_glow_logistics_design.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// FOUC guard — reveal hidden elements after JS bootstraps.
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

require_once locate_template( 'template-parts/shipping-returns/data-shipping-returns.php' );
?>
<main id="main-content" class="dg-sr" data-page="shipping-returns">

	<?php get_template_part( 'template-parts/shipping-returns/section-hero' ); ?>

	<div class="dg-sr-wrap">
		<div class="dg-sr-split">

			<?php get_template_part( 'template-parts/shipping-returns/section-shipping-philosophy' ); ?>

			<?php get_template_part( 'template-parts/shipping-returns/section-returns-faqs' ); ?>

		</div>
	</div>
</main>
<?php get_template_part( 'template-parts/shipping-returns/section-return-modal' ); ?>
<?php get_footer(); ?>