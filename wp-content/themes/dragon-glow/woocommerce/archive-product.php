<?php
/**
 * Dragon Glow — Shop Archive (The Collection)
 * WooCommerce override: woocommerce/archive-product.php
 * Layout: hero + section header + magazine grid + philosophy + rituals
 * Matches template-shop.php (mock) structure.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- 1. Immersive hero banner -->
<?php get_template_part( 'template-parts/shop/hero' ); ?>

<!-- 2. Curated Glow section: header + product grid + pagination -->
<section class="py-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto" id="products">

	<?php get_template_part( 'template-parts/shop/section-header' ); ?>

	<!-- Active filter tags (driven by URL params) -->
	<?php get_template_part( 'template-parts/shop/active-filters' ); ?>

	<?php
		// ── Product loop ──────────────────────────────────────────
		$has_products    = have_posts();
		$found_posts     = (int) $GLOBALS['wp_query']->found_posts;
		$is_filtered_out = ! $has_products && $found_posts > 0;
		$is_empty_db    = ! $has_products && $found_posts === 0;
	?>

	<?php if ( $has_products ) : ?>

		<!-- Magazine staggered grid: 1/2/3 columns -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-gutter gap-y-32 dg-shop-grid" id="dg-product-grid">
			<?php
			$delay = 0;
			while ( have_posts() ) :
				the_post();
				set_query_var( 'dg_product_delay', $delay );
				get_template_part( 'template-parts/shop/product-card' );
				$delay += 100;
			endwhile;
			?>
		</div>

		<?php
		// ── Pagination ──────────────────────────────────────────
		get_template_part( 'template-parts/shop/pagination' );
		?>

	<?php elseif ( $is_filtered_out ) : ?>
		<!-- Filtered out: products exist but nothing matched -->
		<div class="text-center py-24">
			<div class="w-32 h-32 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-6">
				<span class="material-symbols-outlined text-primary" style="font-size: 64px;">search_off</span>
			</div>
			<h2 class="font-headline text-headline-md text-primary mb-4">
				<?php esc_html_e( 'No products found', 'dragon-glow' ); ?>
			</h2>
			<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
				<?php esc_html_e( 'We could not find any products matching your current filters. Try adjusting your selection.', 'dragon-glow' ); ?>
			</p>
			<a class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block"
			   href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">
				<?php esc_html_e( 'Clear Filters', 'dragon-glow' ); ?>
			</a>
		</div>

	<?php else : ?>
		<!-- Database is empty -->
		<div class="text-center py-24">
			<div class="w-32 h-32 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-6">
				<span class="material-symbols-outlined text-primary" style="font-size: 64px;">search</span>
			</div>
			<h2 class="font-headline text-headline-md text-primary mb-4">
				<?php esc_html_e( 'No products yet', 'dragon-glow' ); ?>
			</h2>
			<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
				<?php esc_html_e( 'Our collection is growing. New rituals arrive every season — check back soon for luminous additions.', 'dragon-glow' ); ?>
			</p>
			<a class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block"
			   href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to Home', 'dragon-glow' ); ?>
			</a>
		</div>
	<?php endif; ?>

</section>

<!-- 3. Mobile filter sheet -->
<div id="dg-mobile-filter-panel" class="fixed inset-0 z-[200] hidden">
	<div class="absolute inset-0 bg-inverse-surface/50" id="dg-filter-overlay"></div>
	<div class="absolute right-0 top-0 bottom-0 w-80 bg-surface overflow-y-auto p-6">
		<div class="flex justify-between items-center mb-6">
			<h3 class="font-headline text-xl text-primary"><?php esc_html_e( 'Filters', 'dragon-glow' ); ?></h3>
			<button type="button" id="dg-close-filter" class="p-2 hover:bg-surface-container rounded-full transition-colors">
				<span class="material-symbols-outlined">close</span>
			</button>
		</div>
		<?php get_template_part( 'template-parts/shop/filter-sidebar' ); ?>
	</div>
</div>

<!-- 4. Ingredient philosophy section -->
<?php get_template_part( 'template-parts/shop/philosophy' ); ?>

<!-- 5. Brand rituals section (AM / PM) -->
<?php get_template_part( 'template-parts/shop/rituals' ); ?>

<?php
/**
 * Note: All shop JavaScript (hero parallax, reveal on scroll, filter dropdown,
 * mobile sheet, active tags, apply/reset) is now consolidated in assets/js/shop.js
 * which is enqueued via inc/enqueue/scripts.php when is_shop() or is_product_taxonomy().
 * No inline script needed here.
 */
?>

<?php get_footer();
