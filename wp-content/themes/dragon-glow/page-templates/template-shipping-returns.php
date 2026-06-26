<?php
/**
 * Template Name: Shipping & Returns — Dragon Glow
 * Description: Shipping & returns — editorial bento layout with trust badges, FAQ mini, and timeline visual.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Đánh dấu JS sẵn sàng ngay khi parse → reveal elements ẩn trước, tránh flash (FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// ── Load data trước khi render partials ───────────────────────────────
require_once locate_template( 'template-parts/shipping-returns/data-shipping-returns.php' );

// ── Hero ─────────────────────────────────────────────────────────────────────
set_query_var( 'dg_hero_title',    esc_html__( 'Shipping & Returns', 'dragon-glow' ) );
set_query_var( 'dg_hero_subtitle', esc_html__( 'Receiving your ritual, beautifully handled — and effortless if it ever needs to come back.', 'dragon-glow' ) );
get_template_part( 'template-parts/global/page-hero' );
?>

<main id="main-content" class="dg-sr">

	<?php // ── Scroll progress accent (top edge, scroll-linked) ─────────────── ?>
	<div class="dg-sr-progress" aria-hidden="true">
		<span class="dg-sr-progress-fill"></span>
	</div>

	<div class="dg-sr-wrap">

		<?php // ── Editorial intro ─────────────────────────────────────────── ?>
		<?php get_template_part( 'template-parts/shipping-returns/section-intro' ); ?>

		<?php // ── Trust badges ───────────────────────────────────────────── ?>
		<?php get_template_part( 'template-parts/shipping-returns/section-trust-badges' ); ?>

		<?php // ── BENTO GRID ─────────────────────────────────────────────── ?>
		<section class="dg-bento" aria-label="<?php esc_attr_e( 'Shipping and returns details', 'dragon-glow' ); ?>">

			<?php // Tile: Free shipping highlight ─────────────────────────── ?>
			<?php get_template_part( 'template-parts/shipping-returns/tile-free-shipping' ); ?>

			<?php // Tile: Delivery options table ─────────────────────────── ?>
			<?php get_template_part( 'template-parts/shipping-returns/tile-delivery-table' ); ?>

			<?php // Tiles: 3 Stats with count-up ─────────────────────────── ?>
			<?php get_template_part( 'template-parts/shipping-returns/tile-stats' ); ?>

			<?php // Tile: Returns timeline (4-step vertical) ───────────────── ?>
			<?php get_template_part( 'template-parts/shipping-returns/tile-returns-stepper' ); ?>

			<?php // Tile: Coverage ────────────────────────────────────────── ?>
			<?php get_template_part( 'template-parts/shipping-returns/tile-coverage' ); ?>

			<?php // Tiles: Packaging pillars ───────────────────────────────── ?>
			<?php get_template_part( 'template-parts/shipping-returns/tile-packaging' ); ?>

			<?php // Tile: Concierge CTA ───────────────────────────────────── ?>
			<?php get_template_part( 'template-parts/shipping-returns/tile-cta' ); ?>

		</section>

		<?php // ── FAQ Mini Accordion ──────────────────────────────────────── ?>
		<?php get_template_part( 'template-parts/shipping-returns/section-faq-mini' ); ?>

	</div>
</main>

<?php get_footer(); ?>
